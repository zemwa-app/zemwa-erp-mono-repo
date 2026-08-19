<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta name="keywords" content="HTML5 Template" />
	<meta name="description" content="Special">
	<meta name="author" content="">

	<!-- Title -->
	<title>{{ $content['page_title'] ? : '' }}</title>

	<!-- Mobile Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicon
	<link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">  -->

	<!-- CSS -->
	<link href="{{ Module::asset('landingpagepro:templates/lib/bootstrap/css/bootstrap.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/fontawesome/css/font-awesome.min.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/magnific/magnific-popup.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/slick/slick.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/slick/slick-theme.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/bootstrap-select.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/font.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/colors.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/main.css') }}" type="text/css" rel="stylesheet" />

	<!-- IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	<style>
		.banner-bg-5 { background-image: url("{{ $content['banner_header_image'] ? : '' }}"); }
	</style>
</head>
<body>
	<!-- =============== Header =============== -->
	<header class="header-st-5">
		<div class="topbar white-bg sticky">
			<div class="container">
				<div class="row">
					<div class="col-md-2 col-sm-4 mobile-dis">
						<div class="logo">
							<a href="#">
								<img src="{{ $content['page_logo'] ? : '' }}" alt="logo" />
							</a>
						</div> <!-- ../logo -->
					</div>
					<div class="col-md-10 col-sm-12 col-xs-12">
						<div class="menu-container clearfix">
							<!-- Brand and toggle get grouped for better mobile display -->
							<div class="nav-header mobile-show">
								<button type="button" class="nav-toggle">
									<i class="fa fa-bars"></i>
								</button>
								<div class="mobile-logo">
									<a href="#">
										<img src="{{ $content['page_logo'] ? : '' }}" alt="logo" />
									</a>
								</div> <!-- ../logo -->
							</div>
							<ul class="list-style-none menu pull-right">
								<li><a href="#home">Home</a></li>
								<li><a href="#about">About</a></li>
								<li><a href="#specials">Specials</a></li>
								<li><a href="#team">Team</a></li>
								<li><a href="#package">Package</a></li>
								<li><a href="#contact">Contact</a></li>
							</ul>
						</div><!-- ../menu-container -->
					</div>
				</div>
			</div>
		</div>
		<div class="top-bar-recover"></div>
	</header>
	<!-- =============== ../Header =============== -->
	<!-- =============== ../Section Banner =============== -->
		<div id="home" class="section-banner-st-5 banner-bg-5">
			<div class="banner-text text-center ">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<h1 class="banner-h1">{!! $content['banner_header1'] ? : '' !!}</h1>
							<h2 class="banner-h2">{{ $content['banner_header2'] ? : '' }}</h2>
							<a href="{{ $content['banner_url'] ? : '' }}" class="btn btn-primary">read more</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- =============== ../Section Banner =============== -->
	<!-- =============== Main =============== -->
	<main>
		<!-- =============== Section Services Style 4 =============== -->
		<section id="about" class="section section-aboutus-st-2 pad-top-80 pad-bottom-50">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-sm-12 col-xs-12 aboutus-box pull-right">
						<div class="aboutus-box-inner">
							<img src="{{ $content['about_bg'] ? : '' }}" alt="restaurents1" class="img-responsive" />
						</div>
					</div><!-- ../service-box -->
					<div class="col-md-6 col-sm-12 col-xs-12 aboutus-box pull-left">
						<div class="aboutus-box-inner">
							<div class="aboutus-title clearfix">
								<h3>{!! $content['about_header'] ? : '' !!}</h3>
							</div>
							<div class="aboutus-descrip">
								<p>{{ $content['about_content'] ? : '' }}</p>
							</div>
							<div class="aboutus-link">
								<a href="{{ $content['about_link'] ? : '' }}" class="btn btn-default btn-overlay-effect" >read more</a>
							</div>
						</div>
					</div><!-- ../service-box -->
				</div>
			</div>
		</section>
		<!-- =============== ../Section Services Style  4 =============== -->
		<!-- =============== Section Product Style 2 =============== -->
		<section id="specials" class="section section-product-st-2 clearfix">
			<div class="section-title section-title-st-5">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h1>{{ $content['special_header'] ? : '' }}</h1>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-sm-4 col-xs-12 product-box">
							<div class="product-box-inner">
								<div class="product-thumb">
									<img src="{{ $content['special_item_header_image1'] ? : '' }}" alt="product6" class="img-responsive" />
									<span class="category-icon">
										<i class="fa {{ $content['special_item_icon1'] ? : '' }}" aria-hidden="true"></i>
									</span>
								</div>
								<div class="product-content">
									<div class="product-title clearfix">
										<h4>{{ $content['special_item_header1'] ? : '' }}</h4>
										<p>{{ $content['special_item_content1'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../product-box -->
						<div class="col-md-4 col-sm-4 col-xs-12 product-box">
							<div class="product-box-inner">
								<div class="product-thumb">
									<img src="{{ $content['special_item_header_image2'] ? : '' }}" alt="product7" class="img-responsive" />
									<span class="category-icon">
										<i class="fa {{ $content['special_item_icon2'] ? : '' }}" aria-hidden="true"></i>
									</span>
								</div>
								<div class="product-content">
									<div class="product-title clearfix">
										<h4>{{ $content['special_item_header2'] ? : '' }}</h4>
										<p>{{ $content['special_item_content2'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../product-box -->
						<div class="col-md-4 col-sm-4 col-xs-12 product-box">
							<div class="product-box-inner">
								<div class="product-thumb">
									<img src="{{ $content['special_item_header_image3'] ? : '' }}" alt="product8" class="img-responsive" />
									<span class="category-icon">
										<i class="fa {{ $content['special_item_icon3'] ? : '' }}" aria-hidden="true"></i>
									</span>
								</div>
								<div class="product-content">
									<div class="product-title clearfix">
										<h4>{{ $content['special_item_header3'] ? : '' }}</h4>
										<p>{{ $content['special_item_content3'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../product-box -->
					</div>
				</div>
			</div><!-- ../section-content -->
		</section>
		<!-- =============== ../Section Product Style 2 =============== -->
		<!-- =============== Section Team Style 4 =============== -->
		<section id="team" class="section section-team-st-4 clearfix">
				<div class="section-title section-title-st-5">
					<div class="container">
						<div class="row">
							<div class="col-md-12 text-center">
								<h1>{{ $content['team_header'] ? : '' }}</h1>
							</div>
						</div>
					</div>
				</div> <!-- ../section-title -->
				<div class="section-content">
					<div class="container">
						<div class="row">
							<div class="team-box">
								<div class="col-md-6 col-sm-12 col-xs-12">
									<div class="team-thumb">
										<img src="{{ $content['team_image'] ? : '' }}" alt="team9" class="img-responsive" />
									</div>
								</div>
								<div class="col-md-6 col-sm-12 col-xs-12">
									<div class="team-info">
										<div class="team-name clearfix">
											<h4>{{ $content['team_member_name'] ? : '' }}</h4>
										</div>
										<div class="team-position">
											<strong>{{ $content['team_member_designation'] ? : '' }}</strong><br/>
										</div>
										<div class="team-descrip">
											<p>{{ $content['team_member_about'] ? : '' }}</p>
										</div>

									</div>
								</div>
							</div><!-- ../team-box -->
						</div>
					</div>
				</div><!-- ../section-content -->
		</section>
		<!-- =============== ../Section Team Style 4 =============== -->
		<!-- =============== Section Package Style 2 =============== -->
		<section id="package" class="section section-package-st-2 ">
			<div class="overlay overlay-bg-secondary"></div>
			<div class="package-content">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-sm-4 col-xs-12 package-box">
							<div class="package-box-inner text-center clearfix">
								<div class="package-upper">
									<div class="package-title clearfix">
										<h3>{{ $content['product_header1'] ? : '' }}</h3>
									</div>
									<div class="package-price">
										<h1>{!! $content['product_price1'] ? : '' !!}</h1>
									</div>
								</div>
								<div class="package-inner-content">
									<p>{{ $content['product_content1'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../package-box -->
						<div class="col-md-4 col-sm-4 col-xs-12 package-box">
							<div class="package-box-inner text-center clearfix">
								<div class="package-upper">
									<div class="package-title clearfix">
										<h3>{{ $content['product_header2'] ? : '' }}</h3>
									</div>
									<div class="package-price">
										<h1>{!! $content['product_price2'] ? : '' !!}</h1>
									</div>
								</div>
								<div class="package-inner-content">
									<p>{{ $content['product_content2'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../package-box -->
						<div class="col-md-4 col-sm-4 col-xs-12 package-box">
							<div class="package-box-inner text-center clearfix">
								<div class="package-upper">
									<div class="package-title clearfix">
										<h3>{{ $content['product_header3'] ? : '' }}</h3>
									</div>
									<div class="package-price">
										<h1>{!! $content['product_price3'] ? : '' !!}</h1>
									</div>
								</div>
								<div class="package-inner-content">
									<p>{{ $content['product_content3'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../package-box -->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Package Style 2 =============== -->
		<!-- =============== Section Subscribe Style 4 =============== -->
		<section id="contact" class="section section-subscribe section-subscribe-st-5 text-center">
			<div class="subscribe-st-5-inner">
				<div class="container">
					<div class="row">
						<div class="col-md-12 subscribe-content">
							<div class="subscribe-content-inner">
								<h3 class="title-main-color">{{ $content['lead_form_title'] ? : '' }}</h3>
								<p>{{ $content['lead_form_content'] ? : '' }}</p>
							</div>
						</div>
						<div class="col-md-12 subscribe-form">
							<div class="subscribe-form-inner">
								<iframe src='{{ $content['lead_form'] ? : '' }}' frameborder='0' scrolling='yes' style='background-color:#000; display:block; width:100%; min-height:210px;'></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Subscribe Style 4 =============== -->
	</main>
	<!-- =============== ../Main =============== -->
	<!-- =============== Footer =============== -->
	<footer class="footer-st-5">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-sm-8 col-xs-12 text-left">
					<p>&copy 2024, All Rights Reserved</p>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-12 text-right">
					<div class="ft-social-row">
						<ul class="social-default social-icon">
							<li>
								<a href="{{ $content['linkedin_url'] ?: '' }}">
									<i class="fa fa-linkedin"></i>
								</a>
							</li>
							<li>
								<a href="{{ $content['twitter_url'] ?: '' }}">
									<i class="fa fa-twitter"></i>
								</a>
							</li>
							<li>
								<a href="{{ $content['fb_url'] ?: '' }}">
									<i class="fa fa-facebook"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<!-- =============== ../Footer =============== -->

	<!-- =============== Javascript =============== -->
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.min.js') }}"></script><!-- Jquery Library -->
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/bootstrap/js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery-ui.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/bootstrap-select.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/moment-locale.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/dtpicker.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/magnific/jquery.magnific-popup.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/slick/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/countdown/jquery.countdown.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.ui.touch-punch.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/allinone_carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/main.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/mail/form-triger.js') }}"></script>

</body>
