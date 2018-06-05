<?php
if (!defined('PHP_MYSQL_CRUD_NATIVE')) { exit('Script cannot access directly.'); }
?>

	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN FLASH MESSAGE-->
			<div class="row">
				<div class="col-md-12">
					<pre><?php
					print_r(Loader::$match);
					?></pre>
				</div>
			</div>
			<!-- END FLASH MESSAGE-->
		
			<!-- BEGIN PAGE HEADER-->
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN PAGE TITLE & BREADCRUMB-->
					<h3 class="page-title">
						Daily Revenue				
					</h3>
					<ul class="page-breadcrumb breadcrumb">
						<li class="btn-group">
							<a href="<?= base_url('');?>" class="btn green pull-right">
								Add Data<i class="fa fa-plus"></i>
							</a>
						</li>
						<li>
							<i class="fa fa-th"></i>
							<a href="/report/interface/player/">Player Report</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							<i class="fa fa-home"></i>Daily Revenue
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
								<i class="fa fa-calendar"></i>
								Select Month/Year
							</div>
							<div class="tools">
								<a class="expand" href="javascript:;"></a>
							</div>
						</div>
						<div class="portlet-body form display-hide">
							<form action="/report/ccu/daily" role="form" id="formSelectMonthYear" method="post">
								<div class="form-body">
									<div class="form-group">
										<i class="fa fa-calendar"></i> Select Month
										<select class="form-control" name="month">
											<option value='01' selected='selected'>January</option><option value='02'>February</option><option value='03'>March</option><option value='04'>April</option><option value='05'>May</option><option value='06'>June</option><option value='07'>July</option><option value='08'>August</option><option value='09'>September</option><option value='10'>October</option><option value='11'>November</option><option value='12'>December</option>
										</select>
									</div>
									<div class="form-group">
										<i class="fa fa-calendar"></i> Select Year
										<select class="form-control" name="year">
											<option value='2015'>2015</option><option value='2016'>2016</option><option value='2017'>2017</option><option value='2018' selected='selected'>2018</option>										
										</select>
									</div>
									<div class="form-group">
										<i class="fa fa-calendar"></i> Select Payment
										<select class="form-control" name="payment">
											<option value="tmw" selected='selected'>TrueMoney Wallet</option>
											<option value="ppadaptive" >PayPal Payment Adaptive</option>
										</select>
									</div>
									<button class="btn blue search-btn" name="logs" type="submit">Show Data</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<!-- BEGIN FORM-->
				<div class="col-md-6 pull-right">
					<form action="/template/interface/player" class="form-horizontal" role="form" id="" method="post">
						<div class="input-group">
							<input name="player_nickname" class="form-control" placeholder="Player Nickname" type="text" id=""/>
							<span class="input-group-btn">
								<input  class="btn btn-primary" type="submit" value="Search"/>
							</span>
						</div>
					</form>	
				</div>
				<!-- END FORM-->
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<div class="table-responsive">
					
					</div>
				</div>
			</div>
						
			<div class="row">
				<div class="col-md-12">
					<p class="text-right">Page {1} from {2}</p>
					<ul class="pagination pull-right">
						<li class="prev">
							<span><i class="fa fa-angle-left"></i></span>
						</li>                
						<li class="active"><span>1</span></li>
						<li><a href="#">2</a></li>
						<li class="next">
							<a href="#" rel="next">
								<i class="fa fa-angle-right"></i>
							</a>
						</li>               
						<li>
							<a href="#" rel="last">
								<i class="fa fa-angle-double-right"></i>
							</a>
						</li>            
					</ul>
				</div>
			</div>
			
		</div>
	</div>
	<!-- END CONTENT -->
	
	
	