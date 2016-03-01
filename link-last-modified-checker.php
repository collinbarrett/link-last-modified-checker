<?php
/*
Plugin Name: Link Last Modified Checker
Plugin URI: https://collinmbarrett.com/projects/
Description: Plugin to check the last modified date of remote files via shortcode built for use on https://filterlists.com.
Version: 0.1.4
Author: Collin M. Barrett
Author URI: https://collinmbarrett.com/
*/

// wp shortcode [lastmodified url="url-value"]
function lastmodified_func( $atts ) {
    $a = shortcode_atts( array(
        'url' => '',
    ), $atts );
    return filemtime_remote( $a['url'] );
}
add_shortcode( 'lastmodified', 'lastmodified_func' );

// check the last modified value of a url
function filemtime_remote( $url ) {
  $moddate = get_transient( 'mod_' . esc_url( $url ) );
  $modrc = get_transient( 'modrc' );

  // if list date is due for refresh and max date refreshes for this page request have not been reached
  if( ( false === $moddate || $moddate == "Pending" ) && $modrc < 3 ) {
    $list = file_get_contents( $url , null , null , 0 , 455);

    // increment list refresh counter
    $modrc++;
    set_transient( 'modrc', $modrc, 60 );

    // parse year, month, and day
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
    } else if ( strpos( $list , "Updated: " ) !== false ) {
        $moddateplus = explode( "Updated: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 21 );
        if ( strpos( $moddateraw , " " ) == 1 ) {
            $modyear = substr( $moddateraw, 6, 4 );
            $modmonth = substr( $moddateraw, 2, 3 );
            $modday = "0" . substr( $moddateraw, 0, 1 );
        } else {
            $modyear = substr( $moddateraw, 7, 4 );
            $modmonth = substr( $moddateraw, 3, 3 );
            $modday = substr( $moddateraw, 0, 2 );
        }
    } else if ( strpos( $list , "Paskutinis atnaujinimas: " ) !== false ) {
        $moddateplus = explode( "Paskutinis atnaujinimas: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 12 );
        $modyear = substr( $moddateraw, 0, 4 );
        $modmonthnum = substr( $moddateraw, 5, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 8, 2 );
    }

    // verify year, month, and day
    if ( ctype_digit( $modday ) &&
         strlen ( $modday ) == 2 &&
         ctype_alpha( $modmonth ) &&
         strlen ( $modmonth ) == 3 &&
         ctype_digit( $modyear ) &&
         strlen ( $modyear ) == 4 ) {
         $moddate = $modday . " " . $modmonth . " " . $modyear;
    } else if ( $list == "" ) {
        $moddate = "Inaccessible";
    } else if ( $moddateraw == ""  ) {
        $moddate = "N/A";
    } else {
        $moddate = "Parse Error";
    }

    // set random transient timeout between 6 and 18 hrs
    $timeout = mt_rand( 21600 , 64800 );

    // set transient for url updated date
    set_transient( 'mod_' . esc_url( $url ), $moddate , $timeout );
  } else if ( $modrc >= 3 ) {
    $moddate = "Pending";
  }
  return $moddate;
}

?>
