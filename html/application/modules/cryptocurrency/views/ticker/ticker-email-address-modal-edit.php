<?php
if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }

?>

<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<i class="close fa fa-times" title="" data-dismiss="modal" aria-hidden="true" data-original-title="Close"></i>
		</div>
		<form id="form-edit-email-address-data" action="<?= base_url($base_path . '/ticker/emailaddress/editaction/' . $collect['email_single_data']->seq);?>" method="post">
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12 product-information">
						<div id="quick-shop-container">
							<div class="text-center">
								<h4 id="quick-shop-title" class="alert alert-info">
									Edit Email Address
								</h4>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 box">
						<div class="box-header">
							<h2 class="box-title">
								<?=$collect['email_single_data']->email_address;?>
							</h2>
						</div>
						<div class="box-body form-body">
							<div class="form-group required">
								<label for="input-email-name">Email Name</label>
								<input type="text" id="input-email-name" name="email_name" class="form-control required" value="<?= (isset($collect['email_single_data']->email_name) ? $collect['email_single_data']->email_name : '');?>" />
							</div>
							<div class="form-group required">
								<label for="input-email-address">Email Address</label>
								<input type="text" id="input-email-address" name="email_address" class="form-control required" value="<?= (isset($collect['email_single_data']->email_address) ? $collect['email_single_data']->email_address : '');?>" />
							</div>
							<div class="form-group required">
								<label for="input-email-is-enabled">Email is enabled?</label>
								<select id="input-email-is-enabled" class="form-control required" name="email_is_enabled">
									<?php
									if (strtoupper($collect['email_single_data']->email_is_enabled) === 'Y') {
										?>
										<option value="Y" selected="selected">Yes</option>
										<option value="N">No</option>
										<?php
									} else {
										?>
										<option value="Y">Yes</option>
										<option value="N" selected="selected">No</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<div class="row">
					<div class="col-md-12 product-information">
						<div class="form-group text-center">
							<button id="btn-save-this-item" type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Save</button>
						</div>
					</div>
				</div>
				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">(&times;) Close</button>
			</div>
		</form>
	</div>
</div>