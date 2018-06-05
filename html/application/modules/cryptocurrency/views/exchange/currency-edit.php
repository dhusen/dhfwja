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
						<i class="fa fa-list"></i>
						<a href="<?= base_url($base_path . '/exchange');?>">Exchange</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-pencil"></i> Edit Real Currency
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<i class="fa fa-money"></i> <?= (isset($collect['currency_data']->currency_code) ? $collect['currency_data']->currency_code : '');?>
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
					<form action="<?= base_url($base_path . '/exchange/editcurrencyaction');?>" role="form" method="post">
						<div class="box-body table-responsive no-padding">
							<div class="form-body">
								<div class="form-group required">
									<label for="currency_code">Currency Code</label>
									<input type="text" class="form-control required" id="currency_code"  name="currency_code" maxlength="3" value="<?= (isset($collect['currency_data']->currency_code) ? $collect['currency_data']->currency_code : '');?>" />
								</div>
								<div class="form-group">
									<?php
									if ($collect['currency_data']->currency_is_active === 'Y') {
										?><input type="checkbox" class="form-control" id="currency_is_active" name="currency_is_active" value="Y" checked="checked" /> Enable Real Currency?<?php
									} else {
										?><input type="checkbox" class="form-control" id="currency_is_active" name="currency_is_active" value="Y" /> Enable Real Currency?<?php
									}
									?>
									<label for="currency_is_active"></label>
								</div>
								<div class="form-group required">
									<label for="currency_country_code">Country Name</label>
									<select class="form-control required" id="currency_country_code" name="currency_country_code">
										<option value=""> -- Select Country --</option>
										<?php
										if (isset($collect['countries'])) {
											if (is_array($collect['countries']) && count($collect['countries'])) {
												foreach ($collect['countries'] as $val) {
													if (($val->currency_code == $collect['currency_data']->currency_code) && ($val->code == $collect['currency_data']->currency_country_code)) {
														?>
														<option value="<?=$val->code;?>" selected="selected"><?=$val->name;?></option>
														<?php
													} else {
														?>
														<option value="<?=$val->code;?>"><?=$val->name;?></option>
														<?php
													}
												}
											}
										}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="box-footer">
							<div class="form-group">
								<button id="save-this-item" type="submit" class="btn btn-primary">Save Currency</button>
								<a id="cancel-this-item" href="<?= base_url($base_path . '/exchange/listcurrency');?>" class="btn btn-default">Cancel</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<?php
				// echo $collect['pagination'];
				?>
			</div>
		</div>
		
		<!-- END MAIN CONTENT -->



		
		
		
	</div>
</div>




