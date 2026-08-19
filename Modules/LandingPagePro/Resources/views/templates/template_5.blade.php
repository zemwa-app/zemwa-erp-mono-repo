
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
		.banner-bg-16 { background-image: url("{{ $content['banner_header_image'] ? : '' }}"); }
		.fa-linkedin:hover { background-color: red; }
	</style>
</head>
<body>
	<!-- =============== Header =============== -->
	<header class="header-st-16">
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
									<a href="index.html">
										<img src="{{ $content['page_logo'] ? : '' }}" alt="logo" />
									</a>
								</div> <!-- ../logo -->
							</div>
							<!-- Social Icons-->
							<ul class="social-icon-2 pull-right">
								<li>
									<a class="fa fa-facebook" href="{{ $content['fb_url'] ?: '' }}"></a>
								</li>
								<li>
									<a class="fa fa-twitter" href="{{ $content['twitter_url'] ?: '' }}"></a>
								</li>
								<li>
									<a class="fa fa-linkedin" href="{{ $content['linkedin_url'] ?: '' }}" style="background-color:blue;"></a>
								</li>
							</ul>
							<!-- Social Icons-->
							<ul class="list-style-none menu pull-right">
								<li><a href="#home">Home</a></li>
								<li><a href="#services">services</a></li>
								<li><a href="#team">team</a></li>
								<li><a href="#project">projects</a></li>
								<li><a href="#">Contact</a></li>
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
		<div id="home" class="section-banner-st-16 banner-bg-16">
			<div class="banner-text ">
				<div class="container">
					<div class="register-box pull-right">
						<div class="register-title">
							<h4>{{ $content['lead_form_title'] ? : '' }}</h4>
						</div>
						<div class="register-form">
							<iframe src='{{ $content['lead_form'] ?: '' }}' frameborder='0' scrolling='yes' style='background-color:#000; display:block; width:100%; height:100%;'></iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- =============== ../Section Banner =============== -->
	<!-- =============== Main =============== -->
	<main>
		<!-- =============== Section Services=============== -->
		<section id="services" class="section section-services-st-16">
			<div class="section-title section-title-st-16">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['service_header'] ? : '' }}</h2>
							<p>{{ $content['service_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div>
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon1'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header1'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text1'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon2'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header2'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text2'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon3'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header3'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text3'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon4'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header4'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text4'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon5'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header5'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text5'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 service-box">
							<div class="service-box-inner">
								<div class="services-icon">
									<img src="{{ $content['service_icon6'] ? : '' }}" alt="cosmetics">
								</div>
								<div class="services-title">
									<h4>{{ $content['service_item_header6'] ? : '' }}</h4>
								</div>
								<div class="services-descrip">
									<p>{{ $content['service_item_text6'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../service-box -->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== Section Services=============== -->
		<!-- =============== Section Video=============== -->
		<section class="section section-video-st-16">
			<div class="col-md-6 col-sm-12" data-mh="item-height">
				<div class="overlay"></div>
				<div class="video-box">
					<a class="anchor-overlay overlay popup-vimeo" href="{{ $content['video_url'] ? : '' }}">
						<span class="play-icon">
							<img src="{{ Module::asset('landingpagepro:templates/img/video-icon4.png') }}" alt="">
						</span>
					</a>
				</div><!-- ../video-box -->
			</div>
			<div class="col-md-6 col-sm-12" data-mh="item-height">
				<div class="video-content ">
					<div class="professional-skill-box">
						<div class="professional-skill-title">
							<h5>{{ $content['skill_progress_header1'] ? : '' }} - <span>({{ $content['skill_percentage1'] ? : '' }})</span></h5>
						</div>
						<div class="progress">
							  <div class="progress-bar" style="width:{{ $content['skill_percentage1'] ? : '' }}"></div>
						</div>
					</div>
					<div class="professional-skill-box">
						<div class="professional-skill-title">
							<h5>{{ $content['skill_progress_header2'] ? : '' }} <span>({{ $content['skill_percentage2'] ? : '' }})</span></h5>
						</div>
						<div class="progress">
							  <div class="progress-bar" style="width:{{ $content['skill_percentage2'] ? : '' }}"></div>
						</div>
					</div>
					<div class="professional-skill-box">
						<div class="professional-skill-title">
							<h5>{{ $content['skill_progress_header3'] ? : '' }} - <span>({{ $content['skill_percentage3'] ? : '' }})</span></h5>
						</div>
						<div class="progress">
							  <div class="progress-bar" style="width:{{ $content['skill_percentage3'] ? : '' }}"></div>
						</div>
					</div>
					<div class="professional-skill-box">
						<div class="professional-skill-title">
							<h5>{{ $content['skill_progress_header4'] ? : '' }} - <span>({{ $content['skill_percentage4'] ? : '' }})</span></h5>
						</div>
						<div class="progress">
							  <div class="progress-bar" style="width:{{ $content['skill_percentage4'] ? : '' }}"></div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== Section Video=============== -->
		<!-- =============== Section Team=============== -->
		<section id="team" class="section section-team-st-16">
			<div class="section-title section-title-st-16">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['team_header'] ? : '' }}</h2>
							<p>{{ $content['team_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-sm-6 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image1'] ? : '' }}" alt="team6" class="img-responsive">
								</div>
								<div class="team-info clearfix">
									<div class="team-name">
										<h4>{{ $content['team_name1'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation1'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image2'] ? : '' }}" alt="team6" class="img-responsive">
								</div>
								<div class="team-info clearfix">
									<div class="team-name">
										<h4>{{ $content['team_name2'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation2'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image3'] ? : '' }}" alt="team6" class="img-responsive">
								</div>
								<div class="team-info clearfix">
									<div class="team-name">
										<h4>{{ $content['team_name3'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation3'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
					</div>
				</div>
			</div><!-- ../section-content -->
		</section>
		<!-- =============== Section Team=============== -->
		<!-- =============== Section Project=============== -->
		<section id="project" class="section section-project-st-16">
			<div class="section-title section-title-st-16">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['portfolio_heading'] ? : '' }}</h2>
							<p>{{ $content['portfolio_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-sm-6 col-xs-12 project-box">
							<div class="project-box-inner">
								<div class="project-thumb">
									<img src="{{ $content['portfolio_thumb1'] ? : '' }}" alt="project" class="img-responsive">
								</div>
								<div class="project-content">
									<div class="project-title">
										<h4>{{ $content['portfolio_title1'] ? : '' }}</h4>
									</div>
									<div class="project-descrip">
										<p>{{ $content['portfolio_content1'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../project-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 project-box">
							<div class="project-box-inner">
								<div class="project-thumb">
									<img src="{{ $content['portfolio_thumb2'] ? : '' }}" alt="project" class="img-responsive">
								</div>
								<div class="project-content">
									<div class="project-title">
										<h4>{{ $content['portfolio_title2'] ? : '' }}</h4>
									</div>
									<div class="project-descrip">
										<p>{{ $content['portfolio_content2'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../project-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 project-box">
							<div class="project-box-inner">
								<div class="project-thumb">
									<img src="{{ $content['portfolio_thumb3'] ? : '' }}" alt="project" class="img-responsive">
								</div>
								<div class="project-content">
									<div class="project-title">
										<h4>{{ $content['portfolio_title3'] ? : '' }}</h4>
									</div>
									<div class="project-descrip">
										<p>{{ $content['portfolio_content3'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../project-box -->
					</div>
				</div>
			</div><!-- ../section-content -->
		</section>
		<!-- =============== Section Project=============== -->
		<!-- =============== Section Partners =============== -->
		<section class="section section-partners-st-16">
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-3 partner-box">
							<div class="partner-box-inner">
								<img class="img-responsive" alt="helpme" src="{{ $content['partner_thumb1'] ? : '' }}">
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 partner-box">
							<div class="partner-box-inner">
								<img class="img-responsive" alt="openheart" src="{{ $content['partner_thumb2'] ? : '' }}">
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 partner-box">
							<div class="partner-box-inner">
								<img class="img-responsive" alt="listing" src="{{ $content['partner_thumb3'] ? : '' }}">
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 partner-box">
							<div class="partner-box-inner">
								<img class="img-responsive" alt="beadmin" src="{{ $content['partner_thumb4'] ? : '' }}">
							</div>
						</div><!-- ../partner-box -->
						<div class="col-md-3 partner-box">
							<div class="partner-box-inner">
								<img class="img-responsive" alt="openheart" src="{{ $content['partner_thumb5'] ? : '' }}">
							</div>
						</div><!-- ../partner-box -->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== ../Section Partners Style 4 =============== -->
	</main>
	<!-- =============== ../Main =============== -->
	<!-- =============== Footer =============== -->
	<footer class="footer-st-16">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-sm-6 col-xs-12">
					<div class="footer-logo">
						<a href="#"><img src="{{ $content['page_logo'] ? : '' }}" alt=""></a>
					</div>
				</div>
				<div class="col-md-5 col-sm-6 col-xs-12 text-center">
					<p>&copy; 2024, All Rights Reserved.</p>
				</div>
				<div class="col-md-4 col-sm-12 col-xs-12 text-right">
					<ul class="social-icon-2">
						<li>
							<a class="fa fa-facebook" href="{{ $content['fb_url'] ?: '' }}"></a>
						</li>
						<li>
							<a class="fa fa-twitter" href="{{ $content['twitter_url'] ?: '' }}"></a>
						</li>
						<li>
							<a class="fa fa-linkedin" href="{{ $content['linkedin_url'] ?: '' }}"></a>
						</li>
					</ul>
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
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBueyERw9S41n4lblw5fVPAc9UqpAiMgvM&amp;"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/snazzymap/snazzymap.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/matchheight/jquery.matchHeight-min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/main.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/mail/form-triger.js') }}"></script>

</body>
