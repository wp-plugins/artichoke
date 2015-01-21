<?php

function artichoke_merge($template, $content, $post) {

	$feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );

	$patterns = array();
	$patterns[0] = '/\[\[title\]\]/i';
	$patterns[1] = '/\[\[content\]\]/i';
	$patterns[2] = '/\[\[featured_image\]\]/i';

	$replacements = array();
	$replacements[0] = $post->post_title;
	$replacements[1] = $content;
	$replacements[2] = $feat_image;

	foreach (artichoke_get_field_names($template) as $field) {
		$patterns []= '/\[\[' . $field . '\]\]/i';
		$replacements []= get_post_meta($post->ID, '_artichoke_page_field_' . $field, true);
	}

	return preg_replace($patterns, $replacements, $template);

}

function artichoke_get_field_names($content) {

	$pattern = '/\[\[\w+\]\]/';
	preg_match_all($pattern, $content, $matches);

	$res = array();
	foreach ($matches[0] as $key => $value) {
		
		$field = strtolower(substr($value, 2, strlen($value) - 4));

		if(!in_array($field, array('title', 'content', 'featured_image'))) {
			$res []= $field;
		}
	}

	return $res;
}