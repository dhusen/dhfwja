<?php
if (!defined('PHP_MYSQL_CRUD_NATIVE')) { exit('Script cannot access directly.'); }
?>

	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN FLASH MESSAGE-->
			<div class="row">
				<div class="col-md-12">
					<?php
					if (isset($_SESSION['error']) && isset($_SESSION['action_message'])) {
						if ($_SESSION['error'] > 0) {
							$alert_div_dismissable = 'alert alert-danger alert-dismissable';
						} else {
							$alert_div_dismissable = 'alert alert-success alert-dismissable';
						}
						?>
						<div class='<?=$alert_div_dismissable;?>'>
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<?=$_SESSION['action_message'];?>
						</div>
						<?php
						unset($_SESSION['error']);
						unset($_SESSION['action_message']);
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
						<li class="btn-group">
							<a href="<?= base_url($base_path . '/index.php/addressbook/additem');?>" class="btn green pull-right">
								Add Item<i class="fa fa-plus"></i>
							</a>
						</li>
						<li>
							<i class="fa fa-home"></i>
							<a href="<?= base_url($base_path . '/index.php/');?>">Home</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-book"></i>
							<a href="<?= base_url($base_path . '/index.php/addressbook');?>">Address Book</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<?php
						if (isset($collect['addressbook']['group_data'][0])) {
							if ((int)$collect['addressbook']['group_data'][0]->group_parent_seq > 0) {
								?>
								<li>
									<i class="fa fa-list-alt"></i>
									<a href="<?= base_url($base_path . "/index.php/addressbook/group/{$collect['addressbook']['group_data'][0]->parent_name_url}");?>">
										<?=$collect['addressbook']['group_data'][0]->parent_name_text;?>
									</a>
									<i class="fa fa-angle-right"></i>
								</li>
								<?php
							} else {
								?>
								<li>
									<i class="fa fa-list-alt"></i>
									<a href="<?= base_url($base_path . "/index.php/addressbook/listgroup");?>">List</a>
									<i class="fa fa-angle-right"></i>
								</li>
								<?php
							}
							?>
							<li>
								<i class="fa fa-bookmark"></i>
								<?=$collect['addressbook']['group_data'][0]->group_name_text;?>
							</li>
							<?php
						}
						?>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			
			<!-- START MAIN CONTENT -->
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="box box-primary">
						<div class="box-header">
							<h3 class="box-title">
								Items of <?=$collect['addressbook']['group_data'][0]->group_name_text;?>
							</h3>
						</div>
						<div class="box-body table-responsive no-padding">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>No.</th>
										<th>Item Name</th>
										<th>Item Group</th>
										<th>Added</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (isset($collect['addressbook']['items']['data'])) {
										if (count($collect['addressbook']['items']['data']) > 0) {
											$for_i = 1;
											foreach ($collect['addressbook']['items']['data'] as $keval) {
												?>
												<tr>
													<td><?=$for_i;?></td>
													<td>
														<?=$keval->item_name_text;?>
													</td>
													<td>
														<?php
														if (strlen($keval->item_group_name) === 0) {
															echo "-";
														} else {
															echo "<a href='" . base_url($base_path . '/index.php/addressbook/group/' . $keval->item_group_url) . "'>{$keval->item_group_name}</a>";
														}
														?>
													</td>
													<td>
														<?=$keval->item_add_datetime;?>
													</td>
												</tr>
												<?php
												$for_i += 1;
											}
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="box-footer no-padding">
							<?=$collect['pagination'];?>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="box box-primary">
						<div class="box-header">
							<h3 class="box-title">
								-
							</h3>
						</div>
						
					</div>
				</div>
			</div>
			<!-- END MAIN CONTENT -->



			
			
			
		</div>
	</div>
	
	
	