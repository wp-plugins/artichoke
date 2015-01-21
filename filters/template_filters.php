<?php

require_once(dirname(__FILE__) . '/../engine/template_engine.php');

function artichoke_template_the_content_filter( $content ) {
    $post = $GLOBALS['post'];

    if(is_single() && $post->post_type == 'artichoke_template') {

        $css = get_post_meta( $post->ID, '_artichoke_template_css_key', true );

        return '<style>' . $css . '</style><div><strong>BEGIN</strong> Artichoke template<hr/></div>' . $content . '<div style="clear: both;"><hr/><strong>END</strong> Artichoke template</div>';

    } else if($post->post_type == 'page') {

        $templateId = get_post_meta( $post->ID, '_artichoke_page_template_key', true );
        $css = get_post_meta( $templateId, '_artichoke_template_css_key', true );

        if(!isset($templateId) || $templateId == 'null' || $templateId == '') {
            return $content;
        }

        $template = get_page($templateId);

        if(!isset($template)) {
            return $content;
        }

        return '<style>' . $css . '</style>' . artichoke_merge($template->post_content, $content, $post);
    }

    return $content;
}
add_filter( 'the_content', 'artichoke_template_the_content_filter' );


function artichoke_template_the_title_filter( $title, $id = null ) {

    $post = $GLOBALS['post'];

    if(is_single() && $post->post_type == 'artichoke_template' && $post->ID == $id) {
        return '<small>PREVIEW of Artichoke template:<br/></small><strong>' . $title . '</strong>';
    }

    return $title;
}
add_filter( 'the_title', 'artichoke_template_the_title_filter', 10, 2);
