<?php
/**
 * Snax Teaser Widget
 *
 * @package snax
 * @subpackage Widgets
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Class Snax_Widget_Teaser
 */
class Snax_Widget_Teaser extends WP_Widget {

	/**
	 * The total number of displayed widgets
	 *
	 * @var int
	 */
	static $counter = 1;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		$widget_options = apply_filters( 'snax_widget_lists_options', array(
			'classname'   => 'snax snax-teaser',
			'description' => esc_html__( 'A post teaser', 'snax' ),
		) );

		parent::__construct( 'snax_widget_teaser', esc_html__( 'Snax Teaser', 'snax' ), $widget_options );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->get_default_args() );
		echo wp_kses_post( $args['before_widget'] );

		do_action( 'snax_before_widget_teaser_title' );
		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		$post_id = $instance['post_id'];
		if ( $post_id ) {
			$template = '/widget-teaser/' . $instance['group'];
			set_query_var( 'snax_widget_teaser_post_id', $post_id );
			snax_get_template_part( $template );
		}

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->get_default_args() );
		$post_groups = $this->get_post_groups();
		?>
		<div class="snax-widget-lists">
			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget title', 'snax' ); ?>
					:</label>
				<input class="widefat"
				       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				       value="<?php echo esc_attr( $instance['title'] ); ?>">
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'post_id' ) ); ?>"><?php esc_html_e( 'Post', 'snax' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this -> get_field_id( 'post_id' ) ); ?>" name="<?php echo  esc_attr( $this -> get_field_name( 'post_id' ) ); ?>" >
				<?php foreach ( $post_groups as $group ) {
					echo '<optgroup label="' . $group['label'] . '">';
					foreach ( $group['posts'] as $post ) {
						$id = $post->ID . '__' . $group['slug'];
						if ( isset( $instance['group'] ) && isset( $instance['post_id'] ) ) {
							$old_id = $instance['post_id'] . '__' . $instance['group'];
						} else {
							$old_id = '';
						}
						$label = $post->post_title;
						echo '<option value="' . esc_attr( $id ) . '"' . selected( $id === $old_id , true, false ) . '>' . esc_html( $label ) . '</option>';
					}
					echo '</optgroup>';
				}?>
				</select>
			</p>
		</div>
		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$id = explode( '__', filter_var( $new_instance['post_id'], FILTER_SANITIZE_STRING ) );
		$instance['post_id'] = $id[0];
		$instance['group'] = $id[1];
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Get default arguments
	 *
	 * @return array
	 */
	public function get_default_args() {
		return apply_filters( 'snax_widget_teaser_defaults', array(
			'post_id' => esc_html__( 'Recently created lists', 'snax' ),
			'title'        => '',
		) );
	}

	/**
	 * Get groupped posts to select from
	 *
	 * @return array
	 */
	private function get_post_groups() {
		$groups = array();

		$posts = get_posts( array(
			'post_type'         => snax_get_poll_post_type(),
			'posts_per_page'    => -1,
			'meta_query'		=> array(
				array(
					'key'          => '_snax_poll_type',
					'value'        => 'binary',
				),
			),
		) );
		if ( $posts ) {
			$groups[] = array(
				'label'	=> 'Binary Polls',
				'slug'	=> 'poll-binary',
				'posts' => $posts,
			);
		}

		$posts = get_posts( array(
			'post_type'         => snax_get_poll_post_type(),
			'posts_per_page'    => -1,
			'meta_query'		=> array(
				array(
					'key'          => '_snax_poll_type',
					'value'        => 'versus',
				),
			),
		) );
		if ( $posts ) {
			$groups[] = array(
				'label'	=> 'Versus Polls',
				'slug'	=> 'poll-versus',
				'posts' => $posts,
			);
		}

		return $groups;
	}

}
