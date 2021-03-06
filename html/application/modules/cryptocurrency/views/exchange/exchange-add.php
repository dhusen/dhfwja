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
						<i class="fa fa-exchange"></i>
						<a href="<?= base_url($base_path . '/exchange');?>">Exchange</a>
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
			<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">
							Add New Exchange Currency
						</h3>
					</div>
					<form id="exchange-add" action="<?php echo base_url($base_path . '/exchange/addexchangeaction') ?>" method="post" role="form">
						<div class="box-body no-padding">
							<div class="form-body">
								<div class="form-group required">
									<label for="exchange_date"><i class="fa fa-calendar"></i> Date</label>
									<input type="text" class="form-control required" id="exchange_date"  name="exchange_date" maxlength="12" placeholder="<?= date('Y-m-d');?>" value="<?= (isset($input_params['exchange_date']) ? base_safe_text($input_params['exchange_date'], 12) : '');?>" />
								</div>
								<div class="form-group">
									<input type="checkbox" class="form-control" id="exchange_is_active" name="exchange_is_active" value="Y" /> Exchange Currency is Active?
									<label for="exchange_is_active"></label>
								</div>
								<div class="form-group required">
									<label for="exchange_from">Currency From</label>
									<select class="form-control required" id="exchange_from" name="exchange_from">
										<?php
										if (isset($collect['currencies'])) {
											if (is_array($collect['currencies']) && count($collect['currencies'])) {
												foreach ($collect['currencies'] as $fromVal) {
													if (strtoupper($fromVal->currency_code) === 'USD') {
														?><option value="<?=$fromVal->seq;?>" selected="selected"><?=$fromVal->currency_code;?></option><?php
													} else {
														?><option value="<?=$fromVal->seq;?>"><?=$fromVal->currency_code;?></option><?php
													}
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group required">
									<label for="exchange_to">Currency To</label>
									<select class="form-control required" id="exchange_to" name="exchange_to">
										<?php
										if (isset($collect['currencies'])) {
											if (is_array($collect['currencies']) && count($collect['currencies'])) {
												foreach ($collect['currencies'] as $toVal) {
													if (strtoupper($toVal->currency_code) === 'IDR') {
														?><option value="<?=$toVal->seq;?>" selected="selected"><?=$toVal->currency_code;?></option><?php
													} else {
														?><option value="<?=$toVal->seq;?>"><?=$toVal->currency_code;?></option><?php
													}
												}
											}
										}
										?>
									</select>
								</div>
								<div class="form-group required">
									<label for="exchange_amount">Exchange Amount</label>
									<input type="text" class="form-control required" id="exchange_amount"  name="exchange_amount" maxlength="12" placeholder="13500" value="<?= (isset($input_params['exchange_amount']) ? base_safe_text($input_params['exchange_amount'], 16) : '');?>" />
								</div>
							</div>
						</div>
					
						<div class="box-footer">
							<div class="form-group">
								<button id="save-this-item" type="submit" class="btn btn-primary">Save Exchange</button>
								<button id="cancel-this-item" type="button" class="btn btn-default">Cancel</button>
							</div>
						</div>
					</form>
				</div>
				
				
				
			</div>
			
			
			
			
			
			
			<div id="menu-item-list-data" class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
				
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