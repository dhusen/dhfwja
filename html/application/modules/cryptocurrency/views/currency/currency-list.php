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
						<i class="fa fa-money"></i> 
						<a href="<?= base_url($base_path . '/cryptocurrency/listcurrency');?>">Currencies</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-university"></i>
						<span><?=$market_data->market_name;?></span>
					</li>
					<li class="btn-group pull-right">
						<a id="add-marketplace-data" href="<?= base_url($base_path . '/cryptocurrency/currency/add');?>" class="btn green pull-right">
							<i class="fa fa-plus-circle"></i> Add
						</a>
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<div class="col-md-4 col-sm-12 col-xs-12">
				<div class="portlet box blue">
					<div class="portlet-title">
						<div class="caption">
							<i class="fa fa-university"></i> <span>Marketplace</span>
						</div>
					</div>
					<div class="portlet-body form form-group">
						<ul class="menu nav list-unstyled">
							<?php
							if (isset($collect['marketplace'])) {
								if (is_array($collect['marketplace']) && count($collect['marketplace'])) {
									foreach ($collect['marketplace'] as $marketVal) {
										if ($marketVal->market_code == $market_data->market_code) {
											$li_href_active = 'active btn-primary';
										} else {
											$li_href_active = '';
										}
										?>
										<li class="<?=$li_href_active;?>">
											<?php
											if (strtoupper($marketVal->market_is_enabled) === 'Y') {
												?>
												<a href="<?= base_url($base_path . '/cryptocurrency/listcurrency/' . $marketVal->market_code);?>">
													<i class="fa fa-university"></i>
													<span><?=$marketVal->market_name;?></span>
												</a>
												<?php
											} else {
												?>
												<a href="#">
													<i class="fa fa-university"></i>
													<span><?=$marketVal->market_name;?></span>
												</a>
												<?php
											}
											?>
										</li>
										<?php
									}
								}
							}
							?>
							<!--
							<li class="row">
								<div class="col-md-12">
									<form action="<?= base_url($base_path . '/mutasi/listaccount/');?>" role="form" id="date-range-form" method="post">
										<div class="input-group">
											<div class="row">
												<div class="col-md-6">
													<label for="transaction-date-starting">Date Start</label>
													<input id="transaction-date-starting" name="transaction_date[starting]" class="form-control" type="text" value="<?= (isset($transaction_date['starting'])? base_safe_text($transaction_date['starting'], 16) : '');?>" />
												</div>
												<div class="col-md-6">
													<label for="transaction-date-stopping">Date End</label>
													<input id="transaction-date-stopping" name="transaction_date[stopping]" class="form-control" type="text" value="<?= (isset($transaction_date['stopping'])? base_safe_text($transaction_date['stopping'], 16) : '');?>" />
												</div>
											</div>
											<div class="row">
												<div class="col-md-12 pull-right">
													<span class="input-group-btn">
														<input class="btn btn-sm btn-primary" type="submit" value="Submit" id="submit-this-transaction-date" />
													</span>
												</div>
											</div>
										</div>
									</form>
								</div>
							</li>
							-->
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xs-6 pull-right">
				<form action="<?= base_url($base_path . '/cryptocurrency/listcurrency/' . (isset($market_data->market_code) ? $market_data->market_code : ''));?>" class="form-horizontal" role="form" id="searh-form" method="post">
					<div class="input-group">
						<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?= (isset($search_text)? base_safe_text($search_text, 64) : '');?>" />
						<span class="input-group-btn">
							<input  class="btn btn-primary" type="submit" value="Search"/>
						</span>
					</div>	
				</form>
			</div>
		</div>
		
		
		
		
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="box">
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Name</th>
									<th>Marketplace</th>
									<th>Currency Name</th>
									<th>Market Decimal</th>
									<th>Display Decimal</th>
									<th>Status</th>
									<th class="text-center">Ticker Data</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (isset($collect['currencies']['data'])) {
									if (is_array($collect['currencies']['data']) && (count($collect['currencies']['data']) > 0)) {
										foreach ($collect['currencies']['data'] as $keval) {
											?>
											<tr>
												<td>
													<?=$keval->currency_name;?>
												</td>
												<td>
													<a href="<?=$keval->market_address;?>"><?=$keval->market_name;?></a>
												</td>
												<td>
													<?=$keval->currency_market_name;?>
												</td>
												<td>
													<?=$keval->currency_market_decimals;?>
												</td>
												<td>
													<?=$keval->currency_market_decimals_display;?>
												</td>
												<td>
													<?php
													if (strtoupper($keval->currency_is_enabled) === 'Y') {
														echo '<button class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i> <span>Enabled</span></button>';
													} else {
														echo '<button class="btn btn-sm btn-danger"><i class="fa fa-ban"></i> <span>Disabled</span></button>';
													}
													?>
												</td>
												<td class="text-center">
													<?php
													if (strtoupper($keval->currency_is_enabled) === 'Y') {
														?>
														<a class="btn btn-sm btn-primary" href="<?= base_url("{$base_path}/ticker/listenabled");?>">
															<i class="fa fa-eye"></i> <span>View Data</span>
														</a>
														
														<?php
													} else {
														?>
														<span class="btn btn-sm btn-default">
															<i class="fa fa-times-circle"></i> <span>Not available</span>
														</span>
														<?php
													}
													?>
												</td>
											</tr>		
											<?php
										}
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<?php
				echo $collect['pagination'];
				?>
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
	
	$(document).ready(function(){
        $('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = $(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
            $("#searh-form").attr("action", '<?= base_url($base_path . '/cryptocurrency/listcurrency/' . (isset($market_data->market_code) ? $market_data->market_code : ''));?>' + "/" + value);
            $("#searh-form").submit();
        });
    });
</script>