<?php
/**
 * Custom template tags for this theme.
 *
 * @package shoptimizer-child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Replaces the expert's Gravatar with a custom image.
 *
 * This function hooks into the get_avatar filter and checks if the avatar
 * is for the user with the display name "Эксперт по запчастям МИКЗ".
 * If it is, it replaces the Gravatar with a custom image from the theme's assets.
 *
 * @param string $avatar The HTML for the avatar.
 * @param mixed  $id_or_email The user ID, email address, or comment object.
 * @param int    $size The avatar size in pixels.
 * @param string $default The URL for the default avatar.
 * @param string $alt The alternative text for the avatar.
 * @return string The modified avatar HTML.
 */
function shoptimizer_child_expert_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	$user = false;

	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', (int) $id_or_email );
	} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
		$user = get_user_by( 'id', (int) $id_or_email->user_id );
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	}

	if ( $user && 'Эксперт по запчастям МИКЗ' === $user->display_name ) {
		$custom_avatar_url = get_stylesheet_directory_uri() . '/assets/images/mikzz_expert.webp';
		$avatar            = "<img alt='" . esc_attr( $alt ) . "' src='" . esc_url( $custom_avatar_url ) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' decoding='async'>";
	}

	return $avatar;
}
add_filter( 'get_avatar', 'shoptimizer_child_expert_avatar', 10, 5 );
