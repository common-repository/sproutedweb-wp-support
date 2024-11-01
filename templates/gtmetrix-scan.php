<?php
global $wpdb;

$scanHistory = $wpdb->get_row( "SELECT `test_id`,`scan_url`, `load_time`, `page_speed`, `yslow`,`browser`, `region`,`resources`,`response_log`, `created` FROM {$wpdb->prefix}sprouted_gtmetrix ORDER BY id desc", ARRAY_A );
$pageLoad = 'N/A';
$lastReport = 'N/A';
$region = 'N/A';
$pageSpeed = 0;
$ySlow = 0;
$loadTime = 0;
$requests = 0;
$lastReportTime = 0;
$browser = 0;
$screenshot = 0;
$scan_url = site_url();
$pageSize = '0KB';
$pageSpeedCode = [];
$ySlowCode = [];
$scanResult = [];
$screenshotDefault = $this->get_plugin_url('/assets/images/rsz_1default-thumb.png');
if($scanHistory){
	$pageLoad = round($scanHistory['load_time']/1000,2);
	
	$diff = abs(strtotime($this->time_now) - strtotime($scanHistory['created']));

	$years = floor($diff / (365*60*60*24));
	$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	$hourdiff = round((strtotime($this->time_now) - strtotime($scanHistory['created']))/3600, 1);
	$minutes = round($diff / 60);
	if($minutes<60){
		$lastReport = $minutes.' minutes ago';
	} elseif($minutes>=60 && $hourdiff<24){
		$lastReport = $hourdiff.' hours ago';
	} elseif($hourdiff>=24 && $days<31){
		$lastReport = $days.' days ago';
	} elseif($days>=31 && $months<=12){
		$lastReport = $months.' month ago';
	} else {
		$lastReport = $year.' year ago';
	}
	$pageSpeed = $scanHistory['page_speed'];
	$loadTime = round($scanHistory['load_time']/1000,2);
	$ySlow = $scanHistory['yslow'];
	$scanResult = json_decode($scanHistory['response_log'],true);
	$requests = $scanResult['page_elements'];
	$region = $scanHistory['region'];
	$browser = $scanHistory['browser'];
	$lastReportTime = $scanHistory['created'];
	$pageSize = $this->formatSizeUnits($scanResult['page_bytes']);
	$scan_url = $scanHistory['scan_url'];
	$pageSpeedCode = $this->gtmetrix_code($pageSpeed);
	$ySlowCode = $this->gtmetrix_code($ySlow);
	if(file_exists($this->get_plugin_dir()."assets/gtmetrix/screenshots/screenshot-{$scanHistory['test_id']}.jpg")){
		$screenshot =  $this->get_plugin_url('/assets/gtmetrix/screenshots/screenshot-'.$scanHistory['test_id'].'.jpg');
	}
	
}
$gtMetrixLog = get_option( 'sproutedwebchat_gtmetrix_log');
$gtPackages = get_option('sproutedwebchat_gtmetrix_packages');
$gtMetrixCredit = $this->sprouted_gtmetrix_credit;


