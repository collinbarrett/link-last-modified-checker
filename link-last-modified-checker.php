<?php
/*
Plugin Name: Link Last Modified Checker
Plugin URI: https://collinmbarrett.com/projects/
Description: Plugin to check the last modified date of remote files via shortcode built for use on https://filterlists.com.
Version: 0.1.1
Author: Collin M. Barrett
Author URI: https://collinmbarrett.com/
*/

// [lastmodified url="url-value"]
function lastmodified_func( $atts ) {
    $a = shortcode_atts( array(
        'url' => '',
    ), $atts );

    return filemtime_remote( $a['url'] );
}
add_shortcode( 'lastmodified', 'lastmodified_func' );

// check the last modified value of a url
function filemtime_remote( $url )
{
    $list = file_get_contents( $url , null , null , 0 , 200);
    $important = explode("Last modified: ",$list)[1];
    $mydate = substr($important, 0, 21);
    return $mydate;
}

?>
