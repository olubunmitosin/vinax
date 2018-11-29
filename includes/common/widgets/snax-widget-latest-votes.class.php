<?php
/**
 * Snax Latest Votes Widget
 *
 * @package snax
 * @subpackage Widgets
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}


/**
 * Class Snax_Widget_Latest_Votes
 */
class Snax_Widget_Latest_Votes extends WP_Widget {

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
		$widget_options = apply_filters( 'snax_widget_latest_votes_options', array(
			'classname'   => 'snax snax-widget-latest-votes',
			'description' => esc_html__( 'User latest votes', 'snax' ),
		) );

		parent::__construct( 'snax_widget_latest_votes', esc_html__( 'Snax Latest Votes', 'snax' ), $widget_options );
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

		$title = apply_filters( 'widget_title', $instance['title'] );

		// HTML id.
		if ( empty( $instance['id'] ) ) {
			$instance['id'] = 'snax-widget-latest-votes-' . self::$counter++;
		}

		// HTML class.
		$classes   = explode( ' ', $instance['class'] );
		$classes[] = 'snax-widget-latest-votes';

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		$user_id = $this->get_user_id();
		$view_all_url = $this->get_view_all_url( $user_id );

		$votes = snax_get_user_latest_votes( $user_id, $instance['max'] );

		set_query_var( 'snax_latest_votes', $votes );
		set_query_var( 'snax_latest_votes_type', $user_id ? 'for_displayed_user' : 'global' );
		set_query_var( 'snax_latest_votes_view_all_url', $view_all_url );
		set_query_var( 'snax_latest_votes_id', $instance['id'] );
		set_query_var( 'snax_latest_votes_classes', $classes );

		snax_get_template_part( 'widget-latest-votes' );

		echo wp_kses_post( $args['after_widget'] );
	}

	protected function get_user_id() {
		// On BP profile page?
		if ( function_exists( 'bp_get_displayed_user' ) && $user = bp_get_displayed_user() ) {
			return $user->id;
		}

		return 0;
	}

	protected function get_view_all_url( $user_id ) {
		if ( ! $user_id ) {
			return '';
		}

		return bp_core_get_user_domain( $user_id ) . snax_votes_bp_component_id();
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

		?>
		<div class="snax-widget-latest-votes">
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
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"><?php esc_html_e( 'The max. number of entries to show', 'snax' ); ?>
					:</label>
				<input size="5"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'max' ) ); ?>"
				       id="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"
				       value="<?php echo esc_attr( $instance['max'] ) ?>"/>
			</p>

			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e( 'HTML id attribute (optional)', 'snax' ); ?>
					:</label>
				<input class="widefat"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>"
				       id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"
				       value="<?php echo esc_attr( $instance['id'] ) ?>"/>
			</p>

			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>"><?php esc_html_e( 'HTML class(es) attribute (optional)', 'snax' ); ?>
					:</label>
				<input class="widefat"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'class' ) ); ?>"
				       id="<?php echo esc_attr( $this->get_field_id( 'class' ) ); ?>"
				       value="<?php echo esc_attr( $instance['class'] ) ?>"/>
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

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max']   = absint( $new_instance['max'] );
		$instance['id']    = sanitize_html_class( $new_instance['id'] );
		$instance['class'] = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $new_instance['class'] ) ) );

		return $instance;
	}

	/**
	 * Get default arguments
	 *
	 * @return array
	 */
	public function get_default_args() {
		return apply_filters( 'snax_widget_latest_votes_defaults', array(
			'title' => esc_html__( 'Latest votes', 'snax' ),
			'max'   => 3,
			'id'    => '',
			'class' => '',
		) );
	}

	/**
	 * Return WP Query based on instance config data
	 *
	 * @param array $instance           Widget instanca config.
	 *
	 * @return array
	 */
	protected function get_query_args( $instance ) {
		// Default config, reflects the recently_created type.
		$query_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'tax_query'		 	  => array(
				array(
					'taxonomy' 	=> snax_get_snax_format_taxonomy_slug(),
					'field' 	=> 'slug',
					'terms'	  	=> 'list',
				),
			),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'posts_per_page'      => $instance['max'],
			'ignore_sticky_posts' => true,
		);

		return apply_filters( 'snax_widget_latest_votes_query_args', $query_args );
	}
}