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
						<i class="fa fa-btc"></i>
						<a href="<?= base_url($base_path . '/cryptocurrency');?>">Cryptocurrency</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-university"></i> 
						<a href="<?= base_url($base_path . '/cryptocurrency/listmarket');?>">Market Place</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-pencil"></i> Edit
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-list"></i>
						<?=$collect['market_data']->market_name;?>
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
				<div class="box">
					<form id="form-insert-email-address-data" action="<?= base_url($base_path . '/cryptocurrency/editapiaction/' . $collect['market_data']->seq);?>" method="post">
						<div class="box-body table-responsive no-padding">
							<div class="form-body">
								<div class="form-group required">
									<label for="input-market-api-key">Market API Key</label>
									<select id="input-market-api-key" name="api_key" class="form-control required">
										<?php
										if (is_array($collect['market_data']->market_api_keys) && (count($collect['market_data']->market_api_keys) > 0)) {
											foreach ($collect['market_data']->market_api_keys as $keval) {
												if ($keval->market_api_key == $collect['market_data']->market_price_index) {
													$option_is_selected = ' selected="selected"';
												} else {
													$option_is_selected = '';
												}
												?>
												<option value="<?=$keval->api_code;?>"<?=$option_is_selected;?>><?=$keval->api_code_desc;?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
								
							</div>
						</div>
						<div class="box-footer">
							<div class="form-body">
								<div class="form-group">
									<button id="save-this-item" type="submit" class="btn btn-primary btn-sm">Save</button>
									<a id="cancel-this-item" href="<?= base_url($base_path . '/cryptocurrency/listmarket');?>" class="btn btn-default btn-sm">Cancel</a>
								</div>
							</div>
						</div>
					</form>
				</div>
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
			minDate: new Date(2017, 10 - 1, 01),
			maxDate: '0'
		};
		$('#transaction-date-starting').datepick(datepickParams);
		$('#transaction-date-stopping').datepick(datepickParams);
		//$('#inlineDatepicker').datepick({onSelect: showDate});
	});
</script>