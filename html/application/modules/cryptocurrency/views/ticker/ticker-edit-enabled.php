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
						<i class="fa fa-pencil"></i> Edit
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-calculator"></i> <?= (isset($collect['enabled_data']->cryptocurrency_code) ? $collect['enabled_data']->cryptocurrency_code : '');?>
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">
							<?= (isset($collect['enabled_data']->cryptocurrency_code) ? strtoupper($collect['enabled_data']->cryptocurrency_code) : '');?>
						</h3>
					</div>
					<?php
					if (isset($collect['enabled_data'])) {
						?>
						<form id="exchange-edit" action="<?php echo base_url($base_path . '/ticker/editenabledaction/' . (isset($collect['enabled_data']->seq) ? $collect['enabled_data']->seq : 0)) ?>" method="post" role="form">
							<div class="box-body no-padding">
								<div class="form-body">
									<div class="form-group">
										<?php
										if (strtoupper($collect['enabled_data']->cryptocurrency_is_enabled) === 'Y') {
											?><input type="checkbox" class="form-control" id="enabled_is_active" name="enabled_is_active" value="Y" checked="checked" />
											<?php } else { ?>
											<input type="checkbox" class="form-control" id="enabled_is_active" name="enabled_is_active" value="Y" />
											<?php } 
										?>
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
														if ($alltickerval->seq === $collect['enabled_data']->cryptocurrency_compare_ticker_seq_from) {
															?><option value="<?=$alltickerval->seq;?>" selected="selected"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option><?php
														} else {
															?><option value="<?=$alltickerval->seq;?>"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option><?php
														}
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
														if ($alltickerval->seq === $collect['enabled_data']->cryptocurrency_compare_ticker_seq_to) {
															?><option value="<?=$alltickerval->seq;?>" selected="selected"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option><?php
														} else {
															?><option value="<?=$alltickerval->seq;?>"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option><?php
														}
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
										<input type="text" class="form-control required" id="enabled_unit_amount"  name="enabled_unit_amount" maxlength="2" placeholder="15" value="<?= (isset($collect['enabled_data']->cryptocurrency_compare_amount) ? sprintf("%d", $collect['enabled_data']->cryptocurrency_compare_amount) : '');?>" />
									</div>
									<div class="form-group required">
										<div class="row">
											<div class="col-md-6">
												<label for="enabled_comparison_limit_min">Ticker Comparison Premium Limit Minimum (%)</label>
												<input type="text" class="form-control required" id="enabled_comparison_limit_min"  name="enabled_comparison_limit_min" maxlength="5" placeholder="5.00" value="<?= (isset($collect['enabled_data']->cryptocurrency_premium_limit_min) ? sprintf("%.02f", $collect['enabled_data']->cryptocurrency_premium_limit_min) : '');?>" />
											</div>
											<div class="col-md-6">
												<label for="enabled_comparison_limit_max">Ticker Comparison Premium Limit Maximum (%)</label>
												<input type="text" class="form-control required" id="enabled_comparison_limit_max"  name="enabled_comparison_limit_max" maxlength="5" placeholder="5.00" value="<?= (isset($collect['enabled_data']->cryptocurrency_premium_limit_max) ? sprintf("%.02f", $collect['enabled_data']->cryptocurrency_premium_limit_max) : '');?>" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="box-footer">
								<div class="form-group">
									<button id="save-this-item" type="submit" class="btn btn-primary">Edit Ticker Comparison</button>
									<a id="cancel-this-item" href="<?= base_url($base_path . '/ticker/listenabled');?>" class="btn btn-default">Cancel</a>
								</div>
							</div>
						</form>
						<?php
					}
					?>
				</div>
				
				
				
			</div>
			
			
			
			
			
			
			<div id="menu-item-list-data" class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				
			</div>
		</div>
		
		<!-- END MAIN CONTENT -->



		
		
		
	</div>
</div>

<script src="<?= base_url('assets/plugins/datepick/jquery.plugin.js');?>" type="text/javascript"></script>
<link href="<?= base_url('assets/plugins/datepick/jquery.datepick.css');?>" rel="stylesheet" />
<script src="<?= base_url('assets/plugins/datepick/jquery.datepick.js');?>" type="text/javascript"></script>
<script type="text/javascript">
	$(function() {
		var datepickParams = {
			showSpeed: 'fast',
			dateFormat: 'yyyy-mm-dd',
			minDate: new Date()
		};
		$('#exchange_date').datepick(datepickParams);
		//$('#transaction-date-stopping').datepick(datepickParams);
		//$('#inlineDatepicker').datepick({onSelect: showDate});
	});
	

</script>