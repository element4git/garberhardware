<?php
/*
Plugin Name: Google Placees - google places api
Plugin URI: http://joshhoffman.nyc/wordpress/plugins/googleplaces/
Description: Connect to google places
Version: 1
Author: Josh
Author URI: http://joshhoffman.nyc
Text Domain: google-places
Domain Path: /
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}

echo wp_cache_get( '$key', '$group' );

/* Important constants */
define( 'GOOGLE_PLACES_VERSION', '1.0.0' );
define( 'GOOGLE_PLACES_URL', plugin_dir_url( __FILE__ ) );

/* Required helper functions */
// include_once( dirname( __FILE__ ) . '/inc/helpers.php' );
// include_once( dirname( __FILE__ ) . '/inc/settings.php' );
// include_once( dirname( __FILE__ ) . '/inc/widget.php' );

add_action( 'plugins_loaded', 'google_places_loaded' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */

function getGooglePlace(){
    //
    //extract data from the post
    //set POST variables
    $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJq_exWetZwokR31EVR4dJvps&key=AIzaSyC-xaqORfb5SyR1xbM10L_Tjekyh9Z3pk8';

    //url-ify the data for the POST

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, 'http://garber.dev/');

    //execute post
    $google_place = curl_exec($ch);

    return $google_place;


    //close connection
    curl_close($ch);

}

function google_places_loaded() {
	if( !isset($_SESSION['google_places']) ){
		$google_places = wp_cache_get('google_places');
		if ( false === $google_places ) {
			$google_places = json_decode(getGooglePlace());
			wp_cache_set( 'google_places', $google_places, '',3600);
		} 
		$_SESSION['google_places'] = $google_places;
	}
}

add_shortcode( 'google_places', 'google_places_display' );

function google_places_display( $atts, $content = NULL ) {
	/* Display the form */
	$weekdayHours = $_SESSION['google_places']->result->opening_hours->weekday_text;
	$timeHTML = '';
	foreach($weekdayHours as $weekIndex=>$week){
		$timeHTML .= '<div class="week '.$weekIndex.'">'.$week.'</div>';
	}
	return "<div class='section-header'><h2>Open Hours</h2>".$timeHTML.'</div>';

}

//add_action( 'wp_enqueue_scripts', 'google_places_scripts' );

function google_places_scripts() {

	/* style for frontpage contact */
	wp_enqueue_style( 'pirate_forms_front_styles', PIRATE_FORMS_URL . 'css/front.css' );

	/* recaptcha js */
	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	if( !empty($pirate_forms_options) ):

		if( !empty($pirate_forms_options['pirateformsopt_recaptcha_secretkey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_sitekey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_field']) && ($pirate_forms_options['pirateformsopt_recaptcha_field'] == 'yes') ):

			wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );

			wp_enqueue_script( 'pirate_forms_scripts', plugins_url( 'js/scripts.js', __FILE__ ), array('jquery','recaptcha') );

		endif;

	endif;

	wp_enqueue_script( 'pirate_forms_scripts_general', plugins_url( 'js/scripts-general.js', __FILE__ ), array('jquery') );

	$pirate_forms_errors = '';

	if( !empty($_SESSION['pirate_forms_contact_errors'])):
		$pirate_forms_errors = $_SESSION['pirate_forms_contact_errors'];
	endif;

	wp_localize_script( 'pirate_forms_scripts_general', 'pirateFormsObject', array(
		'errors' => $pirate_forms_errors
	) );

}

//add_action( 'admin_enqueue_scripts', 'pirate_forms_admin_css' );

function google_places_forms_admin_css() {

	global $pagenow;

	if ( !empty($pagenow) && ( $pagenow == 'options-general.php' || $pagenow == 'admin.php' )
		&& isset( $_GET['page'] ) && $_GET['page'] == 'pirate-forms-admin' ) {

		wp_enqueue_style( 'pirate_forms_admin_styles', PIRATE_FORMS_URL . 'css/wp-admin.css' );

		wp_enqueue_script( 'pirate_forms_scripts_admin', plugins_url( 'js/scripts-admin.js', __FILE__ ), array('jquery') );
		wp_localize_script( 'pirate_forms_scripts_admin', 'cwp_top_ajaxload', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
}
