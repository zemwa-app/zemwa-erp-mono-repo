
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
		.banner-bg-18 { background-image: url("{{ $content['banner_header_image'] ? : '' }}"); }
	</style>
</head>
<body>
	<!-- =============== Header =============== -->
	<header class="header-st-18">
		<div class="topbar3 sticky">
			<div class="container">
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12">
						<div class="menu-container clearfix text-center">
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
							<ul class="list-style-none menu">
								<li><a href="#home">Home</a></li>
								<li><a href="#about">ABOUT</a></li>
								<li><a href="#teams">TEAM</a></li>
								<li>
									<div class="logo">
										<a href="#">
											<img src="{{ $content['page_logo'] ? : '' }}" alt="logo" />
										</a>
									</div> <!-- ../logo -->
								</li>
								<li><a href="#portfolio">WORKS</a></li>
								<li><a href="#services">SERVICES</a></li>
								<li><a href="#contact">CONTACT</a></li>
							</ul>
						</div><!-- ../menu-container -->
					</div>
				</div>
			</div>
		</div>
	</header>
	<!-- =============== ../Header =============== -->

	<!-- =============== ../Section Banner =============== -->
		<div id="home" class="section-banner-st-18 banner-bg-18">
			<div class="banner-text text-center">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div class="banner-slider1">
								<div class="banner-slide1">
									<img src="{{ $content['banner1_header_image'] ? : '' }}" alt="">
									<h1 class="banner-h1">{{ $content['banner1_header1'] ? : '' }}</h1>
									<h3>{{ $content['banner1_header2'] ? : '' }}</h3>
									<a href="{{ $content['banner1_url'] ? : '' }}" class="btn btn-18 btn-default">KNOW MORE</a>
								</div>
								<div class="banner-slide2">
									<div class="col-md-7 col-sm-12 col-xs-12">
										<img class="img-responsive" src="{{ $content['banner2_header_image'] ? : '' }}" alt="">
									</div>
									<div class="col-md-5 col-sm-12 col-xs-12">
										<h1 class="banner-h1">{{ $content['banner2_header1'] ? : '' }}</h1>
										<h2>{{ $content['banner2_header2'] ? : '' }}</h2>
										<p>{{ $content['banner2_content'] ? : '' }}</p>
										<a href="{{ $content['banner2_url'] ? : '' }}" class="btn btn-18 btn-default">KNOW MORE</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="overlay"></div>
		</div>
		<!-- =============== ../Section Banner =============== -->
	<!-- =============== Main =============== -->
	<main>
		<!-- =============== Section about-us =============== -->
		<section id="about" class="section section-aboutus-st-18">
			<div class="section-title section-title-st-18 ">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['about_header'] ? : '' }}</h2>
							<p>{{ $content['about_subtext'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div>
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div class="aboutus-content text-center">
								<p>{{ $content['about_content'] ? : '' }}</p>
							</div>
						</div>
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon1'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header1'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content1'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon2'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header2'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content2'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon3'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header3'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content3'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon4'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header4'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content4'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon5'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header5'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content5'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
						<div class="col-md-4 col-sm-6 col-xs-12 aboutus-box">
							<div class="aboutus-box-inner">
								<div class="aboutus-icon">
									<i class="fa {{ $content['about_list_icon6'] ? : '' }}"></i>
								</div>
								<div class="aboutus-title">
									<h4>{{ $content['about_list_header6'] ? : '' }}</h4>
								</div>
								<div class="aboutus-descrip">
									<p>{{ $content['about_list_content6'] ? : '' }}</p>
								</div>
							</div>
						</div><!-- ../aboutus-box -->
					</div>
				</div>
			</div>
		</section>
		<!-- =============== Section about-us =============== -->
		<!-- =============== Section Callout =============== -->
		<section class="section section-calout-st-18">
			<div class="callout-text text-center">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
								<h1>{{ $content['callout_header1'] ? : '' }}</h1>
								<h2>{{ $content['callout_header2'] ? : '' }}</h2>
								<a class="btn btn-18 btn-default" href="{{ $content['callout_url'] ? : '' }}">{{ $content['callout_btn_text'] ? : '' }}</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- =============== Section Callout =============== -->
		<!-- =============== Section team =============== -->
		<section id="teams" class="section section-team-st-18">
			<div class="section-title section-title-st-18">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['team_header'] ? : '' }}</h2>
							<p>{!! $content['team_content'] ? : '' !!}</p>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-3 col-sm-4 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image1'] ? : '' }}" alt="team6" class="img-responsive">
									<div class="overlay-2nd">
									</div>
								</div>
								<div class="team-info text-center">
									<div class="team-name">
										<h4>{{ $content['team_name1'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation1'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
						<div class="col-md-3 col-sm-4 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image2'] ? : '' }}" alt="team6" class="img-responsive">
									<div class="overlay-2nd">
									</div>
								</div>
								<div class="team-info text-center">
									<div class="team-name">
										<h4>{{ $content['team_name2'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation2'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
						<div class="col-md-3 col-sm-4 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image3'] ? : '' }}" alt="team6" class="img-responsive">
									<div class="overlay-2nd">
									</div>
								</div>
								<div class="team-info text-center">
									<div class="team-name">
										<h4>{{ $content['team_name3'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation3'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
						<div class="col-md-3 col-sm-4 col-xs-12 team-box">
							<div class="team-box-inner">
								<div class="team-thumb">
									<img src="{{ $content['team_image4'] ? : '' }}" alt="team6" class="img-responsive">
									<div class="overlay-2nd">
									</div>
								</div>
								<div class="team-info text-center">
									<div class="team-name">
										<h4>{{ $content['team_name4'] ? : '' }}</h4>
									</div>
									<div class="team-position">
										<p>{{ $content['team_designation4'] ? : '' }}</p>
									</div>
								</div>
							</div>
						</div><!-- ../team-box -->
					</div>
				</div>
			</div><!-- ../section-content -->
		</section>
		<!-- =============== Section team =============== -->
		<!-- =============== Section Skill =============== -->
		<section class="section section-skill">
			<div class="container">
				<div class="row">
					<div class="col-md-4 col-sm-6 col-xs-12 skill-box">
						<div class="skill-title">
							<h3>{{ $content['skill_header'] ? : '' }}</h3>
						</div>
						<div class="skill-descrip">{!! $content['skill_content'] ? : '' !!}</div>
					 </div>
					 <div class="col-md-8 col-sm-6 col-xs-12 skill-box">
						<div class="progress-box">
							<div class="progress-title">
								<h5>{{ $content['skill_progress_header1'] ? : '' }} </h5>
							</div>
							<div class="progress">
								  <div style="width:{{ $content['skill_percentage1'] ? : '' }}" class="progress-bar">
								  	<span>{{ $content['skill_percentage1'] ? : '' }}</span>
								  </div>
							</div>
						</div>
						<div class="progress-box">
							<div class="progress-title">
								<h5>{{ $content['skill_progress_header2'] ? : '' }} </h5>

							</div>
							<div class="progress">
								  <div style="width:{{ $content['skill_percentage2'] ? : '' }}" class="progress-bar">
								  	<span>{{ $content['skill_percentage2'] ? : '' }}</span>
								  </div>
							</div>
						</div>
						<div class="progress-box">
							<div class="progress-title">
								<h5>{{ $content['skill_progress_header3'] ? : '' }}</h5>
							</div>
							<div class="progress">
								  <div style="width:{{ $content['skill_percentage3'] ? : '' }}" class="progress-bar">
								  	<span>{{ $content['skill_percentage3'] ? : '' }}</span>
								  </div>
							</div>
						</div>
						<div class="progress-box">
							<div class="progress-title">
								<h5>{{ $content['skill_progress_header4'] ? : '' }}  </h5>
							</div>
							<div class="progress">
								  <div style="width:{{ $content['skill_percentage4'] ? : '' }}" class="progress-bar">
								  	<span>{{ $content['skill_percentage4'] ? : '' }}</span>
								  </div>
							</div>
						</div>
					 </div>
				</div>
			</div>
		</section>
		<!-- =============== Section Skill =============== -->
		<!-- =============== Section video =============== -->
		<section class="section section-video-st-18 text-center">
			<div class="video-text">
				<div class="video-thumb">
					<a class="popup-vimeo" href="{{ $content['video_url'] ? : '' }}">
						<span class="play-icon2">
							<i class="fa fa-play"></i>
						</span>
					</a>
				</div>
				<div class="video-title">
					<h2>{{ $content['video_title_head'] ? : '' }}</h2>
					<h4>{{ $content['video_title_content'] ? : '' }}</h4>
				</div>
			</div>
			<div class="overlay"></div>
		</section>
		<!-- =============== Section video =============== -->
		<!-- =============== Section Portfolio=============== -->
		<section id="portfolio" class="section section-portolio-st-18 clearfix">
			<div class="section-title section-title-st-18 ">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['portfolio_header'] ? : '' }}</h2>
							<p>{{ $content['portfolio_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div>
			<div class="section-content">
				<div class="container text-center">
		                <div class="popup-gallery2" id="mixitup">
		                	<div class="row">
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-1" data-myorder="1">
			                    	<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb1'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title1'] ? : '' }}</h3>
													<a href="{{ $content['portfolio_thumb1'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url1'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title1'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content1'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-3" data-myorder="3">
			                    	<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb2'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title2'] ? : '' }}</h3>
													<a href="{{ $content['portfolio_thumb2'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url2'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title2'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content2'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-2" data-myorder="4">
			                    	<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb3'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title3'] ? : '' }}</h3>
													<a href="{{ $content['portfolio_thumb3'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url3'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title3'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content3'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-2" data-myorder="2">
			                  		<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb4'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title4'] ? : '' }}/h3>
													<a href="{{ $content['portfolio_thumb4'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url4'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title4'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content4'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-4" data-myorder="6">
			                    	<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb5'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title5'] ? : '' }}</h3>
													<a href="{{ $content['portfolio_thumb5'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url5'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title5'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content5'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
			                    <div class="col-md-4 col-sm-6 col-xs-12 portfolio-box mix category-3" data-myorder="5">
			                    	<div class="portfolio-inner-box">
				                    	<div class="portfolio-thumb">
				                			<img class="img-responsive" src="{{ $content['portfolio_thumb6'] ? : '' }}" alt="">
				                			<div class="overlay">
												<div class="portfolio-icons">
													<h3>{{ $content['portfolio_title6'] ? : '' }}</h3>
													<a href="{{ $content['portfolio_thumb6'] ? : '' }}" class="popup">
														<i class="fa fa-arrows-alt"></i>
													</a>
													<a href="{{ $content['portfolio_url6'] ? : '' }}">
														<i class="fa fa-link"></i>
													</a>
												</div>
											</div>
				                		</div>
			                        	<div class="portfolio-content">
			                        		<h4>{{ $content['portfolio_sub_title6'] ? : '' }}</h4>
			                        		<p>{{ $content['portfolio_content6'] ? : '' }}</p>
			                        	</div>
			                        </div>
			                    </div>
		                	</div>
		                </div>
		            </div>
		        </div>
			</div>
		</section>
		<!-- =============== Section Projects=============== -->
		<!-- =============== Section Countup=============== -->
		<section class="section section-countup-st-18">
			<div class="container text-center">
				<div class="row">
					<div class="col-md-3 col-sm-6 col-xs-12 countup-box">
						<div class="countup-inner-box">
							<div class="countup-icon">
								<i class="fa fa-star"></i>
							</div>
							<div class="counter">
								{{ $content['countup_number1'] ? : '' }}
							</div>
							<div class="countup-title">
								<h5>{{ $content['countup_text1'] ? : '' }}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12 countup-box">
						<div class="countup-inner-box">
							<div class="countup-icon">
								<i class="fa fa-star"></i>
							</div>
							<div class="counter">
								{{ $content['countup_number2'] ? : '' }}
							</div>
							<div class="countup-title">
								<h5>{{ $content['countup_text2'] ? : '' }}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12 countup-box">
						<div class="countup-inner-box">
							<div class="countup-icon">
								<i class="fa fa-star"></i>
							</div>
							<div class="counter">
								{{ $content['countup_number3'] ? : '' }}
							</div>
							<div class="countup-title">
								<h5>{{ $content['countup_text3'] ? : '' }}</h5>
							</div>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12 countup-box">
						<div class="countup-inner-box">
							<div class="countup-icon">
								<i class="fa fa-star"></i>
							</div>
							<div class="counter">
								{{ $content['countup_number4'] ? : '' }}
							</div>
							<div class="countup-title">
								<h5>{{ $content['countup_text4'] ? : '' }}</h5>
							</div>
						</div>
					</div>
			    </div>
		    </div>
		</section>
		<!-- =============== Section Countup=============== -->
		<!-- =============== Section Services =============== -->
		<section id="services" class="section section-services-st-18">
			<div class="section-title section-title-st-18">
				<div class="container">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>{{ $content['service_header'] ? : '' }}</h2>
							<p>{{ $content['service_content'] ? : '' }}</p>
						</div>
					</div>
				</div>
			</div> <!-- ../section-title -->
			<div class="section-content">
				<div class="container">
					<div class="row">
						<div class="col-md-3 col-xs-12 text-right">
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<h4>{{ $content['service_item_header1'] ? : '' }}</h4>
										<span class="services-icon">
											<i class="fa {{ $content['service_icon1'] ? : '' }}"></i>
										</span>
									</div>
									<div class="services-descrip">
										<p>{{ $content['service_item_text1'] ? : '' }}</p>
									</div>
								</div>
							</div><!-- ../service-box -->
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<h4>{{ $content['service_item_header2'] ? : '' }}</h4>
										<span class="services-icon">
											<i class="fa {{ $content['service_icon2'] ? : '' }}"></i>
										</span>
									</div>
									<div class="services-descrip">
										<p>{{ $content['service_item_text2'] ? : '' }}</p>
									</div>
								</div>
							</div><!-- ../service-box -->
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<h4>{{ $content['service_item_header3'] ? : '' }}</h4>
										<span class="services-icon">
											<i class="fa {{ $content['service_icon3'] ? : '' }}"></i>
										</span>
									</div>
									<div class="services-descrip">
										<p>{{ $content['service_item_text3'] ? : '' }}</p>
									</div>
								</div>
							</div><!-- ../service-box -->
						</div>
						<div class="col-md-6 col-xs-12">
							<div class="services-img">
								<img class="img-responsive" alt="" src="{{ $content['service_section_image'] ? : '' }}">
							</div>
						</div>
						<div class="col-md-3 col-xs-12">
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<span class="services-icon">
											<i class="fa {{ $content['service_icon4'] ? : '' }}"></i>
										</span>
										<h4>{{ $content['service_item_header4'] ? : '' }}</h4>
									</div>
									<div class="services-descrip">
										<p>{{ $content['service_item_text4'] ? : '' }}</p>
									</div>
								</div>
							</div><!-- ../service-box -->
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<span class="services-icon">
											<i class="fa {{ $content['service_icon5'] ? : '' }}"></i>
										</span>
										<h4>{{ $content['service_item_header5'] ? : '' }}</h4>
									</div>
									<div class="services-descrip">
										<p>{{ $content['service_item_text5'] ? : '' }}</p>
									</div>
								</div>
							</div><!-- ../service-box -->
							<div class="service-box">
								<div class="service-box-inner">
									<div class="services-title">
										<span class="services-icon">
											<i class="fa {{ $content['service_icon6'] ? : '' }}"></i>
										</span>
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
			</div>
		</section>
		<!-- =============== Section Services =============== -->
		<!-- =============== Section Contact =============== -->
		<section id="contact" class="section section-contact-st-18">
				<div class="section-title section-title-st-18">
					<div class="container">
						<div class="row">
							<div class="col-md-12 text-center">
								<h2>{{ $content['lead_header'] ? : '' }}</h2>
								<p>{{ $content['lead_content'] ? : '' }}</p>
							</div>
						</div>
					</div>
				</div> <!-- ../section-title -->
				<div class="section-content">
					<div class="container">
						<div class="row">
							<div class="col-md-3 col-sm-5 col-xs-12">
								<div class="contact-content">
									<div class="contact-details">
										<h3>Contact Details</h3>
										<ul>
											<li>{{ $content['lead_address'] ? : '' }}</li>
											<li><a href="{{ $content['lead_number'] ? : '' }}tel:+12 123 456 789">{{ $content['lead_number'] ? : '' }}</a></li>
											<li><a href="mailto:{{ $content['lead_email'] ? : '' }}">{{ $content['lead_email'] ? : '' }}</a></li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-md-8 col-sm-7 col-xs-12 pull-right">
								<h3>{{ $content['lead_form_title'] ? : '' }}</h3>
								<iframe src='{{ $content['lead_form'] ?: '' }}' frameborder='0' scrolling='yes' style='background-color:#000; display:block; width:100%; min-height:210px;'></iframe>
							</div>
						</div>
					</div>
				</div>
		</section>
		<!-- =============== Section Contact =============== -->
	</main>
	<!-- =============== ../Main =============== -->
	<!-- =============== Footer =============== -->
	<footer class="footer-st-18">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<ul class="social-rounded social-icon">
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
						<li>
							<a href="{{ $content['linkedin_url'] ?: '' }}">
								<i class="fa fa-linkedin"></i>
							</a>
						</li>
					</ul>
					<p class="ft-credits">&copy; 2016, All Rights Reserved, Developed by <a href="#">designsvilla</a></p>
					<a href="#home" class="scrollup"><i class="fa fa-angle-double-up"></i></a>
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
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/magnific/jquery.magnific-popup.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/slick/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/lib/countdown/jquery.countdown.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/jquery.ui.touch-punch.min.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/allinone_carousel.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/carousel.js') }}"></script>
	<script src="{{ Module::asset('landingpagepro:templates/lib/mixitup/jquery.mixitup.min.js') }}"></script>
	<script src="{{ Module::asset('landingpagepro:templates/lib/mixitup/mixitup.js') }}"></script>
	<script src="{{ Module::asset('landingpagepro:templates/lib/countup/jquery.counterup.min.js') }}"></script>
	<script src="{{ Module::asset('landingpagepro:templates/lib/countup/waypoints.min.js') }}"></script>
	<script src="{{ Module::asset('landingpagepro:templates/lib/countup/countup.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/js/main.js') }}"></script>
	<script type="text/javascript" src="{{ Module::asset('landingpagepro:templates/mail/form-triger.js') }}"></script>

</body>
