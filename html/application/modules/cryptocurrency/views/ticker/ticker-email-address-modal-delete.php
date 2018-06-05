<?php
if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }

?>

<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<i class="close fa fa-times" title="" data-dismiss="modal" aria-hidden="true" data-original-title="Close"></i>
		</div>
		<form id="form-delete-email-address-data" action="<?= base_url($base_path . '/ticker/emailaddress/deleteaction/' . $collect['email_single_data']->seq);?>" method="post">
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12 product-information">
						<div id="quick-shop-container">
							<div class="text-center">
								<h4 id="quick-shop-title" class="alert alert-info">
									Delete Email Address
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
								<label for="input-email-address">Please insert email address</label>
								<input type="text" id="input-email-address" name="email_address" class="form-control required" />
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<div class="row">
					<div class="col-md-12 product-information">
						<div class="form-group text-center">
							<button id="btn-delete-this-item" type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>
						</div>
					</div>
				</div>
				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">(&times;) Close</button>
			</div>
		</form>
	</div>
</div>