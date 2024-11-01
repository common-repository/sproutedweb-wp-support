<?php
/*
 * Plugin Name: WP Support by SproutedWeb
 * Plugin URI: https://sproutedweb.com
 * Description: Instantly access 24x7 WordPress support, GTMetrix scans, and maintenance options right from your Dashboard.
 * Author: SproutedWeb
 * Contributors: SproutedWeb, CharlesTheCoder
 * Author URI: https://sproutedweb.com
 * Text Domain: sproutedweb-wp-support
 * Version: 2.3
 * Requires at least: 5.6
 * Tested up to: 6.1
 */

defined('ABSPATH') or exit;
if (!class_exists('SproutedWeb_wp_chatinc')) {
    class SproutedWeb_wp_chatinc
    {
        public function __construct()
        {
            ########	REGISTER ACTIVATION HOOK	##########
            register_activation_hook(__FILE__, array($this, 'sproutedwebchat_activate'));
            register_deactivation_hook(__FILE__, array($this, 'sproutedwebchat_deactivation'));
            ########	REGISTER UNINSTALL HOOK	##########
            // register_uninstall_hook(__FILE__, array($this,'sproutedwebchat_uninstall'));
            ########	REDIRECT AFTER ACTIVATION	##########
            add_action('admin_init', array($this, 'sproutedwebchat_plugin_redirect'));
            ########	ADMIN PRINT SCRIPT			##########
            add_action('admin_enqueue_scripts', array($this, "sproutedwebchat_print_script"));
            ########	ENQUEUE SCRIPT/STYLE		##########
            add_action('admin_enqueue_scripts', array($this, "sproutedwebchat_enqueue_script"));
            ########	ADD FIELD UNDER SETTING		##########
            add_filter('admin_init', array($this, "sproutedwebchat_register_fields"));

            ########		SETTING SAVE CALL				##########
            add_action('wp_ajax_sprouted_setting_save', array($this, 'sprouted_setting_save'));
            add_action('wp_ajax_nopriv_sprouted_setting_save', array($this, 'sprouted_setting_save'));

            ########		GTMETRIX SCAN CALL				##########
            add_action('wp_ajax_sprouted_gtmetrix_scan', array($this, 'sprouted_gtmetrix_scan'));
            add_action('wp_ajax_nopriv_sprouted_gtmetrix_scan', array($this, 'sprouted_gtmetrix_scan'));

            add_action('wp_ajax_sprouted_gtmetrix_scan_result', array($this, 'sprouted_gtmetrix_scan_result'));
            add_action('wp_ajax_nopriv_sprouted_gtmetrix_scan_result', array($this, 'sprouted_gtmetrix_scan_result'));

            ########		KEY VERIFY API CALL				##########
            add_action('wp_ajax_sprouted_license_verify', array($this, 'sprouted_license_verify'));
            add_action('wp_ajax_nopriv_sprouted_license_verify', array($this, 'sprouted_license_verify'));

            ########		KEY DEACTIVATE API CALL				##########
            add_action('wp_ajax_sprouted_license_deactivate', array($this, 'sprouted_license_deactivate'));
            add_action('wp_ajax_nopriv_sprouted_license_deactivate', array($this, 'sprouted_license_deactivate'));

            ########		GTMETRIX KEY VERIFY API CALL				##########
            add_action('wp_ajax_sprouted_gtmetrix_verify', array($this, 'sprouted_gtmetrix_verify'));
            add_action('wp_ajax_nopriv_sprouted_gtmetrix_verify', array($this, 'sprouted_gtmetrix_verify'));

            ########		GTMETRIX FULL REPORT DOWNLOAD API CALL				##########
            add_action('wp_ajax_sprouted_gtmetrix_download_report', array($this, 'sprouted_gtmetrix_download_report'));
            add_action('wp_ajax_nopriv_sprouted_gtmetrix_download_report', array($this, 'sprouted_gtmetrix_download_report'));

            add_action('wp_ajax_sprouted_gtmetrix_history', array($this, 'sprouted_gtmetrix_history'));
            add_action('wp_ajax_nopriv_sprouted_gtmetrix_history', array($this, 'sprouted_gtmetrix_history'));

            add_action('admin_menu', array($this, 'sproutedwebchat_menu_pages'));
            add_action('wp_dashboard_setup', array($this, 'sproutedwebchat_dashboard_widget'));

            ########		NONCE KEY				##########
            $this->nonce_key = 'sproutedweb-chatinc';
            ########		IS SCRIPT ACTIVE		##########
            $this->sprouted_gtmetrix_key = sanitize_text_field(trim(get_option('sproutedwebchat_gtmetrix_key', '')));
            $this->sprouted_gtmetrix_credit = sanitize_text_field(trim(get_option('sproutedwebchat_gtmetrix_credit', '')));

            $this->chatinc_active = get_option('sproutedwebchat_active', '');
            $this->sprouted_license_key = sanitize_text_field(trim(get_option('sproutedwebchat_license_key', '')));
            $this->sprouted_license_details = get_option('sproutedwebchat_license_details', '');
            $this->sprouted_other_details = get_option('sproutedwebchat_other_details', '');
            $this->sprouted_common = get_option('sproutedwebchat_other_common', '');
            $this->gtmetrix_location = get_option('sproutedwebchat_gtmetrix_location', '');
            $this->gtmetrix_browsers = get_option('sproutedwebchat_gtmetrix_browsers', '');
            ########		LICENSE KEY				##########
            $this->time_now = date('Y-m-d H:i:s');
            $this->update_interval = (!empty($this->sprouted_other_details) && !empty($this->sprouted_other_details['interval']) ? (int)sanitize_text_field(trim($this->sprouted_other_details['interval'])) : 30); //MINUTE
            $this->gtmetrix_interval = (!empty($this->sprouted_common) && !empty($this->sprouted_common['interval']) ? (int)sanitize_text_field(trim($this->sprouted_common['interval'])) : 30); //MINUTE
            $this->limit = (!empty($this->sprouted_common) && !empty($this->sprouted_common['limit']) ? (int)sanitize_text_field(trim($this->sprouted_common['limit'])) : 10); //MINUTE
            $this->free_scan = 50;
            $this->license_key = 10152642;
            $this->author_website = esc_url_raw('https://sproutedweb.com');
            $this->author_support = esc_url_raw('https://sproutedweb.com/support');
            $this->fb_community = esc_url_raw('https://www.facebook.com/groups/AllThingsWordPress');
            $this->gtmetrix = esc_url_raw('https://gtmetrix.com/');
            $this->key_verify_url = esc_url_raw('https://sproutedweb.com/api/license-verify.php');
            $this->scan_url = esc_url_raw('https://sproutedweb.com/api/gtmetrix-scan.php');
            $this->gtmetrix_url = esc_url_raw('https://sproutedweb.com/api/gtmetrix-verify.php');
            $this->report_download_url = esc_url_raw('https://sproutedweb.com/api/gtmetrix-report-download.php');
            $this->add_time_link = esc_url_raw('https://sproutedweb.com/shop/wordpress-support-session');
        }

        /*****        PLUGIN DE-ACTIVATION OPTION SAVE    *****/
        public function sproutedwebchat_deactivation()
        {
            update_option('sproutedwebchat_active', 0);
        }

        /*****        PLUGIN ACTIVATION OPTION SAVE    *****/
        public function sproutedwebchat_activate()
        {
            add_option('sproutedwebchat_activate', true);
            delete_option('sproutedwebchat_active');
            add_option('sproutedwebchat_active', 1);
            $gtMetrixLog = get_option('sproutedwebchat_gtmetrix_log');
            $gtMetrixCredit = get_option('sproutedwebchat_gtmetrix_credit');
            if (!$gtMetrixLog && !$gtMetrixCredit) {
                add_option('sproutedwebchat_gtmetrix_credit', $this->free_scan);
            }

            $this->activateSettingSave();
        }

        public function activateSettingSave()
        {
            global $table_prefix, $wpdb;
            $gtmetrix_table = $table_prefix . "sprouted_gtmetrix";
            #Check to see if the table exists already, if not, then create it
            if ($wpdb->get_var("SHOW TABLES LIKE '$gtmetrix_table'") != $gtmetrix_table) {
                $sql = "CREATE TABLE IF NOT EXISTS `$gtmetrix_table` (
						  `id` bigint(20) NOT NULL AUTO_INCREMENT,
						  `test_id` varchar(100) NOT NULL,
						  `scan_url` text NOT NULL,
						  `load_time` varchar(10) NOT NULL,
						  `page_speed` varchar(10) NOT NULL,
						  `yslow` varchar(10) NOT NULL,
						  `region` varchar(200) NOT NULL,
						  `browser` varchar(200) NOT NULL,
						  `response_log` longtext NOT NULL,
						  `resources` longtext NOT NULL,
						  `is_free` tinyint(4) NOT NULL,
						  `created` datetime NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
                require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
                dbDelta($sql);
            } else {
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$gtmetrix_table` LIKE 'scan_url';")) {
                    $sql = "ALTER TABLE `$gtmetrix_table` ADD `scan_url` TEXT NOT NULL  AFTER `test_id`;";
                    $wpdb->query($sql);
                }
            }
        }

        /*****        DASHBOARD WIDGET    *****/
        public function sproutedwebchat_dashboard_widget()
        {
            global $wp_meta_boxes;
            add_meta_box('my_dashboard_widget', 'WordPress Support Plan Details', array($this, 'sproutedwebchat_dashboard_help'), 'dashboard', 'side', 'high');
        }

        /*****        DASHBOARD WIDGET TEXT    *****/
        public function sproutedwebchat_dashboard_help()
        {
            $html = '';
            $logo = plugins_url('assets/images/logo.png', __FILE__);
            $otherCommon = get_option('sproutedwebchat_other_common');
            if ($otherCommon && !empty($otherCommon['logo'])) {
                $logo = $otherCommon['logo'];
            }
            if (!empty($this->sprouted_license_key)) {
                $features = get_option('sproutedwebchat_plan_features');
                $other = get_option('sproutedwebchat_other_details');

                $license_detail = $this->sprouted_license_details;
                $statusColor = 'red';
                if (!empty($license_detail)) {
                    if (strtolower($license_detail['license_status']) == 'active') {
                        $statusColor = 'green';
                    }
                    $remainingKey = ($license_detail['max_instance_number'] - $license_detail['number_use_remaining']);
                }

                $html .= '<a href="' . $this->author_website . '">
							<img class="size-medium alignnone" src="' . $logo . '" target="_blank">
						</a>
						<br><br>
						<span style="text-decoration: underline"><strong>' . get_option('sproutedwebchat_plan_name') . '</strong> </span>
						<br><br>
						<strong>License: ' . $this->sprouted_license_key . '</strong>
						' . (!empty($license_detail) && $license_detail['max_instance_number'] ? '<p><strong>Active Sites : <b style="color:' . $statusColor . '">' . $remainingKey . ' out of ' . $license_detail['max_instance_number'] . ' Allowed</b></strong></p>' : '') . '
						<p>
							<strong>Renews: <span style="color: #008000">' . ($other && $other['next_payment_date'] ? date('F jS, Y', strtotime($other['next_payment_date'])) : 'N/A') . '</span> </strong>
							' . (!empty($license_detail) && $license_detail['license_status'] ? '<strong>Status : <b style="color:' . $statusColor . '">' . ucfirst($license_detail['license_status']) . '</b></strong>' : '') . '
						</p>
						<span style="color: #008000">
							<strong>
								<span style="color: #333333">
									Support Time Remaining: <span style="color: #008000">' . ($other && $other['remaining_time'] ? $other['remaining_time'] : 'N/A') . '</span>
								</span>
							</strong>
							<a href="javascript:void(0);" target="_blank" rel="noopener">
								<span style="color: #000000">
									<strong>
										<span style="text-decoration: underline"><a href="' . $this->add_time_link . '" target="_blank">Add Time</a></span>
									</strong>
								</span>
							</a>
						</span>
						<br>
						<br>
						<div style="color: #333333" style="margin-top:20px;">
							<strong>
								<a href="' . admin_url('admin.php?page=sprouted-features') . '">View My Plan Features</a>
							</strong>
						</div>';
                if ($this->chatinc_active == 1) {
                    $html .= '<br>
							<p>
								To instantly connect with our WP Support team, simply click on the chat icon in the lower right corner of this screen.
							</p>';
                }
            } else {
                $html .= '<p>Please <a href="' . $this->author_website . '/wordpress-maintenance-plans" target="_blank">Select a Plan</a> or <a href="' . admin_url('admin.php?page=sprouted-setting') . '">Activate Your License Key</a></p>';
            }
            echo $html;
        }

        /*****        PLUGIN MENU PAGE ADDD    *****/
        public function sproutedwebchat_menu_pages()
        {
            global $submenu;
            add_menu_page('WP Support Plan', 'WP Support Plan', 'manage_options', 'sprouted-setting', array($this, 'sprouted_settings'), 'dashicons-format-chat', 2);
            add_submenu_page('sprouted-setting', 'Settings', 'Settings', 'manage_options', 'sprouted-setting', array($this, 'sprouted_settings'));
            if ($this->sprouted_license_key) {
                add_submenu_page('sprouted-setting', ' My Plan Features', ' My Plan Features', 'manage_options', 'sprouted-features', array($this, 'sprouted_features'));
            }
            add_submenu_page('sprouted-setting', 'Performance Scan', 'Performance Scan', 'manage_options', 'sprouted-scan', array($this, 'sprouted_scan'));
            add_submenu_page('sprouted-setting', 'Join Our FB Community', 'Join Our FB Community', 'manage_options', 'sprouted-fb-community', array($this, 'sprouted_fb_community'));
        }

        public function sprouted_fb_community()
        {
        }

        public function sprouted_scan()
        {
            $this->activateSettingSave();
            $this->get_template('gtmetrix-scan');

        }

        public function sprouted_features()
        {
            $features = get_option('sproutedwebchat_plan_features');
            $license_log = get_option('sproutedwebchat_license_log');
            $license_detail = $this->sprouted_license_details;

            $statusColor = 'red';
            if (!empty($license_detail)) {
                if (strtolower($license_detail['license_status']) == 'active') {
                    $statusColor = 'green';
                }
                $remainingKey = ($license_detail['max_instance_number'] - $license_detail['number_use_remaining']);
            }
            $html = '<div class="wrap sproutedweb">
						<div class="updated sproutedweb-message" style="display:none;"><p>Successful</p></div>
						<div id="dashboard-widgets-wrap">
							<div id="dashboard-widgets" class="metabox-holder">
								<div id="side-sortables2">
									<div class="postbox-container" style="width:54%">
										<div class="meta-box-sortables">
											<div class="postbox">
												<h3 class="hndle" style="cursor: unset;">
													<span>Your WP Support Plan Includes</span>
												</h3>
												<div class="inside">';

            //$html .=							(!empty($license_detail) && $license_detail['max_instance_number'] ? '<p><strong>Active Sites : <b style="color:'.$statusColor.'">'.$remainingKey.' out of '.$license_detail['max_instance_number'].' Allowed</b></strong></p><hr>' : '');
            $f = 1;
            foreach ($features as $feature) {
                $html .= html_entity_decode($feature);
                $f++;
            }
            $html .= '</div>
											</div>
										</div>
									</div>
									
									
									<div class="postbox-container license-log" style="width:45%">
										<div class="meta-box-sortables">
											<div class="postbox">
												<h3 class="hndle" style="cursor: unset;">
													<span>License Key Activation Log</span>
												</h3>
												<div class="inside">';

            $html .= (!empty($license_detail) && $license_detail['max_instance_number'] ? '<p><strong>License Key : <code>' . $this->sprouted_license_key . '</code></strong></p><p><strong>Active Sites : <b style="color-:' . $statusColor . '">' . $remainingKey . ' out of ' . $license_detail['max_instance_number'] . ' Allowed</b></strong></p><hr>' : '');
            $l = 1;
            $html .= '<table width="100%" border="1" cellpadding="5" cellspacing="0" style="text-align:center;">
							<thead>
								<th>S.No</th>
								<th>Domain</th>
								<th>Activation Date</th>
								<th>Status</th>
								<th>Manage</th>
							</tbody>';
            foreach ($license_log as $k => $log) {
                $html .= '<tr>
								<td>' . $l . '</td>
								<td>' . $log['domain'] . '</td>
								<td>' . date('F jS, Y h:i A', strtotime($log['modified'])) . '</td>
								<td class="license-status-' . $k . '" data-site_url="' . $log['site_url'] . '" data-key_status="' . $log['status'] . '">' . ($log['status'] ? '<b style="color:green;">Active</b>' : '<b style="color:red;">Inactive</b>') . '</td>
								<td width="32%">
			<input name="status-' . $k . '" id="status_activate" value="1" type="radio" class="license-action" ' . ((int)$log['status'] == 1 ? 'checked' : '') . ' data-index="' . $k . '" data-site_url="' . $log['site_url'] . '" data-key_status="' . $log['status'] . '">
			<label for="status_activate">Activate</label>
			
			<input name="status-' . $k . '" id="status_deactivate" value="0" type="radio" class="license-action" ' . ((int)$log['status'] == 0 ? 'checked' : '') . ' data-index="' . $k . '" data-site_url="' . $log['site_url'] . '" data-key_status="' . $log['status'] . '">
			<label for="status_deactivate">Deactivate</label>
								</td>
							</tr>';
                $l++;
            }
            if (empty($license_log)) {
                $html .= '<tr><td colspan="5">No Record Found</td>';
            }
            $html .= '</table>';
            $html .= '</div>
											</div>
										</div>
									</div>
									
									
								</div>
							</div>
						</div>
					</div>';
            echo $html;
        }

        /*****        SETTING SAVE    *****/
        public function sprouted_setting_save()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['_nonce']) && isset($_POST['is_enable'])) {
                    if (wp_verify_nonce(sanitize_text_field($_POST['_nonce']), $this->nonce_key)) {
                        update_option('sproutedwebchat_active', ((int)sanitize_text_field($_POST['is_enable']) == 1 ? 1 : 0));
                        $response = array('status' => 1, 'message' => 'Successful Saved.');
                    } else {
                        $response = array('status' => 0, 'message' => 'Invalid nonce.');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'All fields are required');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            wp_send_json($response);
        }

        /*****        GTMETRIX SCAN CALL    *****/
        public function sprouted_gtmetrix_scan_result()
        {
            $this->get_template('gtmetrix-scan');
            exit();
        }

        /*****        GTMETRIX FULL REPORT DOWNLOAD CALL    *****/
        public function sprouted_gtmetrix_download_report()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['report_url'] && $_POST['testid'])) {
                    global $wpdb;
                    $postData = array('_nonce' => wp_create_nonce($this->nonce_key), 'report_url' => $_POST['report_url']);
                    $url = $this->report_download_url;
                    $httpResponse = $this->sproutedwebHTTPPost($url, $postData);
                    if (is_wp_error($httpResponse)) {
                        $error_message = $httpResponse->get_error_message();
                        $response = array('status' => 0, 'message' => "Something went wrong: $error_message");
                    } else {
                        if (!empty($httpResponse['response']['code']) && $httpResponse['response']['code'] == 200) {
                            $bodyResult = json_decode(wp_remote_retrieve_body($httpResponse), true);
                            // echo json_decode($bodyResult['report']);
                            if ($bodyResult['status']) {
                                $reportPath = $this->get_plugin_dir() . "assets/gtmetrix/pdf/report_pdf-{$_POST['testid']}.pdf";
                                chmod($this->get_plugin_dir() . "assets/gtmetrix/pdf/", 0777);
                                file_put_contents($reportPath, base64_decode($bodyResult['report']));
                                $response = array('status' => 1, 'message' => 'Successful', 'report' => $bodyResult['report']);
                            } else {
                                $response = array('status' => 0, 'message' => $bodyResult['message']);
                            }
                        } else {
                            $response = array('status' => 0, 'message' => 'Try Again.');
                        }
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Invalid data');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            a:
            wp_send_json($response);
        }

        /*****        GTMETRIX SCAN CALL    *****/
        public function sprouted_gtmetrix_scan()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['_nonce']) && !empty($_POST['scan_url'])) {
                    if (wp_verify_nonce(sanitize_text_field($_POST['_nonce']), $this->nonce_key)) {
                        $license_key = sanitize_text_field(trim($this->sprouted_gtmetrix_key));
                        if (!$license_key) {
                            //$response = array('status'=>0,'message'=>'License key missing.');
                            //goto a;
                        }
                        global $wpdb;
                        $site_url = site_url();
                        $scan_url = $_POST['scan_url'];
                        $urlDetails = parse_url($scan_url);
                        $urlDomain = $urlDetails['host'];
                        if (!filter_var(gethostbyname($urlDomain), FILTER_VALIDATE_IP) || $urlDomain == 'localhost') {
                            $response = array('status' => 0, 'message' => 'Can\'t use on localhost or invalid server.');
                            goto a;
                        }
                        if (!empty($_POST['scan_location'])) {
                            $key = array_search($_POST['scan_location'], array_column($this->gtmetrix_location, 'id'));
                            if (isset($this->gtmetrix_location[$key])) {
                                $region = $this->gtmetrix_location[$key]['name'];
                            } else {
                                $region = 'Default';
                            }
                        } else {
                            $region = 'Default';
                        }
                        if (!empty($_POST['scan_browser'])) {
                            $keyBr = array_search($_POST['scan_browser'], array_column($this->gtmetrix_browsers, 'id'));
                            if (isset($this->gtmetrix_browsers[$keyBr])) {
                                $browser = $this->gtmetrix_browsers[$keyBr]['name'];
                            } else {
                                $browser = 'Default';
                            }
                        } else {
                            $browser = 'Default';
                        }
                        $postData = array('_nonce' => sanitize_text_field($_POST['_nonce']), 'site_url' => sanitize_text_field($site_url), 'scan_url' => sanitize_text_field($scan_url), 'license_key' => $license_key, 'scan_location' => (int)$_POST['scan_location'], 'region' => $_POST['scan_location'], 'browser' => $_POST['scan_browser']);
                        $url = $this->scan_url;
                        $httpResponse = $this->sproutedwebHTTPPost($url, $postData);
                        $response = $this->sproutedGtmetrixScan($httpResponse, $region, $browser);

                    } else {
                        $response = array('status' => 0, 'message' => 'Invalid nonce.');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Nonce missing');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            a:
            wp_send_json($response);
        }

        /*****        LICENSE VERFIY API CALL    *****/
        public function sprouted_license_verify()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['_nonce']) && !empty($_POST['license_key'])) {
                    if (wp_verify_nonce(sanitize_text_field($_POST['_nonce']), $this->nonce_key)) {
                        $license_key = sanitize_text_field(trim($_POST['license_key']));
                        $site_url = site_url();

                        $postData = array('_nonce' => sanitize_text_field($_POST['_nonce']), 'license_key' => $license_key, 'key_status' => 1, 'site_url' => sanitize_text_field($site_url), 'date' => date('Y-m-d H:i:s'), 'gtmetrix' => 1, 'action' => 'verify');
                        $response = $this->getLicenseInfo($license_key, $postData);
                    } else {
                        $response = array('status' => 0, 'message' => 'Invalid nonce.');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Enter License key');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            wp_send_json($response);
        }

        /*****        LICENSE VERFIY API CALL    *****/
        public function sprouted_license_deactivate()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if ($this->sprouted_license_key) {
                    if (!empty($_POST['site_url']) && isset($_POST['key_status'])) {
                        $license_key = sanitize_text_field(trim($this->sprouted_license_key));
                        $site_url = sanitize_text_field(trim($_POST['site_url']));
                        if ((int)sanitize_text_field($_POST['key_status']) == 1) {
                            $key_status = 0;
                        } else {
                            $key_status = 1;
                        }
                        $postData = array('_nonce' => wp_create_nonce($this->nonce_key), 'license_key' => $license_key, 'key_status' => $key_status, 'site_url' => sanitize_text_field($site_url), 'date' => date('Y-m-d H:i:s'), 'action' => 'deactivate');
                        $response = $this->getLicenseInfo($license_key, $postData);
                    } else {
                        $response = array('status' => 0, 'message' => 'Enter License key');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Sorry, this license key doesn\'t exist or is not valid.');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            wp_send_json($response);
        }

        /*****        WP REMOTE POST    *****/
        public function sproutedwebHTTPPost($url, $postData)
        {
            $requestVerify = wp_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 600,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(
                        'Content-Type: application/json',
                        'Content-Length: ' . count($postData)
                    ),
                    'body' => $postData,
                    'cookies' => array()
                )
            );
            return $requestVerify;
        }

        /*****        GTMETRIX INFO FUNCTION    *****/
        public function sproutedGtmetrixScan($httpResponse, $region = '', $browser = '')
        {
            global $wpdb;
            if (is_wp_error($httpResponse)) {
                $error_message = $httpResponse->get_error_message();
                $response = array('status' => 0, 'message' => "Something went wrong: $error_message");
            } else {
                if (!empty($httpResponse['response']['code']) && $httpResponse['response']['code'] == 200) {
                    $bodyResult = json_decode(wp_remote_retrieve_body($httpResponse), true);

                    if ($bodyResult['status']) {
                        if (empty($bodyResult['results']) || empty($bodyResult['testid'])) {
                            $response = array('status' => 0, 'message' => 'Try Again.');
                        } else {
                            $gt = $bodyResult['results'];
                            $other = $bodyResult['other_detail'];
                            $wpdb->insert(
                                "{$wpdb->prefix}sprouted_gtmetrix",
                                array(
                                    'test_id' => $bodyResult['testid'],
                                    'scan_url' => $bodyResult['scan_url'],
                                    'load_time' => $gt['fully_loaded_time'],
                                    'page_speed' => $gt['pagespeed_score'],
                                    'yslow' => $gt['yslow_score'],
                                    'region' => $region,
                                    'browser' => $browser,
                                    'resources' => json_encode($bodyResult['resources']),
                                    'response_log' => json_encode($bodyResult['results']),
                                    'is_free' => $bodyResult['is_free'],
                                    'created' => $this->time_now
                                )
                            );
                            if ($wpdb->insert_id) {
                                update_option('sproutedwebchat_gtmetrix_credit', ((int)$other['gtmetrix_credit']));

                                $otherDetails['last_update_time'] = $this->time_now;
                                $otherDetails = array_merge($otherDetails, $bodyResult['other_detail']);
                                update_option('sproutedwebchat_gtmetrix_other_details', $this->recursive_sanitize_text_field($otherDetails));
                                if (!empty($bodyResult['screenshot'])) {
                                    $screenshotPath = $this->get_plugin_dir() . "assets/gtmetrix/screenshots/screenshot-{$bodyResult['testid']}.jpg";
                                    chmod($this->get_plugin_dir() . "assets/gtmetrix/screenshots/", 0777);

                                    $reportPath = $this->get_plugin_dir() . "assets/gtmetrix/pdf/report_pdf-{$bodyResult['testid']}.pdf";
                                    chmod($this->get_plugin_dir() . "assets/gtmetrix/pdf/", 0777);
                                    file_put_contents($reportPath, base64_decode($bodyResult['report_pdf_full']));
                                    file_put_contents($screenshotPath, base64_decode($bodyResult['screenshot']));
                                }
                                $response = array('status' => 1, 'message' => $bodyResult['message'], 'bodyResult' => $bodyResult);
                            } else {
                                $response = array('status' => 0, 'message' => 'Try Again.');
                            }
                        }
                    } else {
                        $response = array('status' => 0, 'message' => $bodyResult['message'], 'is_free' => 1);
                        if (isset($bodyResult['is_free'])) {
                            update_option('sproutedwebchat_gtmetrix_credit', 0);
                            $response['is_free'] = 0;
                        }
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Try Again.');
                }
            }
            return $response;
        }

        /*****        LICENSE INFO FUNCTION    *****/
        public function getLicenseInfo($license_key, $postData)
        {
            $requestVerify = wp_remote_post($this->key_verify_url, array(
                    'method' => 'POST',
                    'timeout' => 50,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(
                        'Content-Type: application/json',
                        'Content-Length: ' . count($postData)
                    ),
                    'body' => $postData,
                    'cookies' => array()
                )
            );
            if (is_wp_error($requestVerify)) {
                $error_message = $requestVerify->get_error_message();
                $response = array('status' => 0, 'message' => "Something went wrong: $error_message");
            } else {
                if (!empty($requestVerify['response']['code']) && $requestVerify['response']['code'] == 200) {
                    $otherDetails['last_update_time'] = $this->time_now;
                    $bodyResult = json_decode(wp_remote_retrieve_body($requestVerify), true);
                    $mySourceUrl = parse_url(site_url());
                    $myDomain = $mySourceUrl['host'];

                    $actionUrl = isset($postData['site_url']) ? parse_url($postData['site_url']) : parse_url(site_url());
                    $actionDomain = $actionUrl['host'];
                    if ($bodyResult['status']) {
                        $logArray = $bodyResult['license_log'];
                        $haveMyDomain = 1;
                        if (!empty($logArray)) {
                            $activeDomains = array_column($logArray, 'domain');
                            if (!in_array($myDomain, $activeDomains)) {
                                $haveMyDomain = 0;
                            }
                        }
                        $bodyResult['haveMyDomain'] = $haveMyDomain;
                        if (($postData['action'] == 'deactivate' && $myDomain == $actionDomain) || $haveMyDomain == 0) {
                            delete_option('sproutedwebchat_license_key');
                            delete_option('sproutedwebchat_plan_name');
                            delete_option('sproutedwebchat_license_log');
                            delete_option('sproutedwebchat_plan_features');
                            delete_option('sproutedwebchat_other_details');
                            delete_option('sproutedwebchat_license_details');
                        } else {
                            $otherDetails = array_merge($otherDetails, $bodyResult['other_detail']);
                            update_option('sproutedwebchat_license_key', $license_key);
                            update_option('sproutedwebchat_plan_name', sanitize_text_field($bodyResult['plan_name']));
                            update_option('sproutedwebchat_license_log', $this->recursive_sanitize_text_field($bodyResult['license_log']));
                            update_option('sproutedwebchat_plan_features', $this->recursive_sanitize_html_field($bodyResult['features']));
                            update_option('sproutedwebchat_other_details', $this->recursive_sanitize_text_field($otherDetails));
                            update_option('sproutedwebchat_license_details', $this->recursive_sanitize_text_field($bodyResult['license_details']));
                        }
                        if (!empty($bodyResult['gtmetrix_location'])) {
                            update_option('sproutedwebchat_gtmetrix_location', $this->recursive_sanitize_text_field($bodyResult['gtmetrix_location']));
                        }
                        $response = $bodyResult;
                    } else {
                        $response = array('status' => 0, 'message' => $bodyResult['message']);
                    }
                } else {
                    $response = array('status' => 0, 'message' => "Server not reachable. Try again");
                }
            }

            return $response;
        }

        /*****        SETTING PAGE    *****/
        public function sprouted_settings()
        {
            $license_detail = $this->sprouted_license_details;
            $statusColor = 'red';
            if (!empty($license_detail) && strtolower($license_detail['license_status']) == 'active') {
                $statusColor = 'green';
            }

            echo '<div class="wrap sproutedweb">
						<div class="updated sproutedweb-message" style="display:none;"><p>Successful</p></div>
						<h1 id="add-new-user">Setting</h1>
						<form method="post" id="setting-save">
							<input type="hidden" name="_nonce" value="' . wp_create_nonce($this->nonce_key) . '" />
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row">
											<label for="sproutedwebchat_active">WP Support Chat Bar</label>										
										</th>
										<td>
											<select id="sproutedwebchat_active" style="width: 10%;" name="sproutedwebchat_active">
												<option value="1" ' . ($this->chatinc_active == 1 ? 'selected' : '') . '>Enable</option>
												<option value="0" ' . ($this->chatinc_active == 0 ? 'selected' : '') . '>Disable</option>
											</select>
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit"><input type="submit" name="save_setting" id="save_setting" class="button button-primary" value="Save"></p>
						</form>
						<h1 id="add-new-user">WP Support License</h1>
						<form method="post" id="verify-key">
							<input type="hidden" name="_nonce" value="' . wp_create_nonce($this->nonce_key) . '" />
							<table class="form-table">
								<tbody>
									<tr class="form-field form-required">
										<th scope="row">
											<label for="sproutedwebchat_active">Enter License Key Here:</label>										
										</th>
										<td>
											<input name="key" type="text" id="key" value="" aria-required="true" autocapitalize="none" autocorrect="off" style="width: 25em;">
											' . ($this->sprouted_license_key ? '<p>Verified Key : ' . $this->sprouted_license_key . '</p>' : '') . '
											' . (!empty($license_detail) && $license_detail['license_status'] ? '<span>License Status : <b style="color:' . $statusColor . '">' . ucfirst($license_detail['license_status']) . '</b></span>' : '') . '
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit"><input type="submit" name="verify" id="verify" class="button button-primary" value="Verify"></p>
						</form>
					</div>';
        }

        /*****        PLUGIN REDIRECT AFTER ACTIVATE    *****/
        public function sproutedwebchat_plugin_redirect()
        {
            global $pagenow;
            $otherDetails = $this->sprouted_other_details;
            $otherDetailsGt = get_option('sproutedwebchat_gtmetrix_other_details');
            if (!empty($this->sprouted_license_key) && !empty($otherDetails['last_update_time'])) {
                $time_now = strtotime($this->time_now);
                $last_update = strtotime($otherDetails['last_update_time']);
                $lastUpdateMin = round(abs($time_now - $last_update) / 60, 2);

                if ($lastUpdateMin >= $this->update_interval) {
                    $license_key = sanitize_text_field(trim($this->sprouted_license_key));

                    $postData = array('_nonce' => wp_create_nonce($this->nonce_key), 'license_key' => $license_key, 'refresh' => 1, 'action' => 'refresh');
                    $response = $this->getLicenseInfo($license_key, $postData);
                    if (!empty($response) && $response['status'] && $response['haveMyDomain'] == 0) {
                        wp_redirect(admin_url('admin.php?page=sprouted-setting'));
                    }
                }
            }
            if ((!$this->chatinc_active && !empty($otherDetailsGt['last_update_time'])) || empty($this->sprouted_gtmetrix_key)) {
                $time_now = strtotime($this->time_now);
                $last_updateGt = strtotime($otherDetailsGt['last_update_time']);
                $lastUpdateMinGt = round(abs($time_now - $last_updateGt) / 60, 2);

                if (($lastUpdateMinGt >= $this->gtmetrix_interval)) {
                    $site_url = site_url();
                    $gtmetrixUrl = $this->gtmetrix_url;
                    $license_gtmetrix_key = sanitize_text_field(trim($this->sprouted_gtmetrix_key));
                    $postDataGt = array('_nonce' => wp_create_nonce($this->nonce_key), 'license_key' => $license_gtmetrix_key, 'refresh' => 1, 'gtmetrix' => 1, 'browsers' => 1, 'packages' => 1, 'site_url' => $site_url);
                    $httpResponse = $this->sproutedwebHTTPPost($gtmetrixUrl, $postDataGt);
                    if (is_wp_error($httpResponse)) {
                        $error_message = $httpResponse->get_error_message();
                        $response = array('status' => 0, 'message' => "Something went wrong: $error_message");
                    } else {
                        if (!empty($httpResponse['response']['code']) && $httpResponse['response']['code'] == 200) {
                            $bodyResult = json_decode(wp_remote_retrieve_body($httpResponse), true);
                            if (isset($bodyResult['other_detail']['gtmetrix_credit'])) {
                                update_option('sproutedwebchat_gtmetrix_credit', $bodyResult['other_detail']['gtmetrix_credit']);
                            }
                            if (isset($bodyResult['gtmetrix_packages'])) {
                                update_option('sproutedwebchat_gtmetrix_packages', $bodyResult['gtmetrix_packages']);
                            }
                            if (!empty($bodyResult['license_log'])) {
                                update_option('sproutedwebchat_gtmetrix_log', $this->recursive_sanitize_text_field($bodyResult['license_log']));
                            }
                            if (!empty($bodyResult['common_other'])) {
                                update_option('sproutedwebchat_other_common', $this->recursive_sanitize_text_field($bodyResult['common_other']));
                            }
                            $otherDetails = array();
                            $otherDetails['last_update_time'] = $this->time_now;
                            if (!empty($bodyResult['other_detail'])) {
                                $otherDetails = array_merge($otherDetails, $bodyResult['other_detail']);
                            }
                            update_option('sproutedwebchat_gtmetrix_other_details', $this->recursive_sanitize_text_field($otherDetails));
                            if (!empty($bodyResult['gtmetrix_location'])) {
                                update_option('sproutedwebchat_gtmetrix_location', $this->recursive_sanitize_text_field($bodyResult['gtmetrix_location']));
                            }
                            if (!empty($bodyResult['gtmetrix_browsers'])) {
                                update_option('sproutedwebchat_gtmetrix_browsers', $this->recursive_sanitize_text_field($bodyResult['gtmetrix_browsers']));
                            }
                            $response = $bodyResult;
                        } else {
                            $response = array('status' => 0, 'message' => 'Try Again.');
                        }
                    }
                }
            }
            if (get_option('sproutedwebchat_activate', false)) {
                delete_option('sproutedwebchat_activate');
                if (!isset($_GET['activate-multi'])) {
                    wp_redirect(admin_url('admin.php?page=sprouted-setting'));
                }
            }
            if ($pagenow == 'admin.php' && (!empty($_GET['page']) && $_GET['page'] == 'sprouted-knowledgebase')) {
                wp_redirect($this->author_support);
                exit;
            }
            if ($pagenow == 'admin.php' && (!empty($_GET['page']) && $_GET['page'] == 'sprouted-fb-community')) {
                wp_redirect($this->fb_community);
                exit;
            }
            if ($pagenow == 'admin.php' && (!empty($_GET['page']) && $_GET['page'] == 'sprouted-scan')) {
                // wp_redirect( $this->gtmetrix );
                // exit;
            }
        }

        /*****        PLUGIN FIELD REGISTER IN SETTING    *****/
        public function sproutedwebchat_register_fields()
        {
            register_setting('general', 'sproutedwebchat_active', 'esc_attr');
            add_settings_field('sproutedwebchat_active', '<label for="sproutedwebchat_active">' . __('Chat Support', 'sproutedwebchat_active') . '</label>', array($this, 'my_general_sproutedwebchat'), 'general');
        }

        /*****        PLUGIN ACTIVATION FIELD ADD    *****/
        public function my_general_sproutedwebchat()
        {
            echo '<select id="sproutedwebchat_active" style="width: 10%;" name="sproutedwebchat_active">
					<option value="1" ' . ($this->chatinc_active == 1 ? 'selected' : '') . '>Enable</option>
					<option value="0" ' . ($this->chatinc_active == 0 ? 'selected' : '') . '>Disable</option>
				</select>';
        }

        /*****        PLUGIN SCRIPT PRINT IN ADMIN FOOTER    *****/
        public function sproutedwebchat_print_script()
        {
            $script = "<!-- Start of SproutedWeb WP Support code -->";
            if ($this->chatinc_active == 1) {
                $script .= "<script type='text/javascript'>\n";
                $script .= "window.__lc = window.__lc || {};
					window.__lc.license = " . $this->license_key . ";
					(function() {
					  var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
					  lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
					  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
					})();";
                $script .= "\n</script>\n";
                $script .= "<noscript>\n
								<a href='https://www.livechatinc.com/chat-with/10152642/' rel='nofollow'>24x7 WP Support</a>,
								powered by <a href='https://www.sproutedweb.com' rel='noopener nofollow' target='_blank'>SproutedWeb WP Support</a>
							\n</noscript>\n";
            }
            $script .= '<!-- End of SproutedWeb WP Support code -->';
            echo $script;
        }

        /*****        RECURSIVE SANITIZE TEXT    *****/
        public function recursive_sanitize_text_field($array)
        {
            if (is_array($array)) {
                foreach ($array as $key => &$value) {
                    if (is_array($value)) {
                        $value = $this->recursive_sanitize_text_field($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                }
            }
            return $array;
        }

        /*****        RECURSIVE SANITIZE    *****/
        public function recursive_sanitize_html_field($array)
        {
            if (is_array($array)) {
                foreach ($array as $key => &$value) {
                    if (is_array($value)) {
                        $value = $this->recursive_sanitize_html_field($value);
                    } else {
                        $value = sanitize_text_field(htmlentities($value));
                    }
                }
            }
            return $array;
        }

        public function get_template($template)
        {
            $template_name = 'templates/' . $template . '.php';
            include $this->get_plugin_dir() . $template_name;
        }

        public function get_plugin_dir()
        {
            return plugin_dir_path(__FILE__);
        }

        public function get_plugin_url($url)
        {
            return plugins_url($url, __FILE__);
        }

        public function gtmetrix_code($value)
        {
            if ($value >= 90) {
                $code = array('code' => 'A', 'color' => '4bb32b');
            } elseif ($value >= 80 && $value < 90) {
                $code = array('code' => 'B', 'color' => '90c779');
            } elseif ($value >= 70 && $value < 80) {
                $code = array('code' => 'C', 'color' => 'd2bf2f');
            } elseif ($value >= 60 && $value < 70) {
                $code = array('code' => 'D', 'color' => 'e4a63d');
            } elseif ($value >= 50 && $value < 60) {
                $code = array('code' => 'E', 'color' => 'ca7c55');
            } else {
                $code = array('code' => 'F', 'color' => 'd62f30');
            }
            return $code;
        }

        public function formatSizeUnits($bytes)
        {
            if ($bytes >= 1073741824) {
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                $bytes = number_format($bytes / 1024, 2) . ' KB';
            } elseif ($bytes > 1) {
                $bytes = $bytes . ' bytes';
            } elseif ($bytes == 1) {
                $bytes = $bytes . ' byte';
            } else {
                $bytes = '0 bytes';
            }

            return $bytes;
        }

        /*****        GTMETRIX KEY VERFIY API CALL    *****/
        public function sprouted_gtmetrix_verify()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['_nonce']) && !empty($_POST['license_key'])) {
                    if (wp_verify_nonce(sanitize_text_field($_POST['_nonce']), $this->nonce_key)) {
                        $license_key = sanitize_text_field(trim($_POST['license_key']));
                        $site_url = site_url();
                        $gtmetrixUrl = $this->gtmetrix_url;
                        $postData = array('_nonce' => sanitize_text_field($_POST['_nonce']), 'license_key' => $license_key, 'key_status' => 1, 'site_url' => sanitize_text_field($site_url), 'date' => date('Y-m-d H:i:s'), 'gtmetrix' => 1);
                        $httpResponse = $this->sproutedwebHTTPPost($gtmetrixUrl, $postData);
                        if (is_wp_error($httpResponse)) {
                            $error_message = $httpResponse->get_error_message();
                            $response = array('status' => 0, 'message' => "Something went wrong: $error_message");
                        } else {
                            if (!empty($httpResponse['response']['code']) && $httpResponse['response']['code'] == 200) {
                                $bodyResult = json_decode(wp_remote_retrieve_body($httpResponse), true);
                                update_option('sproutedwebchat_gtmetrix_key', $license_key);
                                if (isset($bodyResult['other_detail']['gtmetrix_credit'])) {
                                    update_option('sproutedwebchat_gtmetrix_credit', $bodyResult['other_detail']['gtmetrix_credit']);
                                }
                                update_option('sproutedwebchat_gtmetrix_log', $this->recursive_sanitize_text_field($bodyResult['license_log']));

                                if (!empty($bodyResult['gtmetrix_location'])) {
                                    update_option('sproutedwebchat_gtmetrix_location', $this->recursive_sanitize_text_field($bodyResult['gtmetrix_location']));
                                }
                                $otherDetails['last_update_time'] = $this->time_now;
                                $otherDetails = array_merge($otherDetails, $bodyResult['other_detail']);
                                update_option('sproutedwebchat_gtmetrix_other_details', $this->recursive_sanitize_text_field($otherDetails));
                                $response = $bodyResult;
                            } else {
                                $response = array('status' => 0, 'message' => 'Try Again.');
                            }
                        }
                    } else {
                        $response = array('status' => 0, 'message' => 'Invalid nonce.');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Enter License key');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            a:
            wp_send_json($response);
        }

        public function sprouted_gtmetrix_history()
        {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                if (!empty($_POST['_nonce']) && !empty($_POST['page_no'])) {
                    if (wp_verify_nonce(sanitize_text_field($_POST['_nonce']), $this->nonce_key)) {
                        $offset = !empty($_POST['page_no']) ? (($_POST['page_no'] - 1) * $this->limit) : 0;
                        $html = $this->getGtmetrixScanHistory($this->limit, $offset);
                        $response = array('status' => 1, 'message' => 'Successful', 'html' => $html);
                    } else {
                        $response = array('status' => 0, 'message' => 'Invalid nonce');
                    }
                } else {
                    $response = array('status' => 0, 'message' => 'Invalid Request');
                }
            } else {
                $response = array('status' => 0, 'message' => 'Invalid Request');
            }
            a:
            wp_send_json($response);
        }

        public function getGtmetrixScanHistory($limit = 10, $offset = 0)
        {
            include $this->get_plugin_dir() . 'inc/Pagination.class.php';

            global $wpdb;
            $totalRecord = $wpdb->get_var("SELECT count(id) FROM {$wpdb->prefix}sprouted_gtmetrix");
            $pagConfig = array(
                'baseURL' => admin_url('admin.php?page=sprouted-scan'),
                'ajax' => true,
                'totalRows' => $totalRecord,
                'perPage' => $this->limit
            );
            $pagination = new Pagination($pagConfig);
            $html = '<h4>Scan History</h4>
					<table class="table table-bordered" id="gtmetrix-scan-history" style="background:#F9F9F9;">
						<thead>
						  <tr>
							<th width="15%">Download Report</th>
							<th width="30%">URL</th>
							<th width="10%">Date</th>
							<th width="10%">Load Time</th>
							<th width="15%">Page Speed</th>
							<th width="10%">YSlow</th>
							<th width="10%">Region</th>
						  </tr>
						</thead>
						<tbody>';
            $scanHistory = $wpdb->get_results("SELECT `test_id`,`scan_url`, `load_time`, `page_speed`, `yslow`,`browser`, `region`,`resources`,`response_log`, `created` FROM {$wpdb->prefix}sprouted_gtmetrix ORDER BY id desc LIMIT $offset,$limit", ARRAY_A);
            if ($scanHistory) {
                foreach ($scanHistory as $s => $history) {
                    $resources = json_decode($history['resources'], true);
                    $html .= '<tr>
						<td>';
                    if (file_exists($this->get_plugin_dir() . 'assets/gtmetrix/pdf/report_pdf-' . $history['test_id'] . '.pdf')) {
                        $html .= '<a href="' . $this->get_plugin_url('assets/gtmetrix/pdf/report_pdf-' . $history['test_id'] . '.pdf') . '" class="" target="_blank">Full Report</a>';
                    } else {
                        $html .= '<a href="javascript:void(0);" class="download-full-report" data-full_report="' . $resources['report_pdf_full'] . '" data-testid="' . $history['test_id'] . '">Full Report</a>';
                    }
                    $html .= '</td>
						<td><a href="' . $history['scan_url'] . '" target="_blank">' . $history['scan_url'] . '</a></td>
						<td>' . $history['created'] . '</td>
						<td>' . round($history['load_time'] / 1000, 2) . '</td>
						<td>' . $history['page_speed'] . '%</td>
						<td>' . $history['yslow'] . '%</td>
						<td>' . $history['region'] . '</td>
					 </tr>';
                }
            } else {
                $html .= '<tr><td colspan="7" style="text-align:center;">No Record Found</td></tr>';
            }
            $html .= '</tbody>
			  </table>';
            $html .= $pagination->createLinks();
            return $html;
        }

        /*****        PLUGIN EXTERNAL STYPE & STYLE REGISTER    *****/
        public function sproutedwebchat_enqueue_script()
        {
            global $pagenow;
            wp_enqueue_script('sproutedweb-script', plugins_url('assets/js/script.js', __FILE__), array(), false, true);
            wp_localize_script('sproutedweb-script', 'sproutedweb', array('ajax_url' => admin_url('admin-ajax.php'), 'key_verify_url' => $this->key_verify_url, 'admin_url' => admin_url(), 'features_page' => admin_url('admin.php?page=sprouted-features'), 'sprouted_scan' => admin_url('admin.php?page=sprouted-scan'), 'site_url' => site_url(), 'setting_page' => admin_url('admin.php?page=sprouted-setting'), '_nonce' => wp_create_nonce($this->nonce_key)));
            wp_enqueue_style('sproutedweb-style', plugins_url('assets/css/style.css', __FILE__), array());
            wp_enqueue_script('sproutedweb-showLoading', plugins_url('assets/js/jquery.showLoading.js', __FILE__), array(), false, true);
            wp_enqueue_style('sproutedweb-showLoading', plugins_url('assets/css/showLoading.css', __FILE__), array());
            wp_enqueue_style('sproutedweb-gtmetrix', plugins_url('assets/css/gtmetrix.css', __FILE__), array());
            wp_enqueue_style('sproutedweb-pagination2', plugins_url('assets/css/pagination2.css', __FILE__), array());

            if ($pagenow == 'admin.php' && (!empty($_GET['page']) && $_GET['page'] == 'sprouted-scan')) {
                wp_enqueue_style('bootstrap.min', plugins_url('assets/css/bootstrap.min.css', __FILE__), array());
            }
        }
    }


}
/*****        PLUGIN FUNCTION    *****/
function SproutedWeb_wp_chatinc()
{
    new SproutedWeb_wp_chatinc();
}

/*****        PLUGIN CLASS CALL    *****/
SproutedWeb_wp_chatinc();
?>