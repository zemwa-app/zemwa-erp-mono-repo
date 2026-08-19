<style>
	#lpTemplate label{
		font-weight: bold;
	}
	#lpTemplate input[type="text"],#lpTemplate textarea {
		border: none;
		border-bottom: 2px solid lightblue;
		outline: none;
	}
	#lpTemplate input[type="text"]:focus, #lpTemplate textarea:focus {
		border-bottom-width: 2px;
		border-bottom-color: black;
	}
</style>
<div class="container py-3">
	<form id="lpTemplate">
		@csrf
		<div class="form-row">
			<div class="form-group col-sm-12">
				<input type="hidden" name="uid" value="{{ $id }}" id="lpId">
				<label for="page_name">Landing Page Name</label>
				<input type="text" name="page_name" value="{{ $content['page_name'] ? : '' }}" class="form-control" id="page_name" placeholder="Enter landing page name">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="page_category">Landing Page Category</label>
				<select class="form-control" name="page_category" id="page_category">
					<option value="" disabled selected>Select Category</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}" @if($selectedCategory == $category->id) selected @endif>{{ $category->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="page_status">Publishing Status</label>
				<select class="form-control" name="page_status" id="page_status">
					<option value="2" @if($selectedStatus == 2) selected @endif>Draft</option>
					<option value="1" @if($selectedStatus == 1) selected @endif>Active</option>
					<option value="0" @if($selectedStatus == 0) selected @endif>Deactive</option>
				</select>
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="page_title">Page Title</label>
				<input type="text" name="page_title" value="{{ $content['page_title'] ? : '' }}" class="form-control" id="page_title" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="page_logo">Logo URL</label>
				<input type="text" name="page_logo" value="{{ $content['page_logo'] ? : '' }}" class="form-control" id="page_logo" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_header_image">Header Background Image URL</label>
				<input type="text" name="banner_header_image" value="{{ $content['banner_header_image'] ? : '' }}" class="form-control" id="banner_header_image" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner1_header_image">Banner 1 Image URL</label>
				<input type="text" name="banner1_header_image" value="{{ $content['banner1_header_image'] ? : '' }}" class="form-control" id="banner1_header_image" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner1_header1">Banner Header 1</label>
				<input type="text" name="banner1_header1" value="{{ $content['banner1_header1'] ? : '' }}" class="form-control" id="banner1_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner1_header2">Banner Header 2</label>
				<input type="text" name="banner1_header2" value="{{ $content['banner1_header2'] ? : '' }}" class="form-control" id="banner1_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner1_url">'Know More' Button URL</label>
				<input type="text" name="banner1_url" value="{{ $content['banner1_url'] ? : '' }}" class="form-control" id="banner1_url" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner2_header_image">Banner 2 Image URL</label>
				<input type="text" name="banner2_header_image" value="{{ $content['banner2_header_image'] ? : '' }}" class="form-control" id="banner2_header_image" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner2_header1">Banner Header 1</label>
				<input type="text" name="banner2_header1" value="{{ $content['banner2_header1'] ? : '' }}" class="form-control" id="banner2_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner2_header2">Banner Header 2</label>
				<input type="text" name="banner2_header2" value="{{ $content['banner2_header2'] ? : '' }}" class="form-control" id="banner2_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner2_content">Banner Content</label>
				<input type="text" name="banner2_content" value="{{ $content['banner2_content'] ? : '' }}" class="form-control" id="banner2_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner2_url">'Know More' Button URL</label>
				<input type="text" name="banner2_url" value="{{ $content['banner2_url'] ? : '' }}" class="form-control" id="banner2_url" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_header">About Us Header</label>
				<input type="text" name="about_header" value="{{ $content['about_header'] ? : '' }}" class="form-control" id="about_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_subtext">About Us Sub Text</label>
				<input type="text" name="about_subtext" value="{{ $content['about_subtext'] ? : '' }}" class="form-control" id="about_subtext" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_content">About Us Content</label>
				<input type="text" name="about_content" value="{{ $content['about_content'] ? : '' }}" class="form-control" id="about_content" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon1">About Us - List Icon</label>
				<input type="text" name="about_list_icon1" value="{{ $content['about_list_icon1'] ? : '' }}" class="form-control" id="about_list_icon1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header1">About Us - List Heading</label>
				<input type="text" name="about_list_header1" value="{{ $content['about_list_header1'] ? : '' }}" class="form-control" id="about_list_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content1">About Us - List Content</label>
				<input type="text" name="about_list_content1" value="{{ $content['about_list_content1'] ? : '' }}" class="form-control" id="about_list_content1" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon2">About Us - List Icon</label>
				<input type="text" name="about_list_icon2" value="{{ $content['about_list_icon2'] ? : '' }}" class="form-control" id="about_list_icon2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header2">About Us - List Heading</label>
				<input type="text" name="about_list_header2" value="{{ $content['about_list_header2'] ? : '' }}" class="form-control" id="about_list_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content2">About Us - List Content</label>
				<input type="text" name="about_list_content2" value="{{ $content['about_list_content2'] ? : '' }}" class="form-control" id="about_list_content2" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon3">About Us - List Icon</label>
				<input type="text" name="about_list_icon3" value="{{ $content['about_list_icon3'] ? : '' }}" class="form-control" id="about_list_icon3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header3">About Us - List Heading</label>
				<input type="text" name="about_list_header3" value="{{ $content['about_list_header3'] ? : '' }}" class="form-control" id="about_list_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content3">About Us - List Content</label>
				<input type="text" name="about_list_content3" value="{{ $content['about_list_content3'] ? : '' }}" class="form-control" id="about_list_content3" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon4">About Us - List Icon</label>
				<input type="text" name="about_list_icon4" value="{{ $content['about_list_icon4'] ? : '' }}" class="form-control" id="about_list_icon4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header4">About Us - List Heading</label>
				<input type="text" name="about_list_header4" value="{{ $content['about_list_header4'] ? : '' }}" class="form-control" id="about_list_header4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content4">About Us - List Content</label>
				<input type="text" name="about_list_content4" value="{{ $content['about_list_content4'] ? : '' }}" class="form-control" id="about_list_content4" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 5-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon5">About Us - List Icon</label>
				<input type="text" name="about_list_icon5" value="{{ $content['about_list_icon5'] ? : '' }}" class="form-control" id="about_list_icon5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header5">About Us - List Heading</label>
				<input type="text" name="about_list_header5" value="{{ $content['about_list_header5'] ? : '' }}" class="form-control" id="about_list_header5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content5">About Us - List Content</label>
				<input type="text" name="about_list_content5" value="{{ $content['about_list_content5'] ? : '' }}" class="form-control" id="about_list_content5" placeholder="Enter value">
			</div>
		</div>
		<!-- ABOUT LIST 6-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_icon6">About Us - List Icon</label>
				<input type="text" name="about_list_icon6" value="{{ $content['about_list_icon6'] ? : '' }}" class="form-control" id="about_list_icon6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_header6">About Us - List Heading</label>
				<input type="text" name="about_list_header6" value="{{ $content['about_list_header6'] ? : '' }}" class="form-control" id="about_list_header6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_list_content6">About Us - List Content</label>
				<input type="text" name="about_list_content6" value="{{ $content['about_list_content6'] ? : '' }}" class="form-control" id="about_list_content6" placeholder="Enter value">
			</div>
		</div>

		<!-- CALLOUT SECTION -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_header1">CTA Main Header</label>
				<input type="text" name="callout_header1" value="{{ $content['callout_header1'] ? : '' }}" class="form-control" id="callout_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_header2">CTA Sub Header</label>
				<input type="text" name="callout_header2" value="{{ $content['callout_header2'] ? : '' }}" class="form-control" id="callout_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_url">CTA URL</label>
				<input type="text" name="callout_url" value="{{ $content['callout_url'] ? : '' }}" class="form-control" id="callout_url" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_btn_text">CTA Button Text</label>
				<input type="text" name="callout_btn_text" value="{{ $content['callout_btn_text'] ? : '' }}" class="form-control" id="callout_btn_text" placeholder="Enter value">
			</div>
		</div>

		<!-- TEAM SECTION -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_header">Team Header</label>
				<input type="text" name="team_header" value="{{ $content['team_header'] ? : '' }}" class="form-control" id="team_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_content">Team Content</label>
				<input type="text" name="team_content" value="{{ $content['team_content'] ? : '' }}" class="form-control" id="team_content" placeholder="Enter value">
			</div>
		</div>
		<!-- TEAM BLOCK 1 -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_image1">Team Image</label>
				<input type="text" name="team_image1" value="{{ $content['team_image1'] ? : '' }}" class="form-control" id="team_image1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_name1">Team Member Name</label>
				<input type="text" name="team_name1" value="{{ $content['team_name1'] ? : '' }}" class="form-control" id="team_name1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_designation1">Team Member Designation</label>
				<input type="text" name="team_designation1" value="{{ $content['team_designation1'] ? : '' }}" class="form-control" id="team_designation1" placeholder="Enter value">
			</div>
		</div>
		<!-- TEAM BLOCK 2 -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_image2">Team Image</label>
				<input type="text" name="team_image2" value="{{ $content['team_image2'] ? : '' }}" class="form-control" id="team_image2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_name2">Team Member Name</label>
				<input type="text" name="team_name2" value="{{ $content['team_name2'] ? : '' }}" class="form-control" id="team_name2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_designation2">Team Member Designation</label>
				<input type="text" name="team_designation2" value="{{ $content['team_designation2'] ? : '' }}" class="form-control" id="team_designation2" placeholder="Enter value">
			</div>
		</div>
		<!-- TEAM BLOCK 3 -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_image3">Team Image</label>
				<input type="text" name="team_image3" value="{{ $content['team_image3'] ? : '' }}" class="form-control" id="team_image3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_name3">Team Member Name</label>
				<input type="text" name="team_name3" value="{{ $content['team_name3'] ? : '' }}" class="form-control" id="team_name3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_designation3">Team Member Designation</label>
				<input type="text" name="team_designation3" value="{{ $content['team_designation3'] ? : '' }}" class="form-control" id="team_designation3" placeholder="Enter value">
			</div>
		</div>
		<!-- TEAM BLOCK 4 -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_image4">Team Image</label>
				<input type="text" name="team_image4" value="{{ $content['team_image4'] ? : '' }}" class="form-control" id="team_image4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_name4">Team Member Name</label>
				<input type="text" name="team_name4" value="{{ $content['team_name4'] ? : '' }}" class="form-control" id="team_name4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_designation4">Team Member Designation</label>
				<input type="text" name="team_designation4" value="{{ $content['team_designation4'] ? : '' }}" class="form-control" id="team_designation4" placeholder="Enter value">
			</div>
		</div>

		<!-- SKILL BLOCK -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_header">Skill Header</label>
				<input type="text" name="skill_header" value="{{ $content['skill_header'] ? : '' }}" class="form-control" id="skill_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_content">Skill Content</label>
				<input type="text" name="skill_content" value="{{ $content['skill_content'] ? : '' }}" class="form-control" id="skill_content" placeholder="Enter value">
			</div>
		</div>
		<!-- SKILL PROGRESS BLOCK 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_progress_header1">Skill Area</label>
				<input type="text" name="skill_progress_header1" value="{{ $content['skill_progress_header1'] ? : '' }}" class="form-control" id="skill_progress_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_percentage1">Skill Percentage</label>
				<input type="text" name="skill_percentage1" value="{{ $content['skill_percentage1'] ? : '' }}" class="form-control" id="skill_percentage1" placeholder="Enter value">
			</div>
		</div>
		<!-- SKILL PROGRESS BLOCK 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_progress_header2">Skill Area</label>
				<input type="text" name="skill_progress_header2" value="{{ $content['skill_progress_header2'] ? : '' }}" class="form-control" id="skill_progress_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_percentage2">Skill Percentage</label>
				<input type="text" name="skill_percentage2" value="{{ $content['skill_percentage2'] ? : '' }}" class="form-control" id="skill_percentage2" placeholder="Enter value">
			</div>
		</div>
		<!-- SKILL PROGRESS BLOCK 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_progress_header3">Skill Area</label>
				<input type="text" name="skill_progress_header3" value="{{ $content['skill_progress_header3'] ? : '' }}" class="form-control" id="skill_progress_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_percentage">Skill Percentage</label>
				<input type="text" name="skill_percentage3" value="{{ $content['skill_percentage3'] ? : '' }}" class="form-control" id="skill_percentage3" placeholder="Enter value">
			</div>
		</div>
		<!-- SKILL PROGRESS BLOCK 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_progress_header4">Skill Area</label>
				<input type="text" name="skill_progress_header4" value="{{ $content['skill_progress_header4'] ? : '' }}" class="form-control" id="skill_progress_header4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="skill_percentage4">Skill Percentage</label>
				<input type="text" name="skill_percentage4" value="{{ $content['skill_percentage4'] ? : '' }}" class="form-control" id="skill_percentage4" placeholder="Enter value">
			</div>
		</div>

		<!-- VIDEO BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="video_url">Video URL (Vimeo only)</label>
				<input type="text" name="video_url" value="{{ $content['video_url'] ? : '' }}" class="form-control" id="video_url" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="video_title_head">Video Header</label>
				<input type="text" name="video_title_head" value="{{ $content['video_title_head'] ? : '' }}" class="form-control" id="video_title_head" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="video_title_content">Video Description</label>
				<input type="text" name="video_title_content" value="{{ $content['video_title_content'] ? : '' }}" class="form-control" id="video_title_content" placeholder="Enter value">
			</div>
		</div>

		<!-- PORTFOLIO BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_header">Portfolio Heading</label>
				<input type="text" name="portfolio_header" value="{{ $content['portfolio_header'] ? : '' }}" class="form-control" id="portfolio_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content">Portfolio Description</label>
				<input type="text" name="portfolio_content" value="{{ $content['portfolio_content'] ? : '' }}" class="form-control" id="portfolio_content" placeholder="Enter value">
			</div>
		</div>

		<!-- PORTFOLIO SECTION 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb1">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb1" value="{{ $content['portfolio_thumb1'] ? : '' }}" class="form-control" id="portfolio_thumb1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title1">Portfolio Title</label>
				<input type="text" name="portfolio_title1" value="{{ $content['portfolio_title1'] ? : '' }}" class="form-control" id="portfolio_title1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url1">Portfolio URL</label>
				<input type="text" name="portfolio_url1" value="{{ $content['portfolio_url1'] ? : '' }}" class="form-control" id="portfolio_url1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title1">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title1" value="{{ $content['portfolio_sub_title1'] ? : '' }}" class="form-control" id="portfolio_sub_title1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content1">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content1" value="{{ $content['portfolio_content1'] ? : '' }}" class="form-control" id="portfolio_content1" placeholder="Enter value">
			</div>
		</div>
		<!-- PORTFOLIO SECTION 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb2">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb2" value="{{ $content['portfolio_thumb2'] ? : '' }}" class="form-control" id="portfolio_thumb2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title2">Portfolio Title</label>
				<input type="text" name="portfolio_title2" value="{{ $content['portfolio_title2'] ? : '' }}" class="form-control" id="portfolio_title2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url2">Portfolio URL</label>
				<input type="text" name="portfolio_url2" value="{{ $content['portfolio_url2'] ? : '' }}" class="form-control" id="portfolio_url2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title2">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title2" value="{{ $content['portfolio_sub_title2'] ? : '' }}" class="form-control" id="portfolio_sub_title2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content2">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content2" value="{{ $content['portfolio_content2'] ? : '' }}" class="form-control" id="portfolio_content2" placeholder="Enter value">
			</div>
		</div>
		<!-- PORTFOLIO SECTION 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb3">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb3" value="{{ $content['portfolio_thumb3'] ? : '' }}" class="form-control" id="portfolio_thumb3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title3">Portfolio Title</label>
				<input type="text" name="portfolio_title3" value="{{ $content['portfolio_title3'] ? : '' }}" class="form-control" id="portfolio_title3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url3">Portfolio URL</label>
				<input type="text" name="portfolio_url3" value="{{ $content['portfolio_url3'] ? : '' }}" class="form-control" id="portfolio_url3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title3">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title3" value="{{ $content['portfolio_sub_title3'] ? : '' }}" class="form-control" id="portfolio_sub_title3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content3">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content3" value="{{ $content['portfolio_content3'] ? : '' }}" class="form-control" id="portfolio_content3" placeholder="Enter value">
			</div>
		</div>
		<!-- PORTFOLIO SECTION 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb4">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb4" value="{{ $content['portfolio_thumb4'] ? : '' }}" class="form-control" id="portfolio_thumb4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title4">Portfolio Title</label>
				<input type="text" name="portfolio_title4" value="{{ $content['portfolio_title4'] ? : '' }}" class="form-control" id="portfolio_title4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url4">Portfolio URL</label>
				<input type="text" name="portfolio_url4" value="{{ $content['portfolio_url4'] ? : '' }}" class="form-control" id="portfolio_url4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title4">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title4" value="{{ $content['portfolio_sub_title4'] ? : '' }}" class="form-control" id="portfolio_sub_title4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content4">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content4" value="{{ $content['portfolio_content4'] ? : '' }}" class="form-control" id="portfolio_content4" placeholder="Enter value">
			</div>
		</div>
		<!-- PORTFOLIO SECTION 5-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb5">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb5" value="{{ $content['portfolio_thumb5'] ? : '' }}" class="form-control" id="portfolio_thumb5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title5">Portfolio Title</label>
				<input type="text" name="portfolio_title5" value="{{ $content['portfolio_title5'] ? : '' }}" class="form-control" id="portfolio_title5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url5">Portfolio URL</label>
				<input type="text" name="portfolio_url5" value="{{ $content['portfolio_url5'] ? : '' }}" class="form-control" id="portfolio_url5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title5">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title5" value="{{ $content['portfolio_sub_title5'] ? : '' }}" class="form-control" id="portfolio_sub_title5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content5">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content5" value="{{ $content['portfolio_content5'] ? : '' }}" class="form-control" id="portfolio_content5" placeholder="Enter value">
			</div>
		</div>
		<!-- PORTFOLIO SECTION 6-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_thumb6">Portfolio Thumbnail Image</label>
				<input type="text" name="portfolio_thumb6" value="{{ $content['portfolio_thumb6'] ? : '' }}" class="form-control" id="portfolio_thumb6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_title6">Portfolio Title</label>
				<input type="text" name="portfolio_title6" value="{{ $content['portfolio_title6'] ? : '' }}" class="form-control" id="portfolio_title6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_url6">Portfolio URL</label>
				<input type="text" name="portfolio_url6" value="{{ $content['portfolio_url6'] ? : '' }}" class="form-control" id="portfolio_url6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_sub_title6">Portfolio Sub Title</label>
				<input type="text" name="portfolio_sub_title6" value="{{ $content['portfolio_sub_title6'] ? : '' }}" class="form-control" id="portfolio_sub_title6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_content6">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content6" value="{{ $content['portfolio_content6'] ? : '' }}" class="form-control" id="portfolio_content6" placeholder="Enter value">
			</div>
		</div>

		<!-- COUNTUP BLOCK 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_number1">Countup Number</label>
				<input type="text" name="countup_number1" value="{{ $content['countup_number1'] ? : '' }}" class="form-control" id="countup_number1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_text1">Countup Text</label>
				<input type="text" name="countup_text1" value="{{ $content['countup_text1'] ? : '' }}" class="form-control" id="countup_text1" placeholder="Enter value">
			</div>
		</div>
		<!-- COUNTUP BLOCK 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_number2">Countup Number</label>
				<input type="text" name="countup_number2" value="{{ $content['countup_number2'] ? : '' }}" class="form-control" id="countup_number2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_text2">Countup Text</label>
				<input type="text" name="countup_text2" value="{{ $content['countup_text2'] ? : '' }}" class="form-control" id="countup_text2" placeholder="Enter value">
			</div>
		</div>
		<!-- COUNTUP BLOCK 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_number3">Countup Number</label>
				<input type="text" name="countup_number3" value="{{ $content['countup_number3'] ? : '' }}" class="form-control" id="countup_number3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_text3">Countup Text</label>
				<input type="text" name="countup_text3" value="{{ $content['countup_text3'] ? : '' }}" class="form-control" id="countup_text3" placeholder="Enter value">
			</div>
		</div>
		<!-- COUNTUP BLOCK 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_number4">Countup Number</label>
				<input type="text" name="countup_number4" value="{{ $content['countup_number4'] ? : '' }}" class="form-control" id="countup_number4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="countup_text4">Countup Text</label>
				<input type="text" name="countup_text4" value="{{ $content['countup_text4'] ? : '' }}" class="form-control" id="countup_text4" placeholder="Enter value">
			</div>
		</div>

		<!-- SERVICE BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_header">Service Heading</label>
				<input type="text" name="service_header" value="{{ $content['service_header'] ? : '' }}" class="form-control" id="service_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_content">Service Description</label>
				<input type="text" name="service_content" value="{{ $content['service_content'] ? : '' }}" class="form-control" id="service_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_section_image">Service Section Image</label>
				<input type="text" name="service_section_image" value="{{ $content['service_section_image'] ? : '' }}" class="form-control" id="service_section_image" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header1">Service Item Heading</label>
				<input type="text" name="service_item_header1" value="{{ $content['service_item_header1'] ? : '' }}" class="form-control" id="service_item_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon1">Service Icon</label>
				<input type="text" name="service_icon1" value="{{ $content['service_icon1'] ? : '' }}" class="form-control" id="service_icon1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text1">Service Item Description</label>
				<input type="text" name="service_item_text1" value="{{ $content['service_item_text1'] ? : '' }}" class="form-control" id="service_item_text1" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header2">Service Item Heading</label>
				<input type="text" name="service_item_header2" value="{{ $content['service_item_header2'] ? : '' }}" class="form-control" id="service_item_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon2">Service Icon</label>
				<input type="text" name="service_icon2" value="{{ $content['service_icon2'] ? : '' }}" class="form-control" id="service_icon2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text2">Service Item Description</label>
				<input type="text" name="service_item_text2" value="{{ $content['service_item_text2'] ? : '' }}" class="form-control" id="service_item_text2" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header3">Service Item Heading</label>
				<input type="text" name="service_item_header3" value="{{ $content['service_item_header3'] ? : '' }}" class="form-control" id="service_item_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon3">Service Icon</label>
				<input type="text" name="service_icon3" value="{{ $content['service_icon3'] ? : '' }}" class="form-control" id="service_icon3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text3">Service Item Description</label>
				<input type="text" name="service_item_text3" value="{{ $content['service_item_text3'] ? : '' }}" class="form-control" id="service_item_text3" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header4">Service Item Heading</label>
				<input type="text" name="service_item_header4" value="{{ $content['service_item_header4'] ? : '' }}" class="form-control" id="service_item_header4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon4">Service Icon</label>
				<input type="text" name="service_icon4" value="{{ $content['service_icon4'] ? : '' }}" class="form-control" id="service_icon4" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text4">Service Item Description</label>
				<input type="text" name="service_item_text4" value="{{ $content['service_item_text4'] ? : '' }}" class="form-control" id="service_item_text4" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 5-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header5">Service Item Heading</label>
				<input type="text" name="service_item_header5" value="{{ $content['service_item_header5'] ? : '' }}" class="form-control" id="service_item_header5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon5">Service Icon</label>
				<input type="text" name="service_icon5" value="{{ $content['service_icon5'] ? : '' }}" class="form-control" id="service_icon5" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text5">Service Item Description</label>
				<input type="text" name="service_item_text5" value="{{ $content['service_item_text5'] ? : '' }}" class="form-control" id="service_item_text5" placeholder="Enter value">
			</div>
		</div>
		<!-- SERVICE SECTION 6-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_header6">Service Item Heading</label>
				<input type="text" name="service_item_header6" value="{{ $content['service_item_header6'] ? : '' }}" class="form-control" id="service_item_header6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_icon6">Service Icon</label>
				<input type="text" name="service_icon6" value="{{ $content['service_icon6'] ? : '' }}" class="form-control" id="service_icon6" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_item_text6">Service Item Description</label>
				<input type="text" name="service_item_text6" value="{{ $content['service_item_text6'] ? : '' }}" class="form-control" id="service_item_text6" placeholder="Enter value">
			</div>
		</div>

		<!-- LEADFORM BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_header">Lead Form Heading</label>
				<input type="text" name="lead_header" value="{{ $content['lead_header'] ? : '' }}" class="form-control" id="lead_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_content">Lead Form Description</label>
				<input type="text" name="lead_content" value="{{ $content['lead_content'] ? : '' }}" class="form-control" id="lead_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_address">Your Address</label>
				<input type="text" name="lead_address" value="{{ $content['lead_address'] ? : '' }}" class="form-control" id="lead_address" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_number">Your Contact Number</label>
				<input type="text" name="lead_number" value="{{ $content['lead_number'] ? : '' }}" class="form-control" id="lead_number" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_email">Your Email</label>
				<input type="text" name="lead_email" value="{{ $content['lead_email'] ? : '' }}" class="form-control" id="lead_email" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form_title">Lead Form Title</label>
				<input type="text" name="lead_form_title" value="{{ $content['lead_form_title'] ? : '' }}" class="form-control" id="lead_form_title" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form">Lead Form URL <small>(Leadform will be loaded into iframe)</small></label>
				<input type="text" name="lead_form" value="{{ $content['lead_form'] ? : '' }}" class="form-control" id="lead_form" placeholder="Enter value">
			</div>
		</div>

		<!-- SOCIAL MEDIA BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Linkedin Link</label>
				<input type="text" name="linkedin_url" value="{{ $content['linkedin_url'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">X(Twitter) Link</label>
				<input type="text" name="twitter_url" value="{{ $content['twitter_url'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Facebook Link</label>
				<input type="text" name="fb_url" value="{{ $content['fb_url'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
	</form>
</div>
