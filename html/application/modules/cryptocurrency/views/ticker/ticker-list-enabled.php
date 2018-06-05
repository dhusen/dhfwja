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
						<i class="fa fa-calculator"></i> Ticker Comparison
					</li>
					<li class="btn-group pull-right">
						<a id="add-ticker-comparioson-data" href="<?= base_url($base_path . '/ticker/addenabled');?>" class="btn green pull-right">
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
			<div class="col-md-6 col-sm-12 col-xs-12">

				
			</div>
			<div class="col-md-6 col-xs-6 pull-right">
				<form action="<?= base_url($base_path . '/ticker/listenabled');?>" class="form-horizontal" role="form" id="searh-form" method="post">
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
									<th>Crypto Currency</th>
									<th>Every</th>
									<th>From Market</th>
									<th>To Market</th>
									<th>Premium Limit Min</th>
									<th>Premium Limit Max</th>
									<th>Status</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (isset($collect['ticker_comparison']['data'])) {
									if (is_array($collect['ticker_comparison']['data']) && (count($collect['ticker_comparison']['data']) > 0)) {
										foreach ($collect['ticker_comparison']['data'] as $keval) {
											?>
											<tr>
												<td>
													<?=$keval->cryptocurrency_code;?>
												</td>
												<td>
													<?php
													echo $keval->cryptocurrency_compare_amount . " " . ucfirst($keval->cryptocurrency_compare_unit);
													?>
												</td>
												<td>
													<a href="<?= base_url('cryptocurrency/listcurrency/' . $keval->from_market_code);?>">
														<?=$keval->from_market_name;?>
													</a> [<?=$keval->cryptocurrency_from_realcurrency;?>]
												</td>
												<td>
													<a href="<?= base_url('cryptocurrency/listcurrency/' . $keval->to_market_code);?>">
														<?=$keval->to_market_name;?>
													</a>
												</td>
												<td>
													<?= sprintf("%.02f", $keval->cryptocurrency_premium_limit_min);?>
												</td>
												<td>
													<?= sprintf("%.02f", $keval->cryptocurrency_premium_limit_max);?>
												</td>
												<td>
													<?php
													if (strtoupper($keval->cryptocurrency_is_enabled) === 'Y') {
														echo '<span class="btn-sm"><i class="fa fa-check-circle"></i> Enabled</span>';
													} else {
														echo '<span class="btn-sm"><i class="fa fa-ban"></i> Disabled</span>';
													}
													?>
												</td>
												<td class="text-center">
													<?php
													if (strtoupper($keval->cryptocurrency_is_enabled) === 'Y') {
														?>
														<a class="btn btn-sm btn-primary" href="<?= base_url("{$base_path}/ticker/data/{$keval->seq}");?>">
															<i class="fa fa-eye"></i>
														</a>
														<a class="btn btn-sm btn-warning" href="<?= base_url("{$base_path}/ticker/editenabled/{$keval->seq}");?>">
															<i class="fa fa-pencil"></i>
														</a>
														<?php
													} else {
														?>
														<span class="btn btn-sm btn-default">
															<i class="fa fa-times-circle"></i>
														</span>
														<a class="btn btn-sm btn-warning" href="<?= base_url("{$base_path}/ticker/editenabled/{$keval->seq}");?>">
															<i class="fa fa-pencil"></i>
														</a>
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
            $("#searh-form").attr("action", '<?= base_url($base_path . '/ticker/listenabled');?>' + "/" + value);
            $("#searh-form").submit();
        });
    });
</script>