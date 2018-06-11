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
							<a href="<?= base_url($base_path . '/');?>">Home</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-th-large"></i>
							<a href="<?= base_url($base_path . '/banner');?>">Banner</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-sliders"></i> Slider
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-bars"></i>
							<?= (isset($collect['banner_location_data']->location) ? $collect['banner_location_data']->location : '');?>
						</li>
						<li class="btn-group pull-right">
							<a href="<?= base_url($base_path . '/banner/add');?>" class="btn green pull-right">
								<i class="fa fa-plus-circle"></i> Add
							</a>
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<div class="row">
				<div class="col-md-6 col-sm-12 col-xs-12">
					<div class="portlet box blue">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-info-circle"></i> Banner Location
							</div>
						</div>
						<div class="portlet-body form">
							<ul class="menu nav form-group">
								<?php
								if (isset($collect['banner_location'])) {
									if (is_array($collect['banner_location']) && count($collect['banner_location'])) {
										foreach ($collect['banner_location'] as $val) {
											if ($val->location == $banner_location) {
												$li_href_active = 'btn-primary';
											} else {
												$li_href_active = '';
											}
											?>
											<li class="<?=$li_href_active;?>">
												<a href="<?= base_url($base_path . '/banner/lists/' . $val->location);?>">
													<i class="fa fa-bars"></i>
													<span><?=$val->location;?></span>
												</a>
											</li>
											<?php
										}
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-xs-6">
					<!-- BEGIN FORM-->
					<form action="<?= base_url($base_path . '/banner/lists');?>" class="form-horizontal" role="form" id="searh-form" method="post">
						<div class="input-group">
							<input name="search_text" class="form-control" placeholder="Search...." type="text" id="search_text" value="<?=$search_text;?>" />
							<input type="hidden" name="menu_type" value="<?= (isset($menu_type) ? $menu_type : 'top');?>" />
							<span class="input-group-btn">
								<input  class="btn btn-primary" type="submit" value="Search"/>
							</span>
						</div>	
					</form>
					<!-- END FORM-->
				</div>
			</div>
			<!-- START MAIN CONTENT -->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12">
					<div class="box box-body table-responsive no-padding">
						<?php
						if (isset($collect['menu_items']['data'])) {
							if(is_array($collect['menu_items']['data'])) {
								if (count($collect['menu_items']['data']) > 0) {
									?>
									<table class="table table-hover">
										<thead>
											<tr>
												<th>No.</th>
												<th>Title</th>
												<th>Path</th>
												<th>Link</th>
												<th>Active</th>
												<th>Parent</th>
												<th>Ordering</th>
												<th class="text-center">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$for_i = 1;
											foreach($collect['menu_items']['data'] as $keval) {
												?>
												<tr>
													<td><?php echo $for_i; ?></td>
													<td><?php echo $keval->menu_title; ?></td>
													<td><?php echo $keval->menu_path; ?></td>
													<td><a href="<?= base_url($keval->menu_path);?>"><?php echo base_url($keval->menu_path); ?></a></td>
													<td>
														<?php 
														if ($keval->menu_is_active === 'Y') {
															echo '<span class="btn btn-sm btn-success">' . $keval->menu_is_active . '</span>';
														} else {
															echo '<span class="btn btn-sm btn-warning">' . $keval->menu_is_active . '</span>';
														}
														?>
													</td>
													<td>
														<?php 
														if ($keval->menu_is_parent === 'Y') {
															echo '<span class="btn btn-sm btn-primary">' . $keval->menu_is_parent . '</span>';
														} else {
															echo '<span class="btn btn-sm btn-default">' . $keval->menu_is_parent . '</span>';
														}
														?>
													</td>
													<td><?=$keval->menu_order;?></td>
													<td class="text-center">
														<a class="btn btn-sm btn-warning" href="<?php echo base_url("{$base_path}/menu/view/{$keval->seq}"); ?>">
															<i class="fa fa-eye"></i>
														</a>
														<a class="btn btn-sm btn-info" href="<?php echo base_url("{$base_path}/menu/edit/{$keval->seq}"); ?>">
															<i class="fa fa-pencil"></i>
														</a>
														<a class="btn btn-sm btn-danger" href="javascript:;" data-userid="<?=$keval->seq;?>" onclick="javascript:deleteuser('<?=$keval->seq;?>');">
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
										There is no item menu on this menu type
									</div>
									<?php
								}
							}
						}
						?>
					</div>


				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?=$collect['pagination'];?>
				</div>
			</div>
			<!-- END MAIN CONTENT -->

			

			
			
			
		</div>
	</div>
	
	
	