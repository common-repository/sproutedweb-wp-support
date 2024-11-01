<?php
//UNINSTALL MAGIC
/*****		PLUGIN UNINSTALL OPTION *****/
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
$table_name = $wpdb->prefix . 'sprouted_gtmetrix';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
delete_option('sproutedwebchat_active');
delete_option('sproutedwebchat_license_key');
delete_option('sproutedwebchat_license_details');
delete_option('sproutedwebchat_plan_features');
delete_option('sproutedwebchat_plan_name');
delete_option('sproutedwebchat_other_details');
delete_option('sproutedwebchat_gtmetrix_key');
delete_option('sproutedwebchat_gtmetrix_credit');
delete_option('sproutedwebchat_gtmetrix_log');
delete_option('sproutedwebchat_other_common');
delete_option('sproutedwebchat_gtmetrix_other_details');
?>