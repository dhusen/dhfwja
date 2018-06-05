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
						<i class="fa fa-usd"></i> Real Currencies
					</li>
					<li class="btn-group pull-right">
						<a id="add-real-currency-data" href="<?= base_url($base_path . '/exchange/addcurrency');?>" class="btn green pull-right">
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
				
			</div>
			<div class="col-md-6 col-xs-6 pull-right">
				<form action="<?= base_url($base_path . '/exchange/listcurrency');?>" class="form-horizontal" role="form" id="searh-form" method="post">
					<div class="input-group">
						<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?= (isset($search_text) ? base_safe_text($search_text, 64) : '');?>" />
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
					<?php
					if (isset($collect['currencies']['data'])) {
						if (is_array($collect['currencies']['data']) && (count($collect['currencies']['data']) > 0)) {
							?>
							<div class="box-body table-responsive no-padding">
								<table class="table table-hover">
									<thead>
										<tr>
											<th>Name</th>
											<th>Code</th>
											<th>Country</th>
											<th>Status</th>
											<th>Insert</th>
											<th class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($collect['currencies']['data'] as $keval) {
											?>
											<tr>
												<td>
													<?=$keval->currency_name;?>
												</td>
												<td>
													<?=$keval->currency_code;?>
												</td>
												<td><?=$keval->currency_country_name;?></td>
												<td>
													<?php
													if (strtoupper($keval->currency_is_active) === 'Y') {
														echo '<button class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i> <span>Enabled</span></button>';
													} else {
														echo '<button class="btn btn-sm btn-danger"><i class="fa fa-ban"></i> <span>Disabled</span></button>';
													}
													?>
												</td>
												<td>
													<?=$keval->currency_datetime_insert;?>
												</td>
												<td class="text-center">
													<a class="btn btn-sm btn-warning" href="<?= base_url("{$base_path}/exchange/editcurrency/{$keval->seq}");?>">
														<i class="fa fa-pencil"></i>
													</a>
												</td>
											</tr>		
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<?php
						} else {
							?>
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
								There is no data on this date selected
							</div>
							<?php
						}
					}
					?>
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
			dateFormat: 'yyyy-mm-dd'
		};
		$('#exchange-date').datepick(datepickParams);
		//$('#transaction-date-stopping').datepick(datepickParams);
		//$('#inlineDatepicker').datepick({onSelect: showDate});
	});
	
	$(document).ready(function(){
        $('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = $(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
            $("#searh-form").attr("action", '<?= base_url($base_path . '/exchange/listexchange/' . (isset($exchange) ? base_safe_text($exchange, 32) : ''));?>' + "/" + value);
            $("#searh-form").submit();
        });
    });
</script>