<?php
if (!defined('BASEPATH')) { exit('Script cannot access directly.'); }
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
							<a href="<?= base_url($base_path . '/index.php/');?>">Home</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-book"></i>
							<a href="<?= base_url($base_path . '/index.php/addressbook');?>">Address Book</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-bookmark"></i> Lists
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-6">
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-info-circle"></i> User Roles
							</div>
							<div class="tools">
								<a class="expand" href="javascript:;"></a>
							</div>
						</div>
						<div class="portlet-body form display-hide">
							<div class="form-body">
								<div class="form-group">
									<?php
									if (isset($collect['roles'])) {
										if (is_array($collect['roles']) && count($collect['roles'])) {
											foreach ($collect['roles'] as $val) {
												?>
												<span class="form-control"><i class="fa fa-check"></i> <?=$val->role_name;?></span>
												<?php
											}
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- BEGIN FORM-->
				<div class="col-md-6 pull-right">
					<form action="<?= base_url($base_path . '/index.php/addressbook/lists');?>" class="form-horizontal" role="form" id="searh-form" method="post">
						<div class="input-group">
							<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?=$search_text;?>" />
							<span class="input-group-btn">
								<input  class="btn btn-primary" type="submit" value="Search"/>
							</span>
						</div>
					</form>	
				</div>
				<!-- END FORM-->
			</div>
			<!-- START MAIN CONTENT -->
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="box box-primary">
						<div class="box-header">
							<h3 class="box-title">
								Address Book Groups
							</h3>
						</div>
						<div class="box-body table-responsive no-padding">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>No.</th>
										<th>Group Name</th>
										<th>Group Parent</th>
										<th>Group Items</th>
										<th>Added</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (isset($collect['addressbook']['groups']['data'])) {
										if (count($collect['addressbook']['groups']['data']) > 0) {
											$for_i = 1;
											foreach ($collect['addressbook']['groups']['data'] as $keval) {
												?>
												<tr>
													<td><?=$for_i;?></td>
													<td>
														<?php
														echo "<a href='" . base_url($base_path . '/index.php/addressbook/group/' . $keval->group_name_url) . "'>{$keval->group_name_text}</a>";
														?>
													</td>
													<td>
														<?php
														if (intval($keval->group_parent_seq) > 0) {
															echo "<a href='" . base_url($base_path . '/index.php/addressbook/group/' . $keval->parent_name_url) . "'>{$keval->parent_name_text}</a>";
														} else {
															echo "-";
														}
														?>
													</td>
													<td><?=$keval->group_items;?></td>
													<td>
														<?=$keval->group_add_datetime;?>
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
						<div class="box-header">
							<h3 class="box-title">
								Address Book Items
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
							
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="box box-primary">
						<div class="box-header">
							<h3 class="box-title">
								Address Book Parent Group
							</h3>
						</div>
						<div class="box-body no-padding">
							<?php
							if (isset($collect['addressbook']['group']['parent'])) {
								if (count($collect['addressbook']['group']['parent']) > 0) {
									$i = 0;
									foreach ($collect['addressbook']['group']['parent'] as $keval) {
										if ($i % 2) {
											?>
											</div>
											<div class="box-body no-padding">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="dashboard-stat green">
														<div class="visual"></div>
														<div class="details">
															<div class="number"><?= number_format($keval->group_childs, 0);?></div>
															<div class="desc">
																<?= substr($keval->group_name_text, 0, 32);?>
															</div>
														</div>
														<a href="<?= base_url($base_path . '/index.php/addressbook/group/' . $keval->group_name_url);?>" class="more">
															<?=$keval->group_items;?> Items <i class="m-icon-swapright m-icon-white"></i>
														</a>		
													</div>
												</div>
											<?php
										} else {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<div class="dashboard-stat blue">
													<div class="visual"></div>
													<div class="details">
														<div class="number"><?= number_format($keval->group_childs, 0);?></div>
														<div class="desc">
															<?= substr($keval->group_name_text, 0, 32);?>
														</div>
													</div>
													<a href="<?= base_url($base_path . '/index.php/addressbook/group/' . $keval->group_name_url);?>" class="more">
														<?=$keval->group_items;?> Items <i class="m-icon-swapright m-icon-white"></i>
													</a>		
												</div>
											</div>
											<?php
										}
										$i++;
									}
								}
							}
							?>
						</div>

						
					</div>
				</div>
			</div>
			<!-- END MAIN CONTENT -->



			
			
			
		</div>
	</div>
	
	
	