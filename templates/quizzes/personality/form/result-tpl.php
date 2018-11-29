<?php
/**
 * Result template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="quizzard-result">
	<div class="quizzard-result-header">
		<div class="quizzard-result-thumbnail">
			<%= media.image  %>
		</div>

		<h3 class="quizzard-result-title-yo"><%= title %></h3>
		<input class="quizzard-result-title" type="text" value="<%- title  %>" placeholder="<?php echo esc_html_x( 'Enter result here', 'Placeholder', 'snax' ); ?>" />

		<a class="quizzard-icon quizzard-icon-delete quizzard-result-delete" href="#" title="<?php esc_attr_e( 'Delete', 'snax' ); ?>"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
		<a class="quizzard-icon quizzard-icon-toggle quizzard-result-toggle-state" href="#" title="<?php esc_attr_e( 'Collapse | Expand', 'snax' ); ?>"><?php esc_html_e( 'Collapse | Expand', 'snax' ); ?></a>
	</div>

	<div class="quizzard-result-body">
		<div class="quizzard-result-media <% print(media.id ? 'quizzard-result-with-media' : 'quizzard-result-without-media') %>">
			<%= media.image  %>
			<a class="quizzard-icon quizzard-icon-delete quizzard-result-delete-media" href="#" title="<?php esc_attr_e( 'Delete', 'snax' ); ?>"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
			<input type="hidden" class="quizzard-result-media-id" value="<%- media.id  %>" />
		</div>

		<p>
			<textarea class="quizzard-result-description" rows="4" cols="40" placeholder="<?php echo esc_attr_e( 'Type some description', 'snax' ); ?>"><%= description %></textarea>
		</p>
	</div>
</div>
