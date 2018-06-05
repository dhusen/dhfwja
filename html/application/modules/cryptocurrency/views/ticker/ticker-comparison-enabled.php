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
						<i class="fa fa-sliders"></i> 
						<?= (isset($collect['enabled_data']->cryptocurrency_code) ? $collect['enabled_data']->cryptocurrency_code : '');?>
					</li>
					<li class="btn-group pull-right">
						<a id="add-ticker-comparioson-data" href="<?= base_url($base_path . '/ticker/editenabled/' . (isset($collect['enabled_data']->seq) ? $collect['enabled_data']->seq : '0'));?>" class="btn green pull-right">
							<i class="fa fa-pencil"></i> Edit
						</a>
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<form action="<?= base_url($base_path . '/ticker/data/' . (isset($collect['enabled_data']->seq) ? $collect['enabled_data']->seq : '0'));?>" class="form-horizontal" role="form" id="form-filter-data" method="post">
				<div class="col-md-6 col-sm-12 col-xs-12">
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-filter"></i> 
								Filter
							</div>
						</div>
						<div class="portlet-body">
							<div class="row input-group">
								<div class="col-md-6">
									<div class="input-group">
										<label for="input_date">Date</label>
										<input id="input_date" name="input_date" class="form-control" type="text" value="<?= (isset($tickerdata_date) ? base_safe_text($tickerdata_date, 16) : date('Y-m-d'));?>" />
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
										<label for="input_comparison_limit_min">Limit Min</label>
										<select id="input_comparison_limit_min" name="comparison_limit_min" class="form-control">
											<option value="">-- No Filter --</option>
											<?php
											if (isset($collect['grouped_limit_values'])) {
												if (is_array($collect['grouped_limit_values']) && (count($collect['grouped_limit_values']) > 0)) {
													foreach ($collect['grouped_limit_values'] as $limitval) {
														if ($unit_comparison_every['limit_min'] == $limitval->today_comparison_limit_min) {
															$option_selected_value = ' selected="selected"';
														} else {
															$option_selected_value = '';
														}
														?>
														<option value="<?=$limitval->today_comparison_limit_min;?>"<?=$option_selected_value;?>><?=$limitval->today_comparison_limit_min;?> %</option>
														<?php
													}
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
										<label for="input_comparison_limit_max">Limit Max</label>
										<select id="input_comparison_limit_max" name="comparison_limit_max" class="form-control">
											<option value="">-- No Filter --</option>
											<?php
											if (isset($collect['grouped_limit_values'])) {
												if (is_array($collect['grouped_limit_values']) && (count($collect['grouped_limit_values']) > 0)) {
													foreach ($collect['grouped_limit_values'] as $limitval) {
														if ($unit_comparison_every['limit_max'] == $limitval->today_comparison_limit_max) {
															$option_selected_value = ' selected="selected"';
														} else {
															$option_selected_value = '';
														}
														?>
														<option value="<?=$limitval->today_comparison_limit_max;?>"<?=$option_selected_value;?>><?=$limitval->today_comparison_limit_max;?> %</option>
														<?php
													}
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="row input-group">
								<div class="col-md-12">
									<div class="input-group"> &nbsp; </div>
								</div>
								<div class="col-md-12">
									<div class="input-group">
										<button class="btn blue search-btn" name="logs" type="submit">Show Data</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xs-6 pull-right">
					<div class="input-group">
						<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?= (isset($search_text)? base_safe_text($search_text, 64) : '');?>" />
						<span class="input-group-btn">
							<input  class="btn btn-primary" type="submit" value="Search"/>
						</span>
					</div>	
				</div>
			</form>
		</div>
		
		
		
		
		<div class="row">

			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="box">
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Currency</th>
									<th>Date</th>
									<th>Every</th>
									<th>Time Range</th>
									<th>Real</th>
									<th>Rate</th>
									<th>From <?= (isset($collect['enabled_data']->ticker_data->from_market_name) ? "<a href='" . base_url($base_path . '/cryptocurrency/listcurrency/' . $collect['enabled_data']->ticker_data->from_market_code) . "'>{$collect['enabled_data']->ticker_data->from_market_name}</a>" : '');?></th>
									<th>To <?= (isset($collect['enabled_data']->ticker_data->to_market_name) ? "<a href='" . base_url($base_path . '/cryptocurrency/listcurrency/' . $collect['enabled_data']->ticker_data->to_market_code) . "'>{$collect['enabled_data']->ticker_data->to_market_name}</a>" : '');?></th>
									<th>Result</th>
									<th>Premium</th>
									<th>Limit Min</th>
									<th>Limit Max</th>
									<th>Status</th>
									<!--
									<th class="text-center">Action</th>
									-->
								</tr>
							</thead>
							<tbody>
								<?php
								if (isset($collect['ticker_data_enabled']['data'])) {
									if (is_array($collect['ticker_data_enabled']['data']) && (count($collect['ticker_data_enabled']['data']) > 0)) {
										foreach ($collect['ticker_data_enabled']['data'] as $keval) {
											?>
											<tr>
												<td>
													<?=$keval->cryptocurrency_code;?>
												</td>
												<td><?=$keval->comparison_date;?></td>
												<td>
													<?php
													echo $keval->comparison_every_amount . " " . ucfirst($keval->comparison_every_unit);
													?>
												</td>
												<td>
													<?=$keval->comparison_datetime_starting_time;?> - <?=$keval->comparison_datetime_stopping_time;?>
												</td>
												<td>
													<?php
													echo strtoupper($collect['enabled_data']->ticker_data->cryptocurrency_from_realcurrency);
													?>
												</td>
												<td><?= number_format($keval->today_comparison_currency);?></td>
												<td><?= number_format($keval->exchange_from_last, 2);?></td>
												<td><?= number_format($keval->exchange_to_last, 2);?></td>
												<td><?= $keval->comparison_after_exchange_result;?></td>
												<td><?= $keval->comparison_after_exchange_persen;?></td>
												<td>
													<?= $keval->today_comparison_limit_min;?>
												</td>
												<td>
													<?= $keval->today_comparison_limit_max;?>
												</td>
												<td>
													<?php
													if (($keval->comparison_after_exchange_persen <= $keval->today_comparison_limit_min) || ($keval->comparison_after_exchange_persen >= $keval->today_comparison_limit_max)) {
														?>
														<span class="badge badge-pill badge-success">
															<i class="fa fa-check"></i>
														</span>
														<?php
													} else {
														?>
														<span class="badge badge-pill badge-default">
															<i class="fa fa-clock-o"></i>
														</span>
														<?php
													}
													?>
												</td>
												<!--
												<td class="text-center">
													<?php
													if (strtoupper($keval->cryptocurrency_is_enabled) === 'Y') {
														?>
														<a class="btn btn-sm btn-warning" href="<?= base_url("{$base_path}/ticker/data/{$keval->enabled_seq}");?>">
															<i class="fa fa-envelope"></i>
														</a>
														<a class="btn btn-sm btn-primary" href="<?= base_url("{$base_path}/ticker/data/{$keval->enabled_seq}");?>">
															<i class="fa fa-share"></i>
														</a>
														<a class="btn btn-sm btn-default" href="<?= base_url("{$base_path}/ticker/data/{$keval->enabled_seq}");?>">
															<i class="fa fa-sign-out"></i>
														</a>
														<?php
													} else {
														?>
														<span class="btn btn-sm btn-default">
															<i class="fa fa-times-circle"></i>
														</span>
														<?php
													}
													?>
												</td>
												-->
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
		$('#input_date').datepick(datepickParams);
		//$('#inlineDatepicker').datepick({onSelect: showDate});
	});
	
	$(document).ready(function(){
        $('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = $(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
			var value_of_input_date = $('#input_date').val();
            $("#form-filter-data").attr("action", '<?= base_url($base_path . '/ticker/data/' . $collect['enabled_data']->seq);?>' + "/" + value);
            $("#form-filter-data").submit();
        });
    });
</script>