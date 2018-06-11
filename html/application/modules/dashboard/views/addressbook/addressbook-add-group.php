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
							<i class="fa fa-briefcase"></i>
							Group
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-plus-square"></i> Add
						</li>
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
								Add New Group
							</h3>
						</div>
						<div class="box-body no-padding">
							<form id="addressbook-add" action="<?php echo base_url($base_path . '/index.php/addressbook/addaction/group') ?>" method="post" role="form">
								<div class="form-body">
									<div class="form-group required">
										<div class="row">
											<div class="col-md-6">
												<label for="group_name">Group Name</label>
												<input type="text" class="form-control required" id="group_name"  name="group_name" maxlength="120">
											</div>
											<div class="col-md-6">
												<label for="group_parent">Group Parent</label>
												<select class="form-control required" id="group_parent" name="group_parent">
													<option value="0">No Parent</option>
													<?php
													if (isset($collect['addressbook']['group']['parent'])) {
														if (count($collect['addressbook']['group']['parent']) > 0) {
															foreach ($collect['addressbook']['group']['parent'] as $val) {
																echo "<option value='{$val->seq}'>{$val->group_name_text}</option>";
															}
														}
													}
													if (isset($collect['addressbook']['group']['child'])) {
														if (count($collect['addressbook']['group']['child']) > 0) {
															foreach ($collect['addressbook']['group']['child'] as $val) {
																echo "<option value='{$val->seq}'>{$val->group_name_text}</option>";
															}
														}
													}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
						
						<div class="box-footer">
							<div class="form-group">
								<button id="save-this-item" type="submit" class="btn btn-primary">Save Group</button>
								<button id="cancel-this-item" type="button" class="btn btn-default">Cancel</button>
							</div>
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
	
<script type="text/javascript">
	var stringData = "";
	<?php
	//==========================================
	// Add Configuration
	//==========================================
	?>
	$('#save-this-item').click(function() {
		var objData = {};
		$('#addressbook-add').submit();
	});
	<?php
	//==========================================
	// Cancel add
	//==========================================
	?>
	$('#cancel-this-item').click(function() {
		location.href = '<?= base_url($base_path . "/index.php/addressbook/lists");?>';
	});
</script>
	
	
	