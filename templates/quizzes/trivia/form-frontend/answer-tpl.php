<?php
/**
 * Answer template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="quizzard-answer">
	<div class="quizzard-answer-header">
		<a class="quizzard-icon quizzard-icon-delete quizzard-answer-delete" href="#" title="<?php esc_attr_e( 'Delete', 'snax' ); ?>"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
	</div>


	<div class="quizzard-answer-media <% print(media.id ? 'quizzard-answer-with-media' : 'quizzard-answer-without-media') %>">
		<%= media.image  %>
		<a class="quizzard-icon quizzard-icon-delete quizzard-answer-delete-media" href="#" title="<?php esc_attr_e( 'Delete', 'snax' ); ?>"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
		<input type="hidden" class="quizzard-answer-media-id" value="<%- media.id  %>" />
	</div>

	<div class="quizzard-answer-body">
		<input type="text" class="quizzard-answer-title" value="<%- title  %>" />

		<label class="quizzard-answer-correct-label" title="<?php esc_attr_e( 'Is this answer correct?', 'snax' ); ?>">
			<input type="radio" name="snax_answer_correct_<%- question_id %>" class="quizzard-answer-correct" <% print(correct ? 'checked="checked"' : '') %> /> <span><?php esc_html_e( 'Correct', 'snax' ); ?></span>
		</label>
	</div>
</div>
