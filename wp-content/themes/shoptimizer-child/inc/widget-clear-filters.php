<?php
/**
 * Widget: Clear All Filters
 *
 * @package Shoptimizer Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class MKX_Widget_Clear_Filters
 *
 * A widget to display a "Clear All Filters" button for WooCommerce.
 * This is useful for resetting all active filters on a shop or archive page.
 */
class MKX_Widget_Clear_Filters extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'mkx_clear_filters_widget', // Base ID.
			esc_html__( 'MKX: Clear All Filters', 'shoptimizer-child' ), // Name.
			array( 'description' => esc_html__( 'Displays a button to clear all active WooCommerce product filters.', 'shoptimizer-child' ) ) // Args.
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
		// Only show the widget on WooCommerce archive pages where filters are active.
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) {
			return;
		}

		// Check if any filters are currently applied.
		$is_filtered = false;
		// A list of common filter query parameters.
		$filter_params = array(
			'min_price',
			'max_price',
			'filter_',
			'paged',
		);

		foreach ( $_GET as $key => $value ) {
			if ( strpos( $key, 'filter_' ) === 0 || in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
				$is_filtered = true;
				break;
			}
		}

		if ( ! $is_filtered ) {
			return; // Don't display the widget if no filters are active.
		}

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Get the base URL for the current archive.
		$link = '';
		if ( is_product_category() ) {
			$link = get_term_link( get_queried_object() );
		} elseif ( is_shop() ) {
			$link = get_permalink( wc_get_page_id( 'shop' ) );
		} else {
			// Fallback for other taxonomies or archives.
			$link = get_post_type_archive_link( 'product' );
		}

		echo '<div class="mkx-clear-filters-widget">';
		echo '<a href="' . esc_url( $link ) . '" class="button mkx-clear-filters-button">' . esc_html__( 'Очистить фильтры', 'shoptimizer-child' ) . '</a>';
		echo '</div>';

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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
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