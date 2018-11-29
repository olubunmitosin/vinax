<?php
/**
 * MyCred plugin functions
 *
 * @license For the full license information, please view the Licensing folder
 * that was distributed with this source code.
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'mycred_load_hooks', 'mycred_load_snax_votes_hook', 65 );
add_action( 'mycred_load_hooks', 'mycred_load_snax_format_hook', 65 );
add_filter( 'mycred_setup_hooks', 'mycred_register_snax_hooks', 65 );
add_filter( 'mycred_all_references', 'snax_mycred_add_references', 10, 1 );
/**
 * Add reference
 *
 * @param array $references References.
 * @return array
 */
function snax_mycred_add_references( $references ) {
	$references['snax_vote'] = __( 'Vote', 'snax' );
	$formats = snax_get_formats();
	$formats['quiz']['labels']['name'] = __( 'Quiz', 'snax' );
	$formats['list']['labels']['name'] = __( 'List', 'snax' );
	$formats['poll']['labels']['name'] = __( 'Poll', 'snax' );
	foreach ( $formats as $slug => $format ) {
		$slug = snax_mycred_override_format_slugs( $slug );
		$references[ 'snax_format_' . $slug ] = __( 'Publishing ', 'snax' ) . $formats[ $slug ]['labels']['name'];
	}
	return $references;
}

/**
 * Override format slugs to handle some formats as one
 *
 * @param 	string $slug  Format slug.
 * @return 	string
 */
function snax_mycred_override_format_slugs( $slug ) {
	if ( strpos( $slug, 'quiz' ) > -1 ) {
		$slug = 'quiz';
	}
	if ( strpos( $slug, 'list' ) > -1 ) {
		$slug = 'list';
	}
	if ( strpos( $slug, 'poll' ) > -1 ) {
		$slug = 'poll';
	}
	return $slug;
}

/**
 * Register hook
 *
 * @param array $installed Installed hooks.
 * @return array
 */
function mycred_register_snax_hooks( $installed ) {
	$installed['snax_vote'] = array(
		'title'         => __( 'Vote', 'snax' ),
		'description'   => __( 'Awards for voting.', 'snax' ),
		'callback'      => array( 'SnaxMyCredVoteHook' ),
	);
	$installed['snax_format'] = array(
		'title'         => __( 'Snax Format', 'snax' ),
		'description'   => __( 'Awards for adding Snax Formats.', 'snax' ),
		'callback'      => array( 'SnaxMyCredFormatHook' ),
	);
	return $installed;

}

/**
 * Snax Hook
 */
function mycred_load_snax_votes_hook() {
	/**
	 * Snax MyCred Hook class
	 */
	class SnaxMyCredVoteHook extends myCRED_Hook {

		/**
		 * Construct
		 */
		public function __construct( $hook_prefs, $type = MYCRED_DEFAULT_TYPE_KEY ) {
			parent::__construct( array(
				'id'       => 'snax_vote',
				'defaults' => array(
					'post_creds' => 1,
					'post_log'   => 'Voted on "%post_title%"',
					'item_creds' => 1,
					'item_log'   => 'Voted on "%post_title%"',
				),
			), $hook_prefs, $type );

		}

		/**
		 * Run.
		 */
		public function run() {
			add_action( 'snax_vote_saved', array( $this, 'vote_added' ), 10, 2 );
			add_filter( 'mycred_parse_tags_snax_vote', array( $this, 'parse_custom_tags' ), 10, 2 );
		}

		/**
		 * Parse Custom Tags in Log
		 */
		public function parse_custom_tags( $content, $log_entry ) {
			$data    = maybe_unserialize( $log_entry->data );
			$post_title = get_the_title( $data['post_id'] );
			$content = str_replace( '%post_title%', $post_title, $content );
			return $content;
		}

		/**
		 * Handle added vote.
		 *
		 * @param int $item_id  	Post id.
		 * @param int $author_id	Voting user.
		 */
		public function vote_added( $item_id, $author_id ) {
			$user_id = $author_id;
			if ( snax_get_item_post_type() === get_post_type( $item_id ) ) {
				$amount = $this->prefs['item_creds'];
				$entry = $this->prefs['item_log'];
			} else {
				$amount = $this->prefs['post_creds'];
				$entry = $this->prefs['post_log'];
			}
			$data = array(
				'ref_type'   => 'snax_vote',
				'post_id' => $item_id,
			);
			$this->core->add_creds(
				'snax_vote',
				$user_id,
				$amount,
				$entry,
				'',
				$data,
				$this->mycred_type
			);
		}

		/**
		 * Preferences.
		 */
		public function preferences() {
			$prefs = $this->prefs;
			?>
			<div class="hook-instance">
			<h3><?php _e( 'Voting for a post', 'snax' ); ?></h3>
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
				<div class="form-group">
				<label for="<?php echo $this->field_id( 'post_creds' ); ?>"><?php echo $this->core->plural(); ?></label>
				<input type="text" name="<?php echo $this->field_name( 'post_creds' ); ?>" id="<?php echo $this->field_id( 'post_creds' ); ?>"
				value="<?php echo $this->core->number( $prefs['post_creds'] ); ?>" class="form-control" />
			</div>
		</div>
		<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
			<div class="form-group">
				<label for="<?php echo $this->field_id( 'post_log' ); ?>"><?php _e( 'Log template', 'snax' ); ?></label>
				<input type="text" name="<?php echo $this->field_name( 'post_log' ); ?>" id="<?php echo $this->field_id( 'post_log' ); ?>" placeholder="<?php _e( 'required', 'snax' ); ?>" value="<?php echo esc_attr( $prefs['post_log'] ); ?>" class="form-control" />
				<span class="description"><?php echo $this->available_template_tags( array( 'general', 'user' ), '%post_title%, %reaction%' ); ?></span>
			</div>
				</div>
			</div>
			<h3><?php _e( 'Voting for an item', 'snax' ); ?></h3>
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'item_creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'item_creds' ); ?>" id="<?php echo $this->field_id( 'item_creds' ); ?>"
						value="<?php echo $this->core->number( $prefs['item_creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'item_log' ); ?>"><?php _e( 'Log template', 'snax' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'item_log' ); ?>" id="<?php echo $this->field_id( 'item_log' ); ?>" placeholder="<?php _e( 'required', 'snax' ); ?>" value="<?php echo esc_attr( $prefs['item_log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general', 'user' ), '%post_title%' ); ?></span>
					</div>
				</div>
			</div>
			</div>
			<?php
		}

	}

}

