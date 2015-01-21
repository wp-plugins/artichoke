<?php

require_once('../../../../wp-blog-header.php'); // Use actual root path to wp-blog-header.php
header("HTTP/1.0 200 OK");

require_once(dirname(__FILE__) . '/../engine/template_engine.php');

$currentTemplateId = $_GET['templateId'];

$currentTemplate = get_page($currentTemplateId);

$postId = $_GET['postId'];

$post = get_page($postId);


$fields = artichoke_get_field_names($currentTemplate->post_content);

?>

<input type="hidden" name="artichoke_field_names" value="<?= join(',', $fields) ?>"/>

<h2>Template field values</h2>

<? if(count($fields) > 0): ?>

<table class="fields">
	<thead>
		<tr>
			<th>Tag</th>
			<th>Value</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ( $fields as $field): ?>
		<tr>
			<td>[[<?= $field ?>]]</td>
			<td>
				<textarea name="artichoke_field_<?= $field ?>" class="fieldValue" rows="2" cols="50" placeholder="Value for <?= $field ?> field&hellip;"><?= get_post_meta( $post->ID, '_artichoke_page_field_' . $field, true ); ?></textarea>
			</td>
		</tr>
	<? endforeach; ?>
</tbody>
</table>

<? else: ?>

	<p>No custom fields in template.</p>

<? endif; ?>

