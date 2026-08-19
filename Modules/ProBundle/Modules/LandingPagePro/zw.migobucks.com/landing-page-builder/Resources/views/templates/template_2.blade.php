<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta name="keywords" content="HTML5 Template" />
	<meta name="description" content="Special">
	<meta name="author" content="">

	<!-- Title -->
	<title>{{ $content['page_title'] }}</title>

	<!-- Mobile Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Favicon
	<link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">  -->

	<!-- CSS -->
	<link href="{{ Module::asset('landingpagepro:templates/lib/bootstrap/css/bootstrap.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:lib/bootstrap/css/bootstrap.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/fontawesome/css/font-awesome.min.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/magnific/magnific-popup.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/slick/slick.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/lib/slick/slick-theme.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/allinone_carousel.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/bootstrap-select.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/font.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/colors.css') }}" type="text/css" rel="stylesheet" />
	<link href="{{ Module::asset('landingpagepro:templates/css/main.css') }}" type="text/css" rel="stylesheet" />

	<!-- IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<?php
	//dd($content);
?>
	<!-- =============== Header =============== -->
	<!-- =============== ../Section Banner =============== -->
	<div id="home" class="section-banner-st-6 banner-bg-6">
		<div class="banner-text">
			<div class="container">
				<div class="row">
					<div class="col-md-5 col-sm-5 col-xs-12 pull-right">
						<img src="{{ $content['banner_app2'] }}" alt="app2" class="banner-app2" />
						<img src="{{ $content['banner_app1'] }}" alt="app1" class="banner-app1" />
					</div>
					<div class="col-md-7 col-sm-7 col-xs-12 pull-left">
						<h1 class="banner-h1">{{ $content['banner_h1'] }}</h1>
						<p>{{ $content['banner_text'] }}</p>
						<a href="{{ $content['banner_url'] }}" class="btn btn-default">learn more</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- =============== ../Section Banner =============== -->
	<header class="header-st-6">
		<div class="topbar2 white-bg">
			<div class="container">
				<div class="row">
					<div class="col-md-2 col-sm-4 mobile-dis">
						<div class="logo">
							<a href="#">
								<img src="{{ $content['app_logo'] }}" alt="logo" />
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
										<img src="{{ $content['app_logo'] }}" alt="logo" />
									</a>
								</div> <!-- ../logo -->
							</div>
							<ul class="list-style-none menu pull-right">
								<li><a href="#home">Home</a></li>
								<li><a href="#about">About</a></li>
								<li><a href="#features">Features</a></li>
								<li><a href="#gallery">Gallery</a></li>
								<li><a href="#testimonials">Testimonials</a></li>
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
	<!-- =============== Main =============== -->
	<main>
		<!-- =============== Section Services Style 4 =============== -->
		<section id="about" class="section section-aboutus-st-3 pad-top-120">
			<div class="container">
				<div class="row">
					<div class="col-md-12  text-center">
						<div class="aboutus-app-inner">
							<h3 class="about-title">{{ $content['about_title'] }}</h3>
							<p>{!! $content['about_content'] !!}</p>
							<div class="aboutappimg">
								<img src="{{ $content['about_image'] }}" alt="app4"  />
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Services Style  4 =============== -->
		<!-- =============== Section Features Box Style 1 =============== -->
		<section id="features" class="section section-features-st-1 clearfix pad-top-120 pad-bottom-120">
			<div class="container">
				<div class="row">
					<div class="col-md-7 col-sm-12 col-xs-12 pull-right">
						<div class="featureapp-imgbox">
							<img src="{{ $content['feature_image1'] }}" alt="product9" class="featureapp1" />
							<img src="{{ $content['feature_image2'] }}" alt="product10" class="featureapp2" />
						</div>
					</div>
					<div class="col-md-5 col-sm-12 col-xs-12 pull-left">
						<div class="featureapp-box">
							<div class="featureapp-title">
								<span class="">{{ $content['feature_title_text'] }}</span>
								<h3>{{ $content['feature_title_header'] }}</h3>
							</div>
							<div class="featureapp-descrip">
								<p>{{ $content['feature_content'] }}</p>
								<ul class="featuerlists">
									<li>
										<span>
											<i class="fa fa-check"></i>
										</span>
										{{ $content['feature_list1'] }}
									</li>
									<li>
										<span>
											<i class="fa fa-check"></i>
										</span>
										{{ $content['feature_list2'] }}
									</li>
									<li>
										<span>
											<i class="fa fa-check"></i>
										</span>
										{{ $content['feature_list3'] }}
									</li>
									<li>
										<span>
											<i class="fa fa-check"></i>
										</span>
										{{ $content['feature_list4'] }}
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Features Box Style 1 =============== -->
		<!-- =============== Section Get App Box Style 1 =============== -->
		<section id="download" class="section section-getapp-st-1 clearfix">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-sm-12 col-xs-12 pull-right">
						<div class="getapptext-holder">
							<span>{{ $content['download_title'] }}</span>
							<h3>{{ $content['download_header1'] }}<br/><span class="lgreen-primary-color">{{ $content['download_header2'] }}</span> </h3>
							<p>
								{{ $content['download_content'] }}
							</p>
							<a href="{{ $content['download_link1'] }}">
								<img src="{{ Module::asset('landingpagepro:templates/img/icons/g-play.png') }}" class="get-links mr-rgt-20" alt="g-play" />
							</a>
							<a href="{{ $content['download_link2'] }}">
								<img src="{{ Module::asset('landingpagepro:templates/img/icons/app-store.png') }}" class="get-links" alt="app-store" />
							</a>
						</div>
					</div>
					<div class="col-md-6 col-sm-12 col-xs-12 pull-left">
						<div class="getappimg-holder">
							<img src="{{ $content['download_banner1'] }}" alt="app5" class="get-app1" />
							<img src="{{ $content['download_banner2'] }}" alt="app6" class="get-app2" />
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Get App Box Style 1 =============== -->
		<!-- =============== Section Work Style 1 =============== -->
		<section id="gallery" class="section section-ourwork clearfix lightalice pad-top-120 pad-bottom-70">
			<div class="section-title section-title-st-6">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<span>{{ $content['carousel_title'] }}</span>
							<h1>{{ $content['carousel_header'] }}</h1>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div id="allinone_carousel_charming">
								<ul class="allinone_carousel_list">
									<li><img src="{{ $content['carousel_banner1'] }}" alt="" /></li>
									<li><img src="{{ $content['carousel_banner2'] }}" alt="" /></li>
									<li><img src="{{ $content['carousel_banner3'] }}" alt="" /></li>
									<li><img src="{{ $content['carousel_banner4'] }}" alt="" /></li>
									<li><img src="{{ $content['carousel_banner5'] }}" alt="" /></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Work Style 1 =============== -->
		<!-- =============== ../ Section Partner Style 5 =============== -->
		<section id="partner" class="section section-partners-st-5 partners-indent lightalice pad-top-100 pad-bottom-70">
			<div class="container">
				<div class="row">
					<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
						<div class="partner-box-inner">
							<img src="{{ $content['partner_image1'] }}" alt="helpme" class="img-responsive">
						</div>
					</div><!-- ../partner-box -->
					<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
						<div class="partner-box-inner">
							<img src="{{ $content['partner_image2'] }}" alt="listing" class="img-responsive">
						</div>
					</div><!-- ../partner-box -->
					<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
						<div class="partner-box-inner">
							<img src="{{ $content['partner_image3'] }}" alt="beadmin" class="img-responsive">
						</div>
					</div><!-- ../partner-box -->
					<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
						<div class="partner-box-inner">
							<img src="{{ $content['partner_image4'] }}" alt="openheart" class="img-responsive">
						</div>
					</div><!-- ../partner-box -->
				</div>
			</div>
		</section>
		<!-- =============== ../ Section Partner Style 5 =============== -->
		<!-- =============== ../ Section Partner Style 5 =============== -->
		<section id="contact" class="section section-subscribe section-subscribe-st-6">
			<div class="container">
				<div class="row">
					<div class="col-md-12 subscribe-content">
						<div class="subscribe-content-inner">
							<h3 class="title-main-color">{{ $content['lead_form_header'] ?: '' }}</h3>
							<p class="text-uppercase">{{ $content['lead_form_content'] ?: '' }}</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 subscribe-form">
						<div class="subscribe-form-inner">
							<iframe src='{{ $content['lead_form'] ?: '' }}' frameborder='0' scrolling='yes' style='background-color:#000; display:block; width:100%; min-height:210px;'></iframe>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../ Section Partner Style 5 =============== -->
	</main>
	<!-- =============== ../Main =============== -->
	<!-- =============== Footer =============== -->
	<footer class="footer-st-6 pad-top-40 pad-bottom-35">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12 text-center">
					<div class="ft-social-row">
						<ul class="social-default social-icon">
							<li>
								<a href="{{ $content['linkedin_url'] }}">
									<i class="fa fa-linkedin"></i>
								</a>
							</li>
							<li>
								<a href="{{ $content['twitter_url'] }}">
									<i class="fa fa-twitter"></i>
								</a>
							</li>
							<li>
								<a href="{{ $content['fb_url'] }}">
									<i class="fa fa-facebook"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-md-12 col-sm-12 col-xs-12 text-center text-uppercase">
					<p>© 2023, All Rights Reserved.</p>
				</div>
			</div>
		</div>
	</footer>
	<!-- =============== ../Footer =============== -->

	<!-- =============== Javascript =============== -->

	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.min.js') }}"></script><!-- Jquery Library -->
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/bootstrap/js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery-ui.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/magnific/jquery.magnific-popup.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/countdown/jquery.countdown.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/slick/slick.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.ui.touch-punch.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/allinone_carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/main.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/mail/form-triger.js') }}"></script>

</body>