/**
 * Snax Hook
 */
function mycred_load_snax_format_hook() {
	/**
	 * Snax MyCred Hook class
	 */
	class SnaxMyCredFormatHook extends myCRED_Hook {

		/**
		 * Construct
		 */
		public function __construct( $hook_prefs, $type = MYCRED_DEFAULT_TYPE_KEY ) {
			$defaults = array();
			$formats = snax_get_formats();
			foreach ( $formats as $slug => $format ) {
				$creds 	= $slug . '_creds';
				$log 	= $slug . '_log';
				$defaults[ $creds ] = 1;
				$defaults[ $log ] = 'Published %snax_format%: "%post_title%"';
			}
			parent::__construct( array(
				'id'       => 'snax_format',
				'defaults' => $defaults,
			), $hook_prefs, $type );

		}

		/**
		 * Run.
		 */
		public function run() {

			add_action( 'snax_post_published', array( $this, 'post_added' ),10, 1 );

			add_filter( 'mycred_parse_tags_snax_format', array( $this, 'parse_custom_tags' ), 10, 2 );
		}

		/**
		 * Parse Custom Tags in Log
		 */
		public function parse_custom_tags( $content, $log_entry ) {
			$data    = maybe_unserialize( $log_entry->data );
			$post_title = get_the_title( $data['post_id'] );
			$formats = snax_get_formats();
			$snax_format = $formats[ $data['snax_format'] ]['labels']['name'];
			$content = str_replace( '%post_title%', $post_title, $content );
			$content = str_replace( '%snax_format%', $snax_format, $content );
			return $content;
		}

		/**
		 * Handle added vote.
		 *
		 * @param int $post_id  	Post id.
		 */
		public function post_added( $post_id ) {

			$post = get_post( $post_id );
			$user_id = $post->post_author;
			$slug = snax_get_post_format( $post_id );
			$creds 	= $slug . '_creds';
			$log 	= $slug . '_log';
			$amount = $this->prefs[ $creds ];
			$entry = $this->prefs[ $log ];

			$data = array(
				'ref_type'   => 'snax_format',
				'post_id' => $post_id,
				'snax_format' => $slug,
			);

			$slug = snax_mycred_override_format_slugs( $slug );
			$ret = $this->core->add_creds(
				'snax_format_' . $slug,
				$user_id,
				$amount,
				$entry,
				'',
				$data,
				$this->mycred_type
			);
		}

		/**
		 * Preferences.
		 */
		public function preferences() {
			$prefs = $this->prefs;
			$formats = snax_get_formats();
			?>
			<div class="hook-instance">
			<?php foreach ( $formats as $slug => $format ) :
				$creds 	= $slug . '_creds';
				$log 	= $slug . '_log';
				$title 	= __( 'Publishing', 'snax' ) . ' ' . $format['labels']['name'];
			?>
			<h3><?php echo esc_html( $title ); ?></h3>
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( $creds ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( $creds ); ?>" id="<?php echo $this->field_id( $creds ); ?>"
						value="<?php echo $this->core->number( $prefs[$creds] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( $log ); ?>"><?php _e( 'Log template', 'snax' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( $log ); ?>" id="<?php echo $this->field_id( $log ); ?>" placeholder="<?php _e( 'required', 'snax' ); ?>" value="<?php echo esc_attr( $prefs[$log] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general', 'user' ), '%post_title%, %snax_format%' ); ?></span>
					</div>
				</div>
			</div>
			<?php endforeach;?>
			</div>
			<?php
		}
	}
}
