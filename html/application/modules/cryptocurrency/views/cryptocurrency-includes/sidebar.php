<?php
if (!defined('PHP_MYSQL_CRUD_NATIVE')) { exit('Script cannot access directly.'); }

/*
echo "<pre>";
print_r($collect);
exit;
*/
?>


		<!-- BEGIN SIDEBAR -->
		<!--
		
		-->
		<div class="page-sidebar-wrapper">
			<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
				<ul class="page-sidebar-menu">
					<li class="sidebar-toggler-wrapper">
						<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
						<div class="sidebar-toggler hidden-phone"></div>
					</li>
					<li class="<?= set_sidebar_active('about', 'service', $collect['match']);?>">
						<a href="<?= base_url($base_dashboard_path . '/about');?>">
							<i class="fa fa-question-circle"></i>
							<span class="title">About</span>
							<span class="selected"></span>
						</a>
					</li>
					<li class="start <?= set_sidebar_active('index', 'service', $collect['match']);?>">
						<a href="<?= base_url($base_dashboard_path . '/index');?>">
							<i class="fa fa-home"></i>
							<span class="title">Home</span>
							<span class="selected"></span>
						</a>
					</li>
					<?php
					if (in_array($collect['userdata']['account_role'], base_config('admin_role'))) {
						?>
						<li class="<?= set_sidebar_active('users', 'service', $collect['match']);?>">
							<a href='<?= base_url($base_dashboard_path . '/users');?>'>
								<i class='fa fa-users'></i>
								<span class='title'>Users</span>
								<span class='selected'></span>
								<span class='arrow'></span>
							</a>
							<ul class='sub-menu'>
								<li class='<?= set_sidebar_active(array('service' => 'users', 'method' => 'lists'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_dashboard_path . '/users/lists');?>'><i class='fa fa-user'></i> Lists</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'users', 'method' => 'add'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_dashboard_path . '/users/add');?>'><i class='fa fa-plus'></i> Add</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'users', 'method' => 'edit'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_dashboard_path . '/users/edit');?>'><i class='fa fa-pencil'></i> Edit</a>
								</li>
							</ul>
						</li>
						<?php
					}
					if (in_array($collect['userdata']['account_role'], base_config('editor_role'))) {
						?>
						<li class="<?= set_sidebar_active('cryptocurrency', 'service', $collect['match']);?>">
							<a href='<?= base_url($base_path . '/cryptocurrency');?>'>
								<i class='fa fa-building'></i>
								<span class='title'>Cryptocurrency</span>
								<span class='selected'></span>
								<span class='arrow'></span>
							</a>
							<ul class='sub-menu'>
								<li class='<?= set_sidebar_active(array('service' => 'cryptocurrency', 'method' => 'listmarket'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/cryptocurrency/listmarket');?>'><i class='fa fa-university'></i> Crypto Market</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'cryptocurrency', 'method' => 'listcurrency'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/cryptocurrency/listcurrency');?>'><i class='fa fa-btc'></i> Crypto Currency</a>
								</li>
							</ul>
						</li>
						<li class="<?= set_sidebar_active('exchange', 'service', $collect['match']);?>">
							<a href='<?= base_url($base_path . '/exchange');?>'>
								<i class='fa fa-money'></i>
								<span class='title'>Exchange Money</span>
								<span class='selected'></span>
								<span class='arrow'></span>
							</a>
							<ul class='sub-menu'>
								<li class='<?= set_sidebar_active(array('service' => 'exchange', 'method' => 'listexchange'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/exchange/listexchange');?>'><i class='fa fa-exchange'></i> Lists Exchange</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'exchange', 'method' => 'addexchange'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/exchange/addexchange');?>'><i class='fa fa-plus-circle'></i> Add Exchange</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'exchange', 'method' => 'listcurrency'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/exchange/listcurrency');?>'><i class='fa fa-usd'></i> Lists Currency</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'exchange', 'method' => 'addcurrency'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/exchange/addcurrency');?>'><i class='fa fa-plus-square'></i> Add Currency</a>
								</li>
							</ul>
						</li>
						<li class="<?= set_sidebar_active('ticker', 'service', $collect['match']);?>">
							<a href='<?= base_url($base_path . '/ticker');?>'>
								<i class='fa fa-database'></i>
								<span class='title'>Ticker Data</span>
								<span class='selected'></span>
								<span class='arrow'></span>
							</a>
							<ul class='sub-menu'>
								<li class='<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'listenabled'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/ticker/listenabled');?>'>
										<i class='fa fa-calculator'></i> List
									</a>
								</li>
								<li class='<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'addenabled'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/ticker/addenabled');?>'>
										<i class='fa fa-file-text-o'></i> Add
									</a>
								</li>

								<li class="<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'email'), 'methodsub', $collect['match']);?>">
									<a href='<?= base_url($base_path . '/ticker/email');?>'>
										<i class='fa fa-envelope'></i>
										<span class='title'>Notification Emails</span>
										<span class='selected'></span>
										<span class='arrow'></span>
									</a>
									<ul class='sub-menu'>
										<li class='<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'emailaddress', 'segment' => 'view'), 'methodchild', $collect['match']);?>'>
											<a href='<?= base_url($base_path . '/ticker/emailaddress/view');?>'>
												<i class='fa fa-rss'></i> Address
											</a>
										</li>
										<li class='<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'emailtemplates', 'segment' => 'all'), 'methodchild', $collect['match']);?>'>
											<a href='<?= base_url($base_path . '/ticker/emailtemplates/all');?>'>
												<i class='fa fa-reply'></i> Templates
											</a>
										</li>
									</ul>
								</li>
								<!--
								<li class='<?= set_sidebar_active(array('service' => 'ticker', 'method' => 'data'), 'method', $collect['match']);?>'>
									<a href='<?= base_url($base_path . '/ticker/data');?>'>
										<i class='fa fa-table'></i> Comparison Data
									</a>
								</li>
								-->
							</ul>
						</li>
						<?php
					}
					?>
					
					
				</ul>
				<!-- END SIDEBAR MENU -->
			</div>
		</div>
		<!-- END SIDEBAR -->	