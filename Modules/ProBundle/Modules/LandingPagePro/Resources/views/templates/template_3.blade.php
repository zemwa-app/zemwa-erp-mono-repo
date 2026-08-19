<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta name="keywords" content="HTML5 Template" />
	<meta name="description" content="Special">
	<meta name="author" content="">

	<!-- Title -->
	<title>{{ $content['page_title'] ?: '' }}</title>

	<!-- Mobile Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicon
	<link rel="shortcut icon" href="{{ Module::asset('landingpagepro:templates/images/favicon.png') }}" type="image/x-icon">  -->

	<!-- CSS -->
	<link href="{{ Module::asset('landingpagepro:templates/lib/bootstrap/css/bootstrap.css') }}" type="text/css" rel="stylesheet" />
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
	<style>
		.banner-bg-3 { background-image: url("{{ $content['banner_header_image'] ? : '' }}"); }
		.section-calout-st-1 { background-image: url("{{ $content['callout_header_image'] ? : '' }}"); }
	</style>
</head>
<body>
	<!-- =============== Header =============== -->
	<header class="header-st-3">
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
								<li><a href="#services">Services</a></li>
								<li><a href="#gallery">Gallery</a></li>
								<li><a href="#callout">Callout</a></li>
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
		<div id="home" class="section-banner-st-3 banner-bg-3">
			<div class="banner-text ">
				<div class="container">
					<div class="row">
						<div class="col-md-6 pull-right">
							<h1 class="banner-h1">{{ $content['banner_header1'] ? : '' }}</h1>
							<h2 class="banner-h2">{{ $content['banner_header2'] ? : '' }}</h2>
							<p>{{ $content['banner_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	<!-- =============== ../Section Banner =============== -->
	<!-- =============== Main =============== -->
	<main>
		<!-- =============== Section Services Style 3 =============== -->
		<section id="services" class="section section-services-st-3 pad-top-120 pad-bottom-50">
			<div class="container">
				<div class="row">
					<div class="col-md-4 col-sm-4 col-xs-12 service-box text-center ">
						<div class="service-box-inner">
							<div class="services-icon">
								<img src="{{ $content['service_image1'] ? : '' }}" alt="cosmetics" />
							</div>
							<div class="services-title">
								<h4>{{ $content['service_header1'] ? : '' }}</h4>
							</div>
							<div class="services-descrip">
								<p class="lh-22">{!! $content['service_content1'] ? : '' !!}</p>
							</div>
						</div>
					</div><!-- ../service-box -->
					<div class="col-md-4 col-sm-4 col-xs-12 service-box text-center">
						<div class="service-box-inner">
							<div class="services-icon">
								<img src="{{ $content['service_image2'] ? : '' }}" alt="hairdress" />
							</div>
							<div class="services-title">
								<h4>{{ $content['service_header2'] ? : '' }}</h4>
							</div>
							<div class="services-descrip">
								<p class="lh-22">{!! $content['service_content2'] ? : '' !!}</p>
							</div>
						</div>
					</div><!-- ../service-box -->
					<div class="col-md-4 col-sm-4 col-xs-12 service-box text-center">
						<div class="service-box-inner">
							<div class="services-icon">
								<img src="{{ $content['service_image3'] ? : '' }}" class="mr-top-per-5" alt="massage" />
							</div>
							<div class="services-title">
								<h4>{{ $content['service_header3'] ? : '' }}</h4>
							</div>
							<div class="services-descrip">
								<p class="lh-22">{!! $content['service_content3'] ? : '' !!}</p>
							</div>
						</div>
					</div><!-- ../service-box -->
				</div>
			</div>
		</section>
		<!-- =============== ../Section Services Style 3 =============== -->
		<!-- =============== Section Aboutus =============== -->
		<section id="work" class="section section-aboutus mr-bottom-100">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="section-aboutus-inner">
							<div class="row">
								<div class="col-md-6">
									<img src="{{ $content['about_image'] ? : '' }}" class="img-responsive" alt="beauty1" />
								</div>
								<div class="col-md-6">
									<div class="knowustext">
										<h3>{{ $content['about_header'] ? : '' }}</h3>
										<p>{{ $content['about_content'] ? : '' }}</p>
									</div><!--../knowustext -->
								</div>
							</div>
						</div><!--../section-aboutus-inner-->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Aboutus =============== -->
		<!-- =============== Section Gallery Style 2 =============== -->
		<section id="gallery" class="section section-gallery-st-2 clearfix">
			<div class="section-content popup-gallery clearfix">
				<div class="masornay-box">
					<div class="masornay-thumb">
						<img src="{{ $content['gallery_image1'] ? : '' }}" alt="gallery-thumb2" class="img-responsive" />
						<a href="{{ $content['gallery_image1'] ? : '' }}" class="overlay ">
							<span class="search-icon overlay-2nd overlay-bg-main">
								<i class="fa fa-search"></i>
							</span>
						</a>
					</div>
				</div><!-- ../masornay-box -->
				<div class="masornay-box ">
					<div class="masornay-thumb">
						<img src="{{ $content['gallery_image2'] ? : '' }}" alt="gallery-thumb3" class="img-responsive" />
						<a href="{{ $content['gallery_image2'] ? : '' }}" class="overlay ">
							<span class="search-icon overlay-2nd overlay-bg-main">
								<i class="fa fa-search"></i>
							</span>
						</a>
					</div>
				</div><!-- ../masornay-box -->
				<div class="masornay-box ">
					<div class="masornay-thumb">
						<img src="{{ $content['gallery_image3'] ? : '' }}" alt="gallery-thumb4" class="img-responsive" />
						<a href="{{ $content['gallery_image3'] ? : '' }}" class="overlay ">
							<span class="search-icon overlay-2nd overlay-bg-main">
								<i class="fa fa-search"></i>
							</span>
						</a>
					</div>
				</div><!-- ../masornay-box -->
				<div class="masornay-box">
					<div class="masornay-thumb">
						<img src="{{ $content['gallery_image4'] ? : '' }}" alt="gallery-thumb5" class="img-responsive" />
						<a href="{{ $content['gallery_image4'] ? : '' }}" class="overlay ">
							<span class="search-icon overlay-2nd overlay-bg-main">
								<i class="fa fa-search"></i>
							</span>
						</a>
					</div>
				</div><!-- ../masornay-box -->
			</div>
		</section>
		<!-- =============== ../Section Gallery Style 2 =============== -->
		<!-- =============== Section Callout Style 1 =============== -->
		<section id="callout" class="section section-calout-st-1">
			<div class="callout-text text-center">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
								<h1>{{ $content['callout_header'] ? : '' }}</h1>
								<p>{{ $content['callout_content'] ? : '' }}</p>
								<a href="{{ $content['callout_url'] ? : '' }}" class="btn btn-primary">Register now</a>
						</div>
					</div>
				</div>
			</div>
			<div class="overlay overlay-bg-secondary"></div>
		</section>
		<!-- =============== ../Section Callout Style 1 =============== -->
		<!-- =============== Section Partners =============== -->
		<section id="partner" class="section section-partners-st-3 pad-top-70 pad-bottom-70">
			<div class="section-title section-title-st-2 ">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h1>{!! $content['partner_header'] ? : '' !!}</h1>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
							<div class="partner-box-inner">
								<img src="{{ $content['partner_image1'] ? : '' }}" alt="helpme" class="img-responsive" />
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
							<div class="partner-box-inner">
								<img src="{{ $content['partner_image2'] ? : '' }}" alt="listing" class="img-responsive" />
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
							<div class="partner-box-inner">
								<img src="{{ $content['partner_image3'] ? : '' }}" alt="beadmin" class="img-responsive" />
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 col-sm-3 col-xs-6 partner-box">
							<div class="partner-box-inner">
								<img src="{{ $content['partner_image4'] ? : '' }}" alt="openheart" class="img-responsive" />
							</div>
						</div><!-- ../partner-box -->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Partners =============== -->
		<!-- =============== Section Subscribe =============== -->
		<section id="contact" class="section section-subscribe section-subscribe-st-3">
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
		<!-- =============== ../Section Subscribe =============== -->
	</main>
	<!-- =============== ../Main =============== -->
	<!-- =============== Footer =============== -->
	<footer class="footer-st-3">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-sm-8 col-xs-12 text-left">
					<p>&copy; 2023, All Rights Reserved</p>
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
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/magnific/jquery.magnific-popup.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/slick/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/countdown/jquery.countdown.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.ui.touch-punch.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/allinone_carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/main.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/mail/form-triger.js') }}"></script>

</body>
