<?php
/*
Plugin Name: Link Last Modified Checker
Plugin URI: https://collinmbarrett.com/projects/
Description: Plugin to check the last modified date of remote files via shortcode built for use on https://filterlists.com.
Version: 0.1.2
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
function filemtime_remote( $url ){
  $mydate = get_transient( 'lm_' . esc_url( $url ) );
  if( false === $mydate ) {
    $list = file_get_contents( $url , null , null , 0 , 250);
    $important = explode("Last modified: ",$list)[1];
    $mydate = substr($important, 0, 21);
    $timeout = mt_rand( 21600 , 64800 );
    set_transient( 'lm_' . esc_url( $url ), $mydate , $timeout );
  }
  return $mydate;
}

?>
