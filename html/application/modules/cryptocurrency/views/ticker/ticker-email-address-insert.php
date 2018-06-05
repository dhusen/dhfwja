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
						<i class="fa fa-envelope"></i>
						Email Address
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<?php
						switch (strtolower($this_method)) {
							case 'insert':
								echo '<i class="fa fa-plus-circle"></i> ' . ucfirst($this_method);
							break;
							case 'delete':
								echo '<i class="fa fa-trash"></i> ' . ucfirst($this_method);
							break;
							case 'edit':
								echo '<i class="fa fa-pencil"></i> ' . ucfirst($this_method);
							break;
							case 'view':
							default:
								echo '<i class="fa fa-table"></i> ' . ucfirst($this_method);
							break;
						}
						?>
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
					<form id="form-insert-email-address-data" action="<?= base_url($base_path . '/ticker/emailaddress/insertaction');?>" method="post">
						<div class="box-body table-responsive no-padding">
							<div class="form-body">
								<div class="form-group required">
									<label for="input-email-name">Email Name</label>
									<input type="text" id="input-email-name" name="email_name" class="form-control required" />
								</div>
								<div class="form-group required">
									<label for="input-email-address">Email Address</label>
									<input type="text" id="input-email-address" name="email_address" class="form-control required" />
								</div>
								<div class="form-group required">
									<label for="input-email-is-enabled">Email is enabled?</label>
									<select id="input-email-is-enabled" class="form-control required" name="email_is_enabled">
										<option value="Y">Yes</option>
										<option value="N">No</option>
									</select>
								</div>
							</div>
						</div>
						<div class="box-footer">
							<div class="form-group">
								<button id="save-this-item" type="submit" class="btn btn-primary">Save Email Address</button>
								<a id="cancel-this-item" href="<?= base_url($base_path . '/ticker/emailaddress/view');?>" class="btn btn-default">Cancel</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		
		<!-- END MAIN CONTENT -->



		
		
		
	</div>
</div>

