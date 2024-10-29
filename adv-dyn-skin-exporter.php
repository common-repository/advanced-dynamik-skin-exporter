<?php
/*
Plugin Name: Advanced Dynamik Skin Exporter
Plugin URI:  http://captaingenesis.com/downloads/advanced-dynamik-skin-exporter
Description: Exports all files included in the root of the Dynamik Skin Folder.
Version:     1.3.5
Author:      Jason Seabolt and Captain Genesis	
Author URI:  http://captaingenesis.com
Text Domain: cg_adse
Domain Path: /lang
 */
 
 

/************************************
* the code below is just a standard
* options page. Substitute with
* your own.
*************************************/



/************************************
* this illustrates how to activate
* a license key
*************************************/

function cg_adse_include_scripts() {
	$ver = 1.3;
	$cg_adse_js = 'cg_adse_js';
	$js_path = plugins_url( '/lib/js/avd-dyn-skin-exporter.js', __FILE__ );
	wp_enqueue_script( $cg_adse_js, $js_path, array(), $ver, true );
}
add_action( 'admin_enqueue_scripts', 'cg_adse_include_scripts' );
 
add_action('admin_init', 'cg_dynamik_design_export');

function cg_dynamik_design_export( ) {

add_action( 'current_screen', 'thisScreen' );
function thisScreen() {

    $currentScreen = get_current_screen();

    if( $currentScreen->id === "genesis_page_dynamik-design" ) {
	
			$cg_export_skin_action = $_POST;
if ( $cg_export_skin_action ){
	$cg_adse_name = $cg_export_skin_action['design_export_name'];
	$cg_adse_action = $cg_export_skin_action['action'];
		if ( isset($cg_export_skin_action['settings_only'] )) {
			$cg_adse_settings = $cg_export_skin_action['settings_only'];
		} else { 
			$cg_adse_settings = '';
		} 
}		
else {
	$cg_adse_name = '';
	$cg_adse_action = '';
	$cg_adse_settings = '';
}

if ($cg_adse_action  == 'cg_dynamik_design_export'){
    $export_data = array();
	
	$export_data['dynamik_gen_design_options'] = get_option( 'dynamik_gen_design_options' );
	$export_data['dynamik_gen_responsive_options'] = get_option( 'dynamik_gen_responsive_options' );

	$dynamik_datestamp = dynamik_sanatize_string( gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) ), true );
	if( $cg_adse_name )
	{
		$dynamik_export_dat = dynamik_sanatize_string( $cg_adse_name, true ) . '.dat';
	}
	else
	{
		$dynamik_export_dat = 'dynamik_skin_' . $dynamik_datestamp . '.dat';
	}
	$cheerios = serialize( $export_data );

	if( $cg_adse_settings )
	{
		header( "Content-type: text/plain" );
		header( "Content-disposition: attachment; filename=$dynamik_export_dat" );
		header( "Content-Transfer-Encoding: binary" );
		header( "Pragma: no-cache" );
		header( "Expires: 0" );
		echo $cheerios; 
		exit();
	}
	else
	{
		dynamik_folders_open_permissions();
		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		if( $cg_export_skin_action['design_export_name'] )
		{
			$dynamik_export_zip = dynamik_sanatize_string( $cg_export_skin_action['design_export_name'], true ) . '.zip';
		}
		else
		{
			$dynamik_export_zip = 'dynamik_skin_' . $dynamik_datestamp . '.zip';
		}
		$dynamik_gen_active_skin_path = dynamik_get_active_skin_folder_path();
		$tmp_path = dynamik_get_stylesheet_location( 'path' ) . 'tmp';
		$dat_filename = $tmp_path . '/' . $dynamik_export_dat;
	
		$tmp_image_folder = $tmp_path . '/images';
		$tmp_adthumbs_folder = $tmp_image_folder . '/adminthumbnails';
		$image_folder = dynamik_get_stylesheet_location( 'path' ) . 'images';
		$adthumbs_folder = $image_folder . '/adminthumbnails';

		if( !is_dir( $tmp_path ) )
		{
			mkdir( $tmp_path, 0755, true );
		}
		if( !is_dir( $tmp_image_folder ) )
		{
			mkdir( $tmp_image_folder, 0755, true );
		}
		if( !is_dir( $tmp_adthumbs_folder ) )
		{
			mkdir( $tmp_adthumbs_folder, 0755, true );
		}
		
		$dat_file = fopen( $dat_filename, 'x' );
		fwrite( $dat_file, $cheerios );
		fclose ( $dat_file );
		
		$handle = opendir( $dynamik_gen_active_skin_path );
		while( false !== ( $file = readdir( $handle ) ) )
		{
			$ext = strtolower( substr( strrchr( $file, '.' ), 1 ) );
			$all_skin_files = $tmp_path . '/';
			dynamik_recurse_copy( $dynamik_gen_active_skin_path . '/', $all_skin_files  );
		
		}

		$export_files = array( $all_skin_files );
		
		$dynamik_pclzip = new PclZip( $tmp_path . '/' . $dynamik_export_zip );
		$dynamik_zipped = $dynamik_pclzip->create( $export_files, PCLZIP_OPT_REMOVE_PATH, $tmp_path );
		if( $dynamik_zipped == 0 )
		{
			die( "Error : " . $dynamik_pclzip->errorInfo( true ) );
		}
		
		if( ob_get_level() )
		{
			ob_end_clean();
		}
		header( "Cache-Control: public, must-revalidate" );
		header( "Pragma: hack" );
		header( "Content-Type: application/zip" );
		header( "Content-Disposition: attachment; filename=$dynamik_export_zip" );
		readfile( $tmp_path . '/' . $dynamik_export_zip );
		dynamik_delete_temp_files( $tmp_path );
		dynamik_delete_temp_files( $tmp_image_folder );
		dynamik_delete_temp_files( $tmp_adthumbs_folder );
		dynamik_folders_close_permissions();
		exit();
	}
}
}


   }
    
}