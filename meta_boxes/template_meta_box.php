<?php

function artichoke_add_template_meta_box() {

	$screens = array( 'artichoke_template' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'artichoke_sectionid',
			__( 'Artichoke Settings', 'artichoke_domain' ),
			'artichoke_template_meta_box_callback',
			$screen,
			'normal',
			'high'
			);
	}
}
add_action( 'add_meta_boxes', 'artichoke_add_template_meta_box' );

/**
* Prints the box content.
* 
* @param WP_Post $post The object for the current post/page.
*/
function artichoke_template_meta_box_callback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'artichoke_template_meta_box', 'artichoke_template_meta_box_nonce' );

	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$css = get_post_meta( $post->ID, '_artichoke_template_css_key', true );
	if($css == "") {
		$css = "\n";
	}

	?>

	<input id="hiddenHtml" type="hidden" name="artichoke_template_html_field"/>
	<input id="hiddenCss" type="hidden" name="artichoke_template_css_field"/>

	<h2>Template HTML</h2>
	<div id="htmlEditor" style="width:100%; height:350px;"><?= htmlspecialchars($post->post_content) ?></div>

	<h2>Template CSS</h2>
	<div id="cssEditor" style="width:100%; height:350px;"><?= $css ?></div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?= plugins_url('aceHtmlHighlighter.js', __FILE__) ?>" type="text/javascript" charset="utf-8"></script>

	<script>

	var htmlEditor = ace.edit("htmlEditor");
	htmlEditor.setTheme("ace/theme/chrome");
	htmlEditor.getSession().setMode("ace/mode/html");

	htmlEditor.getSession().on('change', function() {
		htmlChangedHandle();
	});
	htmlChangedHandle();


	//rules = new HtmlHighlightRules().getRules();

	var cssEditor = ace.edit("cssEditor");
	cssEditor.setTheme("ace/theme/chrome");
	cssEditor.getSession().setMode("ace/mode/css");

	cssEditor.getSession().on('change', function() {
		cssChangedHandle();
	});
	cssChangedHandle();

	function cssChangedHandle() {
		var css = cssEditor.getSession().getValue().trim();
		jQuery('#hiddenCss').val(css);
	}

	function htmlChangedHandle() {
		var html = htmlEditor.getSession().getValue().trim();
		jQuery('#hiddenHtml').val(html);
	}
	</script>

	<?
}

/**
* When the post is saved, saves our custom data.
*
* @param int $post_id The ID of the post being saved.
*/
function artichoke_save_template_meta_box_data( $post_id ) {

	/*
	* We need to verify this came from our screen and with proper authorization,
	* because the save_post action can be triggered at other times.
	*/

	// Check if our nonce is set.
	if ( ! isset( $_POST['artichoke_template_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['artichoke_template_meta_box_nonce'], 'artichoke_template_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'artichoke_template' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if (isset( $_POST['artichoke_template_html_field'] ) ) {

		$html = sanitize_post_field( 'post_content', $_POST['artichoke_template_html_field'], $post_id, 'raw' );

		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', 'artichoke_save_template_meta_box_data' );

		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $html ) );

		// re-hook this function
		add_action( 'save_post', 'artichoke_save_template_meta_box_data' );

	}

	if (isset( $_POST['artichoke_template_css_field'] ) ) {

		$css = sanitize_post_field( 'post_content', $_POST['artichoke_template_css_field'], $post_id, 'raw' );

		// Update the meta field in the database.
		update_post_meta( $post_id, '_artichoke_template_css_key', $css );
	}


}
add_action( 'save_post', 'artichoke_save_template_meta_box_data' );


