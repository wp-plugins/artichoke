<?php

require_once(dirname(__FILE__) . '/../engine/template_engine.php');

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function artichoke_add_page_meta_box() {

    $screens = array( 'page' );

    foreach ( $screens as $screen ) {

        add_meta_box(
            'artichoke_sectionid',
            __( 'Artichoke Settings', 'artichoke_domain' ),
            'artichoke_page_meta_box_callback',
            $screen,
            'normal',
            'high'
            );
    }
}
add_action( 'add_meta_boxes', 'artichoke_add_page_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function artichoke_page_meta_box_callback( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'artichoke_page_meta_box', 'artichoke_page_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $currentTemplateId = get_post_meta( $post->ID, '_artichoke_page_template_key', true );

    $args = array(
      'sort_order' => 'ASC',
      'sort_column' => 'post_title',
      'hierarchical' => 1,
      'exclude' => '',
      'include' => '',
      'meta_key' => '',
      'meta_value' => '',
      'authors' => '',
      'child_of' => 0,
      'parent' => -1,
      'exclude_tree' => '',
      'number' => '',
      'offset' => 0,
      'post_type' => 'artichoke_template',
      'post_status' => 'publish'
      );
    $allTemplates = get_pages($args);

    $feat_image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));

    $content_abrv = $post->post_content;
    if(strlen($content_abrv) > 300) {
        $content_abrv = substr($content_abrv, 0, 300) . html_entity_decode('&hellip;', 0, 'UTF-8');;
    }

?>

<style type="text/css">

    table.fields {
        width: 100%;
        border-collapse: collapse;
    }

    table.fields th {
        text-align: left;
        padding: 5px;
    }

    table.fields td {
        border-top: 1px solid lightgray;
        padding: 5px;
        vertical-align: top;
        text-align: left;
    }

    textarea.fieldValue {
        width: 100%;
    }

</style>

<script type="text/javascript">
    jQuery(function() {

        jQuery('input#title').change(function(){
            jQuery('#artichoke_title_value').text(jQuery(this).val());
        });

        jQuery('select#artichoke_template_select').change(function(){
            console.log(this.value);
            loadTemplateFields(this.value);
        });

        jQuery('textarea#content').change(function(){
            var content = jQuery(this).val();
            if(content.length > 300) {
                content = content.substr(0, 300) + '\u2026';
            }
            jQuery('#artichoke_content_value').text(content);
        });

        function loadTemplateFields(templateId) {
            jQuery('#template_field_values').html('Loading...');
            jQuery.ajax("<?= plugins_url('template_field_values.php', __FILE__) . '?postId=' . $post->ID ?>&templateId=" + templateId)
                .done(function(data) {
                    jQuery('#template_field_values').html(data);
                });
        }
        loadTemplateFields(<?= $currentTemplateId ?>);
    });
</script>


<div>
    <label for="artichoke_page_template_field">
        <? _e( 'Template: ', 'artichoke_textdomain' ); ?>
    </label> 
    <select name="artichoke_page_template_field" id="artichoke_template_select">
        <option value="null">-- No template selected --</option>
        <? foreach ( $allTemplates as $template ): ?>
            <option value="<?= $template->ID ?>" <?= ($template->ID == $currentTemplateId? 'selected': '') ?> ><?= $template->post_title ?></option>
        <? endforeach; ?>
    </select>
</div>

<div id="template_field_values">
</div>

<div>
    <h2>Standard field values<sup>1</sup></h2>
    <table class="fields">
        <thead>
            <tr>
                <th>Tag</th>
                <th colspan="2">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>[[title]]</td>
                <td colspan="2"><span id="artichoke_title_value"><?= $post->post_title ?></span></td>
            </tr>
            <tr>
                <td>[[content]]</td>
                <td colspan="2"><span id="artichoke_content_value"><?= htmlspecialchars($content_abrv) ?></span></td>
            </tr>
            <tr>
                <td>[[featured_image]]</td>
                <td>
                    <em>Image:</em><br/>
                    <img style="max-width:80px; max-height:80px;" src="<?= $feat_image ?>"/>
                </td>
                <td>
                    <em>URL:</em><br/><a href="<?= $feat_image ?>"><?= $feat_image ?></a>
                </td>
            </tr>
        </tbody>
    </table>
    <p><sup>1</sup> Values reflect last save.</p>
</div>

<?
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function artichoke_save_page_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['artichoke_page_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['artichoke_page_meta_box_nonce'], 'artichoke_page_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

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
    if (isset( $_POST['artichoke_page_template_field'] ) ) {

       $templateId = $_POST['artichoke_page_template_field'];

	    // Update the meta field in the database.
       update_post_meta( $post_id, '_artichoke_page_template_key', $templateId );
    }

    if(isset($_POST['artichoke_field_names'])) {

        $fields = split(",", $_POST['artichoke_field_names']);
        foreach ($fields as $field) {
            $formField = 'artichoke_field_' . $field;
            
            if(isset($_POST[$formField])) {
                $value = sanitize_post_field( 'post_content', $_POST[$formField], $post_id, 'raw' );

                update_post_meta( $post_id, '_artichoke_page_field_' . $field, $value );
            }
        }
    }

   
}
add_action( 'save_post', 'artichoke_save_page_meta_box_data' );


