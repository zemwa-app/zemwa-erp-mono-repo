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

		<!-- HEADER BLOCK-->
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
		<!-- HEADER LEAD FORM BLOCK-->
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
		<!-- SERVICE BLOCK -->
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

		<!-- VIDEO BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="video_url">Video URL (Vimeo only)</label>
				<input type="text" name="video_url" value="{{ $content['video_url'] ? : '' }}" class="form-control" id="video_url" placeholder="Enter value">
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

		<!-- PORTFOLIO BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="portfolio_heading">Portfolio Heading</label>
				<input type="text" name="portfolio_heading" value="{{ $content['portfolio_heading'] ? : '' }}" class="form-control" id="portfolio_heading" placeholder="Enter value">
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
				<label for="portfolio_content3">Portfolio Brief Description</label>
				<input type="text" name="portfolio_content3" value="{{ $content['portfolio_content3'] ? : '' }}" class="form-control" id="portfolio_content3" placeholder="Enter value">
			</div>
		</div>

		<!-- PARTNER SECTION 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_thumb1">Partner Thumbnail Image</label>
				<input type="text" name="partner_thumb1" value="{{ $content['partner_thumb1'] ? : '' }}" class="form-control" id="partner_thumb1" placeholder="Enter value">
			</div>
		</div>
		<!-- PARTNER SECTION 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_thumb2">Partner Thumbnail Image</label>
				<input type="text" name="partner_thumb2" value="{{ $content['partner_thumb2'] ? : '' }}" class="form-control" id="partner_thumb2" placeholder="Enter value">
			</div>
		</div>
		<!-- PARTNER SECTION 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_thumb3">Partner Thumbnail Image</label>
				<input type="text" name="partner_thumb3" value="{{ $content['partner_thumb3'] ? : '' }}" class="form-control" id="partner_thumb3" placeholder="Enter value">
			</div>
		</div>
		<!-- PARTNER SECTION 4-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_thumb4">Partner Thumbnail Image</label>
				<input type="text" name="partner_thumb4" value="{{ $content['partner_thumb4'] ? : '' }}" class="form-control" id="partner_thumb4" placeholder="Enter value">
			</div>
		</div>
		<!-- PARTNER SECTION 5-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_thumb5">Partner Thumbnail Image</label>
				<input type="text" name="partner_thumb5" value="{{ $content['partner_thumb5'] ? : '' }}" class="form-control" id="partner_thumb5" placeholder="Enter value">
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