$offset = !empty($_GET['page_no'])?(($_GET['page_no']-1)*$this->limit):0;
?>
<div class="wrap sproutedweb">
	<div class="col-md-12" style="">
	<div class="updated sproutedweb-message" style="display:none;"><p>Successful</p></div>
	<div class="col-md-8">
		<div class="row" style="background:#F9F9F9;padding:10px;">
			<?php if($scanHistory){ ?>
			<article>
			   <div class="report-head">
				  <div class="report-screenshot">
					 <div class="analyze-screenshot-wrapper analyze-screenshot-desktop">
						<div class="analyze-screenshot">
						   <div class="analyze-screenshot-image" style="padding-bottom: 56.223%">
							  <img src="<?php echo ($screenshot ? $screenshot : $screenshotDefault); ?>" alt="">
						   </div>
						</div>
					 </div>
				  </div>
				  <div class="report-details">
					 <h3>Performance Report for:</h3>
					 <h3><a href="<?php echo $scan_url; ?>" target="_blank" rel="nofollow noopener noreferrer" class="no-external"><?php echo $scan_url; ?></a></h3>
					 <div class="report-details-content">
						<div class="report-details-info">
						   <div class="report-details-item report-details-timestamp clear">
							  <label>Report generated:</label>
							  <div class="report-details-value">
								 <?php echo ($lastReportTime ? date('D, M d, Y, h:i A',strtotime($lastReportTime)) : 'N/A')?>
							  </div>
						   </div>
						   <div class="report-details-item clear">
							  <label>Test Server Region:</label>
							  <div class="report-details-value">
								 <?php echo $region; ?>
							  </div>
						   </div>
						   <div class="report-details-item report-details-browser clear">
							  <label>Using:</label>
							  <div class="report-details-value">
								 <?php echo ($browser ? $browser : 'N/A'); ?>
							  </div>
						   </div>
						</div>
					 </div>
				  </div>
			   </div>
			   <div class="report-performance clear">
				  <div class="report-scores">
					 <h3>Performance Scores</h3>
					 <div class="box clear">
						<div class="report-score">
						   <h4>PageSpeed Score</h4>
						   <span class="report-score-grade color-grade-<?php echo ($pageSpeedCode ? $pageSpeedCode['code'] : 'E'); ?>"><span><?php echo ($pageSpeedCode ? $pageSpeedCode['code'] : 'E'); ?></span><span class="report-score-percent">(<?php echo $pageSpeed; ?>%)</span></span>
						</div>
						<div class="report-score">
						   <h4>YSlow Score</h4>
						   <span class="report-score-grade color-grade-<?php echo ($ySlowCode ? $ySlowCode['code'] : 'E'); ?>"><span><?php echo ($ySlowCode ? $ySlowCode['code'] : 'E'); ?></span><span class="report-score-percent">(<?php echo $ySlow; ?>%)</span></span>
						</div>
					 </div>
				  </div>
				  <div class="report-page-details">
					 <h3>Page Details</h3>
					 <div class="box clear">
						<div class="report-page-detail">
						   <h4>Load Time</h4>
						   <span class="report-page-detail-value"><?php echo $loadTime; ?>s</span>
						   <i class="site-average sprite-average-below hover-tooltip tooltipstered" data-tooltip-interactive=""></i>
						</div>
						<div class="report-page-detail report-page-detail-size">
						   <h4>Total Page Size</h4>
						   <span class="report-page-detail-value"><?php echo $pageSize; ?></span>
						   <i class="site-average sprite-average-below hover-tooltip tooltipstered" data-tooltip-interactive=""></i>
						</div>
						<div class="report-page-detail report-page-detail-requests">
						   <h4>Requests</h4>
						   <span class="report-page-detail-value"><?php echo $requests; ?></span>
						   <i class="site-average sprite-average-below hover-tooltip tooltipstered" data-tooltip-interactive=""></i>
						</div>
					 </div>
				  </div>
			   </div>
			</article>
			<?php } else {
				echo '<div class="col-md-12" style="height:400px;display:table;"><h4 class="text-center" style="display: table-cell;vertical-align: middle;">Please Run Your First Scan To See Your Results.</h4></div>';
			} ?>
		</div>
		<div class="row" style="background:#F9F9F9;padding:10px;margin-top:20px;" id="gtmetrix-history-section">
					<?php echo $this->getGtmetrixScanHistory($this->limit,$offset); ?>
		</div>
	</div>
	
		<div class="col-md-4 col-sm-12 col-xs-12">
				<div class="row" style="background:#F9F9F9;margin-left:0;">
					<div class="col-md-12 gtmetrix-section">
						 <h4>Perform Your GTMetrix Scan Now</h4>
							<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce($this->nonce_key); ?>" />
							 <div class="form-group">
								  <label for="sel1">Enter a URL To Test</label>
								  <input class="form-control" type="text" name="scan_url" placeholder="https://yourdomain.com" />
							</div>
							 <div class="form-group">
								  <label for="sel1">Select a Testing Location</label>
								  <select class="form-control" name="location">
									<?php 
										if($this->gtmetrix_location){
											foreach($this->gtmetrix_location as $gtmetrix){
									?>
									<option value="<?php echo $gtmetrix['id']; ?>" <?php echo ($gtmetrix['default'] ? 'selected' : ''); ?>><?php echo $gtmetrix['name']; ?></option>
									<?php } } else { ?>
									<option value="-1">Default</option>
									<?php } ?>
								  </select>
							</div>
							 <div class="form-group">
								  <label for="sel1">Select a Testing Browser</label>
								  <select class="form-control" name="browser">
									<?php 
										if($this->gtmetrix_browsers){
											foreach($this->gtmetrix_browsers as $browsers){
									?>
									<option value="<?php echo $browsers['id']; ?>" <?php echo (strpos($browsers['browser'], 'chrome') !== false ? 'selected' : ''); ?>><?php echo $browsers['name']; ?></option>
									<?php } } else { ?>
									<option value="-1">Default</option>
									<?php } ?>
								  </select>
							</div>
							<div class="col-md-12">
								<p><strong>Remaining Scan Credits : <?php echo $gtMetrixCredit; ?></strong></p>
							</div>
							<div class="form-group text-center">
							  <button type="button" class="btn btn-primary text-center">Start Scan</button>
							</div>
					</div>
				</div>

				<div class="row" style="background:#F9F9F9;margin-top:20px;margin-left:0;">
					<div class="col-md-12 gtmetrix-key-section">
						<h4>Add or Redeem Scan Credits</h4>
						<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce($this->nonce_key); ?>" />
							 <div class="form-group">
								  <label for="sel1">Redeem a Scan Package</label>
								  <input class="form-control" name="gtmetrix-key" />
							</div>
							<div class="col-md-12">
								<?php if($this->sprouted_gtmetrix_key){ ?>
								<p>Verified Key : <span class="label label-success" style="font-size:13px;"><?php echo $this->sprouted_gtmetrix_key; ?></span></p>
								<?php } ?>
							</div>
							<div class="form-group text-center">
							  <button type="button" class="btn btn-primary text-center">Verify</button>
							</div>
					</div>
				</div>

			<?php if($gtPackages && is_array($gtPackages)){ ?>

				<div class="row" style="background:#F9F9F9;margin-top:20px;margin-left:0;">
					<div class="col-md-12" id="gtmetrix-package-section">
						<h4>Add More Scan Credits</h4>
							<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce($this->nonce_key); ?>" />
							 <div class="form-group">
								  <label for="sel1">Redeem a Scan Package</label>
								  <select class="form-control" name="gtmetrix-packages">
									<option value="">Select a Package</option>
									<?php foreach($gtPackages as $gtPackage){ ?>
									<option value="<?php echo $gtPackage['url']; ?>"><?php echo $gtPackage['name']; ?></option>
									<?php } ?>
								  </select>
							</div>
					</div>
				</div>

			<?php } ?>
		</div>
	</div>
</div>