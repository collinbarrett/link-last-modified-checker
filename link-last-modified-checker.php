<?php
/*
Plugin Name: Link Last Modified Checker
Plugin URI: https://collinmbarrett.com/projects/
Description: Plugin to check the last modified date of remote files via shortcode built for use on https://filterlists.com.
Version: 0.1.5
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

  // if list date is due for refresh and max date refreshes for this page request have not been reached
  if( false === $moddate ) {
    $list = file_get_contents( $url , null , null , 0 , 480 );

    // parse year, month, and day
    if( strpos( $list , "Last modified: " ) !== false ) {
        $moddateplus = explode( "Last modified: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 21 );
        if ( strpos( $moddateraw , "/" ) == false ) {
            if ( ctype_alpha( substr( $moddateraw, 3, 1 ) ) && ctype_alpha( substr( $moddateraw, 6, 1 ) ) ) {
                $modyearpre = substr( $moddateraw, strpos( $moddateraw, " " ) + 1 );
                $modyear = substr( $modyearpre, strpos( $modyearpre, " " ) + 1 , 4 );
                $modmonth = substr( $moddateraw, 3, 3 );
                $modday = substr( $moddateraw, 0, 2 );
           } else if ( ctype_alpha( substr( $moddateraw, 2, 2 ) ) ) {  // Ex: Juvander’s Finnish
                $modyearpre = substr( $moddateraw, strposX( $moddateraw, " ", 3 ) + 1 );
                $modyear = substr( $modyearpre, 0 , 4 );
                $modmonth = substr( $moddateraw, 8, 3 );
                $modday = substr( $moddateraw, 0, 2 );
           } else if ( strpos( $moddateraw , "-" ) == 4  ) {
               $modyear = substr( $moddateraw, 0, 4 );
               $modmonthnum = substr( $moddateraw, 5, 2 );
               $modmonthtime = mktime(0, 0, 0, $modmonthnum);
               $modmonth = strftime("%b", $modmonthtime);
               $modday = substr( $moddateraw, 8, 2 );
           } else if ( strpos( $moddateraw , "-" ) == 2  ) {  // Ex: X Files Italian
               $modyear = substr( $moddateraw, 6, 4 );
               $modmonthnum = substr( $moddateraw, 3, 2 );
               $modmonthtime = mktime(0, 0, 0, $modmonthnum);
               $modmonth = strftime("%b", $modmonthtime);
               $modday = substr( $moddateraw, 0, 2 );
           } else if ( strpos( $moddateraw , " " ) == 4 ) {  // Ex: Peter Lowe’s Ad Servers
               $modyear = substr( $moddateraw, 12, 4 );
               $modmonth = substr( $moddateraw, 8, 3 );
               $modday = substr( $moddateraw, 5, 2 );
           } else if ( strpos( $moddateraw , " " ) == 1 ) {  // Ex: Maciej’s Polish
               $modyear = substr( $moddateraw, strposX( $moddateraw, " ", 2 ) + 1, 4 );
               $modmonth = substr( $moddateraw, strpos( $moddateraw, " " ) + 1, 3 );
               if ( strpos( $moddateraw, " " ) == 1 ) {
                   $modday = substr( $moddateraw, 0, 1 );
               } else {
                   $modday = substr( $moddateraw, 0, 2 );
               }
           } else if ( strpos( $moddateraw , " " ) == 3 ) {  // Ex: Nauscopicos Spanish and English
               $modyear = substr( $moddateraw, 8, 4 );
               $modmonth = substr( $moddateraw, 0, 3 );
               $modday = substr( $moddateraw, 4, 2 );
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
    } else if ( strpos( $list , "Last Modified: " ) !== false ) {
        $moddateplus = explode( "Last Modified: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 10 );
        if ( strpos( $moddateraw , "-" ) == 2  ) {  // Ex: Schack’s Danish
            $modyear = substr( $moddateraw, 6, 4 );
            $modmonthnum = substr( $moddateraw, 3, 2 );
            $modmonthtime = mktime(0, 0, 0, $modmonthnum);
            $modmonth = strftime("%b", $modmonthtime);
            $modday = substr( $moddateraw, 0, 2 );
        } else {  // Ex: EasyList Chinese
            $modyear = substr( $moddateraw, 7, 4 );
            $modmonth = substr( $moddateraw, 3, 3 );
            $modday = substr( $moddateraw, 0, 2 );
        }
    } else if ( strpos( $list , "Version: " ) !== false ) {
        $moddateplus = explode( "Version: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 12 );
        if ( strpos( $moddateraw , "."  == 1 ) ) {  // Ex: Anti-Adblock Killer
            $moddateraw = "";
        } else if ( strpos( $moddateraw , "!" ) == 3 ) {  // Ex: Daniel’s I Don’t Care about Cookies
            $moddateraw = "";
        } else {
            $modyear = substr( $moddateraw, 0, 4 );
            $modmonthnum = substr( $moddateraw, 4, 2 );
            $modmonthtime = mktime(0, 0, 0, $modmonthnum);
            $modmonth = strftime("%b", $modmonthtime);
            $modday = substr( $moddateraw, 6, 2 );
        }
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
        } else if ( strpos( $moddateplus, "-------------" !== false ) ) {  // Ex: MVPS Hosts
            $modyear = substr( $moddateraw, strposX( $moddateraw, "-", 2 ) + 1, 4 );
            $modmonth = substr( $moddateraw, 0, 3 );
            $modday = substr( $moddateraw, strpos( $moddateraw, "-" ) + 1, 2 );
        } else {
            $modyear = substr( $moddateraw, 7, 4 );
            $modmonth = substr( $moddateraw, 3, 3 );
            $modday = substr( $moddateraw, 0, 2 );
        }
    } else if ( strpos( $list , "Last Update: " ) !== false ) {  // Ex: G&J Hosts
        $moddateplus = explode( "Last Update: " , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 16 );
        $modyear = substr( $moddateraw, 12, 4 );
        $modmonth = substr( $moddateraw, 8, 3 );
        $modday = substr( $moddateraw, 5, 2 );
    } else if ( strpos( $list , "Last updated: " ) !== false ) {
        $moddateplus = explode( "Last updated: " , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 16 );
        if ( ctype_digit( substr( $moddateraw, 14, 2 ) ) ) {  // Ex: Dan Pollock’s Hosts
            $modyear = substr( $moddateraw, 12, 4 );
            $modmonth = substr( $moddateraw, 8, 3 );
            $modday = substr( $moddateraw, 5, 2 );
        } else {  // Ex: Malware Domain Hosts
            $modyear = "20" . substr( $moddateraw, 12, 2 );
            $modmonth = substr( $moddateraw, 8, 3 );
            $modday = substr( $moddateraw, 5, 2 );
        }
    } else if ( strpos( $list , "Changelog:" ) !== false ) {  // Ex: Adaway
        $moddateplus = explode( "Changelog:" , $list )[1];
        $moddateraw = substr( $moddateplus , 0, 14 );
        $modyear = substr( $moddateraw, strpos( $moddateraw, "# " ) + 1, 4 );
        $modmonthnum = substr( $moddateraw, strpos( $moddateraw, "# " ) + 6, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, strpos( $moddateraw, "# " ) + 9, 2 );
    } else if ( strpos( $list , "pro Adblock Plus! " ) !== false ) {  // Ex: Dajbych’s Czech
        $moddateplus = explode( "pro Adblock Plus! " , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 10 );
        $modyear = substr( $moddateraw, 6, 4 );
        $modmonthnum = substr( $moddateraw, 3, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 0, 2 );
    } else if ( strpos( $list , "Paskutinis atnaujinimas: " ) !== false ) { // Ex: EasyList Lithuanian
        $moddateplus = explode( "Paskutinis atnaujinimas: " , $list )[1];
        $moddateraw = substr( $moddateplus , 0 , 10 );
        $modyear = substr( $moddateraw, 0, 4 );
        $modmonthnum = substr( $moddateraw, 5, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 8, 2 );
    } else if ( strpos( $list , "Last updated " ) !== false ) {  // Ex: Gardar’s Icelandic
        $moddateplus = explode( "Last updated " , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 30 );
        $modyearpre = substr( $moddateraw, strposX( $moddateraw, " ", 2 ) + 1 );
        $modyear = substr( $modyearpre, 0 , 4 );
        $modmonth = substr( $moddateraw, strpos( $moddateraw, "/" ) + 1, 3 );
        $modday = substr( $moddateraw, 0, 2 );
    } else if ( strpos( $list , "Malware URLs generated in " ) !== false ) {  // Ex: Joxean’s Malware
        $moddateplus = explode( "Malware URLs generated in " , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 25 );
        $modyear = substr( $moddateraw, strposX( $moddateraw, " ", 4 ) + 1, 4 );
        $modmonth = substr( $moddateraw, 4, 3 );
        $modday = substr( $moddateraw, 8, 2 );
    } else if ( strpos( $list , "last updated on:		" ) !== false ) {  // Ex: hpHosts
        $moddateplus = explode( "last updated on:		" , $list )[1];
        $moddateraw = substr( $moddateplus, 0, 10 );
        $modyear = substr( $moddateraw, 6, 4 );
        $modmonthnum = substr( $moddateraw, 3, 2 );
        $modmonthtime = mktime(0, 0, 0, $modmonthnum);
        $modmonth = strftime("%b", $modmonthtime);
        $modday = substr( $moddateraw, 0, 2 );
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
        $moddate = "Conn. Error";
    } else if ( $moddateraw == ""  ) {
        $moddate = "N/A";
    } else {
        $moddate = "Parse Error";
    }

    // set random transient timeout between 24 and 48 hrs
    $timeout = mt_rand( DAY_IN_SECONDS , DAY_IN_SECONDS * 2 );

    // set transient for url updated date
    set_transient( 'mod_' . esc_url( $url ), $moddate , $timeout );
  }
  return $moddate;
}

/**
 * Find the position of the Xth occurrence of a substring in a string
 * http://stackoverflow.com/a/18589825
 * @param $haystack
 * @param $needle
 * @param $number integer > 0
 * @return int
 */
function strposX( $haystack, $needle, $number ) {
    if( $number == '1' ){
        return strpos( $haystack, $needle );
    } elseif( $number > '1' ){
        return strpos( $haystack, $needle, strposX( $haystack, $needle, $number - 1 ) + strlen( $needle ) );
    } else {
        return error_log( "Error: Value for parameter $number is out of range." );
    }
}

?>
