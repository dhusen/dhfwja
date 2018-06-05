<?php
if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }

?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
	<div class="page-content">
		<!-- BEGIN FLASH MESSAGE-->
		<div class="row">
			<div class="col-md-12">
				<?php
				if ($this->session->flashdata('error')) {
					if ($this->session->flashdata('action_message')) {
						?>
						<div class="alert alert-danger alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<?=$this->session->flashdata('action_message');?>
						</div>
						<?php 
					}
				} else {
					if ($this->session->flashdata('action_message')) {
						?>
						<div class="alert alert-success alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<?=$this->session->flashdata('action_message');?>
						</div>
						<?php 
					}
				}
				?>
			</div>
		</div>
		<!-- END FLASH MESSAGE-->
		
		<!-- BEGIN PAGE HEADER-->
		<div class="row">
			<div class="col-md-12">
				<!-- BEGIN PAGE TITLE & BREADCRUMB-->
				<h3 class="page-title">
					<?= (isset($title) ? $title : '');?>	
				</h3>
				<ul class="page-breadcrumb breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="<?= base_url($base_path . '/');?>">Home</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-database"></i>
						<a href="<?= base_url($base_path . '/ticker');?>">Ticker</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-calculator"></i>
						<a href="<?= base_url($base_path . '/ticker/listenabled');?>">Ticker Comparison</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-plus-circle"></i> Add
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-6">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">
							Add New Comparison Ticker
						</h3>
					</div>
					<form id="form-ticker-enabled-add" action="<?= base_url($base_path . '/ticker/addenabled/action');?>" method="post" role="form">
						<div class="box-body table-responsive no-padding">
							<div class="form-body">
								<div class="form-group required">
									<label for="enabled_crypto_code">Comparison Cryptocurrency</label>
									<select class="form-control required" id="enabled_crypto_code" name="enabled_crypto_code">
										<?php
										if (isset($collect['currencies'])) {
											if (is_array($collect['currencies']) && (count($collect['currencies']) > 0)) {
												foreach ($collect['currencies'] as $curval) {
													echo '<option value="' . $curval->currency_code . '">' . $curval->currency_name . '</option>';
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group">
									<input type="checkbox" class="form-control" id="enabled_is_active" name="enabled_is_active" value="Y" />
									Ticker Data with this Cryptocurrency is enabled?
									<label for="enabled_is_active"></label>
								</div>
								<div class="form-group required">
									<label for="enabled_from_realcurrency">Marketplace Real Currency</label>
									<select class="form-control required" id="enabled_from_realcurrency" name="enabled_from_realcurrency">
										<?php
										if (isset($collect['market_real_currencies']['from'])) {
											if (is_array($collect['market_real_currencies']['from']) && (count($collect['market_real_currencies']['from']) > 0)) {
												foreach ($collect['market_real_currencies']['from'] as $realcurrfrom) {
													if (strtoupper($realcurrfrom->currency_code) === strtoupper($collect['enabled_data']->cryptocurrency_from_realcurrency)) {
														?>
														<option value="<?=$realcurrfrom->currency_code;?>" selected="selected"><?= strtoupper($realcurrfrom->currency_code);?></option><?php
													} else {
														?><option value="<?=$realcurrfrom->currency_code;?>"><?= strtoupper($realcurrfrom->currency_code);?></option><?php
													}
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group required">
									<label for="ticker_comparison_from">Comparison From Marketplace</label>
									<select class="form-control required" id="ticker_comparison_from" name="ticker_comparison_from">
										<?php
										if (isset($collect['selected_tickers'])) {
											if (is_array($collect['selected_tickers']) && (count($collect['selected_tickers']) > 0)) {
												foreach ($collect['selected_tickers'] as $alltickerval) {
													?>
													<option value="<?=$alltickerval->seq;?>"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option>
													<?php
												}
											}
										}
										?>
									</select>
									<br/>
									<label for="ticker_comparison_to">Comparison To Marketplace</label>
									<select class="form-control required" id="ticker_comparison_to" name="ticker_comparison_to">
										<?php
										if (isset($collect['selected_tickers'])) {
											if (is_array($collect['selected_tickers']) && (count($collect['selected_tickers']) > 0)) {
												foreach ($collect['selected_tickers'] as $alltickerval) {
													?>
													<option value="<?=$alltickerval->seq;?>"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option>
													<?php
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group required">
									<label for="enabled_unit_name">Ticker Comparison Unit Name</label>
									<select class="form-control required" id="enabled_unit_name" name="enabled_unit_name">
										<?php
										if (isset($collect['units'])) {
											if (is_array($collect['units']) && count($collect['units'])) {
												foreach ($collect['units'] as $unitVal) {
													if ($unitVal['code'] === $collect['enabled_data']->cryptocurrency_compare_unit) {
														?><option value="<?=$unitVal['code'];?>" selected="selected"><?=$unitVal['name'];?></option><?php
													} else {
														?><option value="<?=$unitVal['code'];?>"><?=$unitVal['name'];?></option><?php
													}
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group required">
									<label for="enabled_unit_amount">Ticker Comparison Unit Amount</label>
									<input type="text" class="form-control required" id="enabled_unit_amount"  name="enabled_unit_amount" maxlength="2" placeholder="15" />
								</div>
								<div class="form-group required">
									<div class="row">
										<div class="col-md-6">
											<label for="enabled_comparison_limit_min">Ticker Comparison Premium Limit Min (%)</label>
											<input type="text" class="form-control required" id="enabled_comparison_limit_min"  name="enabled_comparison_limit_min" maxlength="5" placeholder="-5.00" />
										</div>
										<div class="col-md-6">
											<label for="enabled_comparison_limit_max">Ticker Comparison Premium Limit Max (%)</label>
											<input type="text" class="form-control required" id="enabled_comparison_limit_max"  name="enabled_comparison_limit_max" maxlength="5" placeholder="5.00" />
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="box-footer">
							<div class="form-group">
								<button id="save-this-item" type="submit" class="btn btn-primary">Add Ticker Comparison</button>
								<a id="cancel-this-item" href="<?= base_url($base_path . '/ticker/listenabled');?>" class="btn btn-default">Cancel</a>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			
			
		</div>
		
		<!-- END MAIN CONTENT -->



		
		
		
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('#enabled_crypto_code').change(function() {
			var enabled_crypto_code = $(this).val();
			$('#ticker_comparison_from').hide();
			$('#ticker_comparison_to').hide();
			if (enabled_crypto_code.length > 0) {
				var objData = {
					'enabled_crypto_code': enabled_crypto_code
				};
				$.ajax({
					url: '<?= base_url($base_path . '/ticker/addenabled/ajaxrequest');?>',
					type: 'POST',
					cache: false,
					data : objData,
					contentType: "application/x-www-form-urlencoded",
					//dataType: "json",
					success : function(response) {
						if (response != null) {
							/*
							console.log(response);
							loading_placeholder.hide();
							if (stories_publish_status == 'YES') {
								placeholder.html('<span class="btn btn-success btn-responsive"><i class="fa fa-check"></i></span>');
							} else {
								placeholder.html('<span class="btn btn-info btn-responsive"><i class="fa fa-clock-o"></i></span>');
							}
							*/
							$('#ticker_comparison_from').html(response);
							$('#ticker_comparison_to').html(response);
							$('#ticker_comparison_from').show();
							$('#ticker_comparison_to').show();
						} else {
							alert('Hide');
						}
					}
				});
			}
		});
	});
</script>








