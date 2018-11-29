<?php
/**
 * Snax CTA Widget
 *
 * @package snax
 * @subpackage Widgets
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Class Snax_Widget_CTA
 */
class Snax_Widget_CTA extends WP_Widget {

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
		$widget_options = apply_filters( 'snax_widget_cta_options', array(
			'classname'   => 'snax snax-widget-cta',
			'description' => esc_html__( 'Box with link to the Frontend Submission page', 'snax' ),
		) );

		parent::__construct( 'snax_widget_cta', esc_html__( 'Snax Call To Action', 'snax' ), $widget_options );
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
			$instance['id'] = 'snax-widget-cta-' . self::$counter++;
		}

		// HTML class.
		$classes = explode( ' ', $instance['class'] );

		echo wp_kses_post( $args['before_widget'] );

		do_action( 'snax_before_widget_cta_title' );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		$text_before 	= apply_filters( 'wpml_translate_single_string', $instance['text_before'], 'Widgets', 'Snax Call to Action - text field' );
		$button_label 	= apply_filters( 'wpml_translate_single_string', $instance['button_label'], 'Widgets', 'Snax Call to Action - button label field' );

		$snax_cta_type = $instance['type'];
		$snax_cta_list_type = '';
		if ( 'ranked_list' === $snax_cta_type ) {
			$snax_cta_type = 'list';
			$snax_cta_list_type = 'ranked';
		}
		if ( 'list' === $snax_cta_type ) {
			$snax_cta_type = 'list';
		}
		if ( 'classic_list' === $snax_cta_type ) { 
			$snax_cta_type = 'list';
			$snax_cta_list_type = 'classic';
		}

		set_query_var( 'snax_cta_list_type', $snax_cta_list_type );
		set_query_var( 'snax_cta_type', $snax_cta_type );
		set_query_var( 'snax_cta_text_before', $text_before );
		set_query_var( 'snax_cta_button_label', $button_label );
		snax_get_template_part( 'widget-call-to-action' );

		do_action( 'snax_widget_cta_before_after_widget' );

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

		?>
		<div class="snax-widget-cta">
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
					for="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>"><?php esc_html_e( 'Call To Action text', 'snax' ); ?>
					:</label>
				<input class="widefat"
					   size="50"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'text_before' ) ); ?>"
				       id="<?php echo esc_attr( $this->get_field_id( 'text_before' ) ); ?>"
				       value="<?php echo esc_attr( $instance['text_before'] ) ?>"/>
			</p>

			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>"><?php esc_html_e( 'Button label', 'snax' ); ?>
					:</label>
				<input class="widefat"
					   size="20"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'button_label' ) ); ?>"
				       id="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>"
				       value="<?php echo esc_attr( $instance['button_label'] ) ?>"/>
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
			<?php
			$type = $instance['type'];
			$all_formats = snax_get_formats();
			?>
			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Link to:', 'snax' ); ?>
					:</label>
				<select class="widefat"
				       type="text"
				       name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>"
					   id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>">
					   <option value="all" value=""<?php selected( $type, 'all' ); ?>>All formats</option>
					<?php
					foreach ( $all_formats as $key => $format ) {?>
						<option value="<?php echo esc_attr( $key );?>" value=""<?php selected( $type, $key ); ?>><?php echo wp_kses_post( $format['labels']['name'] );?></option>
						<?php
					}
					?>
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

		$instance['title']        = wp_strip_all_tags( $new_instance['title'] );
		$instance['text_before']  = wp_strip_all_tags( $new_instance['text_before'] );
		$instance['button_label'] = wp_strip_all_tags( $new_instance['button_label'] );
		$instance['id']           = sanitize_html_class( $new_instance['id'] );
		$instance['class']        = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $new_instance['class'] ) ) );
		$instance['type'] 		  = wp_strip_all_tags( $new_instance['type'] );

		do_action( 'wpml_register_single_string', 'Widgets', 'Snax Call to Action - text field', $instance['text_before'] );
		do_action( 'wpml_register_single_string', 'Widgets', 'Snax Call to Action - button label field', $instance['button_label'] );

		return $instance;
	}

	/**
	 * Get default arguments
	 *
	 * @return array
	 */
	public function get_default_args() {
		return apply_filters( 'snax_widget_lists_defaults', array(
			'title'        => '',
			'text_before'  => esc_html__( 'Unleash your creativity and share you story with us!', 'snax' ),
			'button_label' => esc_html__( 'Create', 'snax' ),
			'id'           => '',
			'class'        => '',
			'type'         => 'all',
		) );
	}
}
