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
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_header1">Banner Header 1</label>
				<input type="text" name="banner_header1" value="{{ $content['banner_header1'] ? : '' }}" class="form-control" id="banner_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_header2">Banner Header 2</label>
				<input type="text" name="banner_header2" value="{{ $content['banner_header2'] ? : '' }}" class="form-control" id="banner_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_url">'Know More' Button URL</label>
				<input type="text" name="banner_url" value="{{ $content['banner_url'] ? : '' }}" class="form-control" id="banner_url" placeholder="Enter value">
			</div>
		</div>

		<!-- ABOUT BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_bg">About Us Section Image URL</label>
				<input type="text" name="about_bg" value="{{ $content['about_bg'] ? : '' }}" class="form-control" id="about_bg" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_header">About Us Heading</label>
				<input type="text" name="about_header" value="{{ $content['about_header'] ? : '' }}" class="form-control" id="about_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_content">About Us Description</label>
				<input type="text" name="about_content" value="{{ $content['about_content'] ? : '' }}" class="form-control" id="about_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_link">'Know More' Link'</label>
				<input type="text" name="about_link" value="{{ $content['about_link'] ? : '' }}" class="form-control" id="about_link" placeholder="Enter value">
			</div>
		</div>

		<!-- SPECIAL SERVICE BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_header">Special Service Heading</label>
				<input type="text" name="special_header" value="{{ $content['special_header'] ? : '' }}" class="form-control" id="special_header" placeholder="Enter value">
			</div>
		</div>
		<!-- SPECIAL ITEM 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header_image1">Special Item Header Image</label>
				<input type="text" name="special_item_header_image1" value="{{ $content['special_item_header_image1'] ? : '' }}" class="form-control" id="special_item_header_image1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_icon1">Special Item Icon</label>
				<input type="text" name="special_item_icon1" value="{{ $content['special_item_icon1'] ? : '' }}" class="form-control" id="special_item_icon1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header1">Special Item Heading</label>
				<input type="text" name="special_item_header1" value="{{ $content['special_item_header1'] ? : '' }}" class="form-control" id="special_item_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_content1">Special Service Description</label>
				<input type="text" name="special_item_content1" value="{{ $content['special_item_content1'] ? : '' }}" class="form-control" id="special_item_content1" placeholder="Enter value">
			</div>
		</div>
		<!-- SPECIAL ITEM 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header_image2">Special Item Header Image</label>
				<input type="text" name="special_item_header_image2" value="{{ $content['special_item_header_image2'] ? : '' }}" class="form-control" id="special_item_header_image2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_icon2">Special Item Icon</label>
				<input type="text" name="special_item_icon2" value="{{ $content['special_item_icon2'] ? : '' }}" class="form-control" id="special_item_icon2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header2">Special Item Heading</label>
				<input type="text" name="special_item_header2" value="{{ $content['special_item_header2'] ? : '' }}" class="form-control" id="special_item_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_content2">Special Service Description</label>
				<input type="text" name="special_item_content2" value="{{ $content['special_item_content2'] ? : '' }}" class="form-control" id="special_item_content2" placeholder="Enter value">
			</div>
		</div>
		<!-- SPECIAL ITEM 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header_image3">Special Item Header Image</label>
				<input type="text" name="special_item_header_image3" value="{{ $content['special_item_header_image3'] ? : '' }}" class="form-control" id="special_item_header_image3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_icon3">Special Item Icon</label>
				<input type="text" name="special_item_icon3" value="{{ $content['special_item_icon3'] ? : '' }}" class="form-control" id="special_item_icon3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_header3">Special Item Heading</label>
				<input type="text" name="special_item_header3" value="{{ $content['special_item_header3'] ? : '' }}" class="form-control" id="special_item_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="special_item_content3">Special Service Description</label>
				<input type="text" name="special_item_content3" value="{{ $content['special_item_content3'] ? : '' }}" class="form-control" id="special_item_content3" placeholder="Enter value">
			</div>
		</div>

		<!-- TEAM BLOCK -->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_header">Team Heading</label>
				<input type="text" name="team_header" value="{{ $content['team_header'] ? : '' }}" class="form-control" id="team_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_image">Team Member Image</label>
				<input type="text" name="team_image" value="{{ $content['team_image'] ? : '' }}" class="form-control" id="team_image" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_member_name">Team Member Name</label>
				<input type="text" name="team_member_name" value="{{ $content['team_member_name'] ? : '' }}" class="form-control" id="team_member_name" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_member_designation">Team Member Designation</label>
				<input type="text" name="team_member_designation" value="{{ $content['team_member_designation'] ? : '' }}" class="form-control" id="team_member_designation" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="team_member_about">About Team Member</label>
				<input type="text" name="team_member_about" value="{{ $content['team_member_about'] ? : '' }}" class="form-control" id="team_member_about" placeholder="Enter value">
			</div>
		</div>

		<!-- PRODUCT BLOCK 1-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_header1">Product Heading</label>
				<input type="text" name="product_header1" value="{{ $content['product_header1'] ? : '' }}" class="form-control" id="product_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_price1">Product Price</label>
				<input type="text" name="product_price1" value="{{ $content['product_price1'] ? : '' }}" class="form-control" id="product_price1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_content1">Product Description</label>
				<input type="text" name="product_content1" value="{{ $content['product_content1'] ? : '' }}" class="form-control" id="product_content1" placeholder="Enter value">
			</div>
		</div>
		<!-- PRODUCT BLOCK 2-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_header2">Product Heading</label>
				<input type="text" name="product_header2" value="{{ $content['product_header2'] ? : '' }}" class="form-control" id="product_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_price2">Product Price</label>
				<input type="text" name="product_price2" value="{{ $content['product_price2'] ? : '' }}" class="form-control" id="product_price2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_content2">Product Description</label>
				<input type="text" name="product_content2" value="{{ $content['product_content2'] ? : '' }}" class="form-control" id="product_content2" placeholder="Enter value">
			</div>
		</div>
		<!-- PRODUCT BLOCK 3-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_header3">Product Heading</label>
				<input type="text" name="product_header3" value="{{ $content['product_header3'] ? : '' }}" class="form-control" id="product_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_price3">Product Price</label>
				<input type="text" name="product_price3" value="{{ $content['product_price3'] ? : '' }}" class="form-control" id="product_price3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="product_content3">Product Description</label>
				<input type="text" name="product_content3" value="{{ $content['product_content3'] ? : '' }}" class="form-control" id="product_content3" placeholder="Enter value">
			</div>
		</div>

		<!-- LEAD FORM BLOCK-->
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form_title">Bottom Lead Form Title</label>
				<input type="text" name="lead_form_title" value="{{ $content['lead_form_title'] ? : '' }}" class="form-control" id="lead_form_title" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form_content">Bottom Lead Form Description</label>
				<input type="text" name="lead_form_content" value="{{ $content['lead_form_content'] ? : '' }}" class="form-control" id="lead_form_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form">Bottom Lead Form URL <small>(Leadform will be loaded into iframe)</small></label>
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
