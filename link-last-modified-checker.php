<?php
/*
Plugin Name: Link Last Modified Checker
Plugin URI: https://collinmbarrett.com/projects/
Description: Plugin to check the last modified date of remote files via shortcode built for use on https://filterlists.com.
Version: 0.1.3
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
  $moddate = get_transient( 'mod_' . esc_url( $url ) );
  if( false === $moddate ) {
    $list = file_get_contents( $url , null , null , 0 , 455);
    if( strpos( $list , "Last modified: " ) !== false ) {
        $moddateplus = explode( "Last modified: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 21 );
        if ( strpos( $moddateraw , "/" ) == false ) {
            if ( ctype_alpha( substr( $moddateraw, 3, 1 ) ) && ctype_alpha( substr( $moddateraw, 6, 1 ) ) ) {
                $modyearpre = substr( $moddateraw, strpos( $moddateraw, " " ) + 1 );
                $modyear = substr( $modyearpre, strpos( $modyearpre, " " ) + 1 , 4);
                $modmonth = substr( $moddateraw, 3, 3 );
                $modday = substr( $moddateraw, 0, 2 );
           } else if ( strpos( $moddateraw , "-" ) == 4  ) {
               $modyear = substr( $moddateraw, 0, 4 );
               $modmonthnum = substr( $moddateraw, 5, 2 );
               $modmonthtime = mktime(0, 0, 0, $modmonthnum);
               $modmonth = strftime("%b", $modmonthtime);
               $modday = substr( $moddateraw, 8, 2 );
           } else {
                $modyear = substr( $moddateraw, 7, 4 );
                $modmonth = substr( $moddateraw, 3, 3 );
                $modday = substr( $moddateraw, 0, 2 );
            }
        } else if ( strpos( $moddateraw , "/" ) == 4 ) {
            $modyear = substr( $moddateraw, 0, 4 );
            $modmonthnum = substr( $moddateraw, 5, 2 );
            $modmonthtime = mktime(0, 0, 0, $modmonthnum);
            $modmonth = strftime("%b", $modmonthtime);
            $modday = substr( $moddateraw, 8, 2 );
        }
    } else if ( strpos( $list , "Version: " ) !== false ) {
        $moddateplus = explode( "Version: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 12 );
        $modyear = substr( $moddateraw, 0, 4 );
        $modmonthnum = substr( $moddateraw, 4, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 6, 2 );
    } else if ( strpos( $list , "Last change: " ) !== false ) {
        $moddateplus = explode( "Last change: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 12 );
        $modyear = substr( $moddateraw, 6, 4 );
        $modmonthnum = substr( $moddateraw, 3, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 0, 2 );
    } else if ( strpos( $list , "Paskutinis atnaujinimas: " ) !== false ) {
        $moddateplus = explode( "Paskutinis atnaujinimas: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 12 );
        $modyear = substr( $moddateraw, 0, 4 );
        $modmonthnum = substr( $moddateraw, 5, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 8, 2 );
    }
    $moddate = $modday . " " . $modmonth . " " . $modyear;
    $timeout = mt_rand( 21600 , 64800 );
    //$timeout = 30;            // for testing
    set_transient( 'mod_' . esc_url( $url ), $moddate , $timeout );
  }
  return $moddate;
}

?>
