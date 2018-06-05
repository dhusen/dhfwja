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
					<li class="btn-group pull-right">
						<a href="<?= base_url($base_path . '/ticker/emailaddress/insert');?>" class="btn green pull-right">
							<i class="fa fa-plus"></i> Add
						</a>
					</li>
				</ul>
				<!-- END PAGE TITLE & BREADCRUMB-->
			</div>
		</div>
		<!-- END PAGE HEADER-->
		
		<!-- START MAIN CONTENT -->
		<div class="row">
			<!-- BEGIN FORM-->
			<form action="<?= base_url($base_path . '/ticker/emailaddress/view');?>" role="form" id="email-address-form" method="post">
				<div class="col-md-8 hidden-sm hidden-xs">
					&nbsp;
				</div>
				<div class="col-md-4 col-xs-12 pull-right">
					<div class="input-group">
						<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?= (isset($search_text)? base_safe_text($search_text, 64) : '');?>" />
						<span class="input-group-btn">
							<input class="btn btn-primary" type="submit" value="Search" />
						</span>
					</div>	
				</div>
			</form>
			<!-- END FORM-->
		</div>
				
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="box">
					<div class="box-body table-responsive table-border">
						<table class="table table-hover">
							<thead>
								<tr>
									<th class="alert alert-sm alert-info"><i class="fa fa-rss"></i> Total</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<?php
									if (isset($collect['email_data']['count']->value)) {
										?>
										<td>
											<?= number_format($collect['email_data']['count']->value);?>
										</td>
										<?php
									}
									?>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="box-body table-responsive no-padding">
						<?php
						if (isset($collect['email_data']['data'])) {
							if(is_array($collect['email_data']['data'])) {
								if (count($collect['email_data']['data']) > 0) {
									?>
									<table class="table table-hover">
										<thead>
											<tr>
												<th>No.</th>
												<th>Email Name</th>
												<th>Email Address</th>
												<th>Status</th>
												<th class="text-center">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$for_i = 1;
											foreach($collect['email_data']['data'] as $keval) {
												?>
												<tr>
													<td><?=$for_i;?></td>
													<td><?=$keval->email_name;?></td>
													<td><?=$keval->email_address;?></td>
													<td>
														<?php
														if (strtoupper($keval->email_is_enabled) === 'Y') {
															echo '<span class="btn btn-sm btn-success"><i class="fa fa-check"></i> Enabed</span>';
														} else {
															echo '<span class="btn btn-sm btn-default"><i class="fa fa-ban"></i> Disabled</span>';
														}
														?>
													</td>
													<td class="text-center">
														<a class="btn btn-sm btn-warning btn-modal-view-item" href="<?php echo base_url("{$base_path}/ticker/emailaddress/edit/{$keval->seq}"); ?>">
															<i class="fa fa-pencil"></i>
														</a>
														<a class="btn btn-sm btn-danger btn-modal-view-item" href="<?php echo base_url("{$base_path}/ticker/emailaddress/delete/{$keval->seq}"); ?>">
															<i class="fa fa-trash"></i>
														</a>											
													</td>
												</tr>
												<?php
												$for_i += 1;
											}
											?>
										</tbody>
									</table>
									<?php
								} else {
									?>
									<div class="alert alert-danger alert-dismissable">
										<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
										There is no email address data
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</div>


			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?=$collect['pagination'];?>
			</div>
		</div>
		
		<!-- END MAIN CONTENT -->



		
		
		<div class="modal fade" id="quick-shop-modal"></div>
	</div>
</div>




<script src="<?= base_url('assets/plugins/datepick/jquery.plugin.js');?>" type="text/javascript"></script>
<link href="<?= base_url('assets/plugins/datepick/jquery.datepick.css');?>" rel="stylesheet" />
<script src="<?= base_url('assets/plugins/datepick/jquery.datepick.js');?>" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function(){
        $('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = $(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
			$("#email-address-form").attr("action", '<?= base_url($base_path . '/ticker/emailaddress/' . $this_method);?>'  + "/" + value);
            $("#email-address-form").submit();
        });
		
		// Modal
		$('.btn-modal-view-item').click(function(el) {
			el.preventDefault();
			var selected_index = $(this).get(0).href;
			//var modal_view_item = selected_index;
			$('#quick-shop-modal').load(selected_index, function() {
				$(this).modal('show');
			});
		});
    });
</script>






