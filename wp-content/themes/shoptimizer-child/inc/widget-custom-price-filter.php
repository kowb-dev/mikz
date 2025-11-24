<?php
/**
 * Widget: Custom Price Filter
 *
 * @package Shoptimizer Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class MKX_Widget_Custom_Price_Filter
 *
 * A custom widget to filter products by price using manual input fields.
 */
class MKX_Widget_Custom_Price_Filter extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'mkx_custom_price_filter_widget', // Base ID.
			esc_html__( 'MKX: Фильтрация по цене', 'shoptimizer-child' ), // Name.
			array( 'description' => esc_html__( 'Displays a form to filter products by a price range with manual input.', 'shoptimizer-child' ) ) // Args.
		);
	}

	/**
	 * Frontend display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? esc_html__( 'Фильтрация по цене', 'shoptimizer-child' ) : $instance['title'] );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$current_min_price = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : '';
		$current_max_price = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : '';
		
		// Get the base URL for the form action
		$form_action = wc_get_page_permalink( 'shop' );
		if ( is_product_category() ) {
			$form_action = get_term_link( get_queried_object()->term_id, 'product_cat' );
		} elseif ( is_product_tag() ) {
			$form_action = get_term_link( get_queried_object()->term_id, 'product_tag' );
		}
		?>

		<form method="get" action="<?php echo esc_url( $form_action ); ?>" class="mkx-custom-price-filter-form">
			<div class="price-inputs">
				<div class="price-input-group">
					<label for="min_price"><?php esc_html_e( 'От', 'shoptimizer-child' ); ?></label>
					<input type="number" id="min_price" name="min_price" value="<?php echo esc_attr( $current_min_price ); ?>" placeholder="<?php esc_attr_e( 'Мин', 'shoptimizer-child' ); ?>" />
				</div>
				<div class="price-input-group">
					<label for="max_price"><?php esc_html_e( 'До', 'shoptimizer-child' ); ?></label>
					<input type="number" id="max_price" name="max_price" value="<?php echo esc_attr( $current_max_price ); ?>" placeholder="<?php esc_attr_e( 'Макс', 'shoptimizer-child' ); ?>" />
				</div>
			</div>
			<button type="submit" class="button mkx-filter-button"><?php esc_html_e( 'Фильтровать', 'shoptimizer-child' ); ?></button>
			<?php echo wc_query_string_form_fields( null, array( 'min_price', 'max_price', 'paged' ), '', true ); ?>
		</form>

		<?php
		echo $args['after_widget'];
	}

	/**
	 * Backend widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Фильтрация по цене', 'shoptimizer-child' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'shoptimizer-child' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
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
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}
}
