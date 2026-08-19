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
				<label for="banner_header_image">Banner Header Image URL</label>
				<input type="text" name="banner_header_image" value="{{ $content['banner_header_image'] ? : '' }}" class="form-control" id="banner_header_image" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_header1">Banner Header 1</label>
				<input type="text" name="banner_header1" value="{{ $content['banner_header1'] ? : '' }}" class="form-control" id="page_logo" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_header2">Banner Header 2</label>
				<input type="text" name="banner_header2" value="{{ $content['banner_header2'] ? : '' }}" class="form-control" id="page_logo" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_content">Banner Content</label>
				<input type="text" name="banner_content" value="{{ $content['banner_content'] ? : '' }}" class="form-control" id="page_logo" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_image1">Service Image 1</label>
				<input type="text" name="service_image1" value="{{ $content['service_image1'] ? : '' }}" class="form-control" id="service_image1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_header1">Service Header 1</label>
				<input type="text" name="service_header1" value="{{ $content['service_header1'] ? : '' }}" class="form-control" id="service_header1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_content1">Service Content 1</label>
				<input type="text" name="service_content1" value="{{ $content['service_content1'] ? : '' }}" class="form-control" id="service_content1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_image2">Service Image 2</label>
				<input type="text" name="service_image2" value="{{ $content['service_image2'] ? : '' }}" class="form-control" id="service_image2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_header2">Service Header 2</label>
				<input type="text" name="service_header2" value="{{ $content['service_header2'] ? : '' }}" class="form-control" id="service_header2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_content2">Service Content 2</label>
				<input type="text" name="service_content2" value="{{ $content['service_content2'] ? : '' }}" class="form-control" id="service_content2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_image3">Service Image 3</label>
				<input type="text" name="service_image3" value="{{ $content['service_image3'] ? : '' }}" class="form-control" id="service_image3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_header3">Service Header 3</label>
				<input type="text" name="service_header3" value="{{ $content['service_header3'] ? : '' }}" class="form-control" id="service_header3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="service_content3">Service Content 3</label>
				<input type="text" name="service_content3" value="{{ $content['service_content3'] ? : '' }}" class="form-control" id="service_content3" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_image">About Us Banner</label>
				<input type="text" name="about_image" value="{{ $content['about_image'] ? : '' }}" class="form-control" id="about_image" placeholder="Enter value">
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
				<label for="about_content">About Us Content</label>
				<input type="text" name="about_content" value="{{ $content['about_content'] ? : '' }}" class="form-control" id="about_content" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="gallery_image1">Gallery Image Link</label>
				<input type="text" name="gallery_image1" value="{{ $content['gallery_image1'] ? : '' }}" class="form-control" id="gallery_image1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="gallery_image2">Gallery Image Link</label>
				<input type="text" name="gallery_image2" value="{{ $content['gallery_image2'] ? : '' }}" class="form-control" id="gallery_image2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="gallery_image3">Gallery Image Link</label>
				<input type="text" name="gallery_image3" value="{{ $content['gallery_image3'] ? : '' }}" class="form-control" id="gallery_image3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="gallery_image4">Gallery Image Link</label>
				<input type="text" name="gallery_image4" value="{{ $content['gallery_image4'] ? : '' }}" class="form-control" id="gallery_image4" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_header_image">CTA Header Image URL</label>
				<input type="text" name="callout_header_image" value="{{ $content['callout_header_image'] ? : '' }}" class="form-control" id="callout_header_image" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_header">CTA Header</label>
				<input type="text" name="callout_header" value="{{ $content['callout_header'] ? : '' }}" class="form-control" id="callout_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="callout_content">CTA Content</label>
				<input type="text" name="callout_content" value="{{ $content['callout_content'] ? : '' }}" class="form-control" id="callout_content" placeholder="Enter value">
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
				<label for="partner_header">Partner Header</label>
				<input type="text" name="partner_header" value="{{ $content['partner_header'] ? : '' }}" class="form-control" id="partner_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_image1">Partner Image</label>
				<input type="text" name="partner_image1" value="{{ $content['partner_image1'] ? : '' }}" class="form-control" id="partner_image1" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_image2">Partner Image</label>
				<input type="text" name="partner_image2" value="{{ $content['partner_image2'] ? : '' }}" class="form-control" id="partner_image2" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_image3">Partner Image</label>
				<input type="text" name="partner_image3" value="{{ $content['partner_image3'] ? : '' }}" class="form-control" id="partner_image3" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="partner_image4">Partner Image</label>
				<input type="text" name="partner_image4" value="{{ $content['partner_image4'] ? : '' }}" class="form-control" id="partner_image4" placeholder="Enter value">
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form_header">Lead Form Header</label>
				<input type="text" name="lead_form_header" value="{{ $content['lead_form_header'] ?: '' }}" class="form-control" id="lead_form_header" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form_content">Lead Form Content</label>
				<input type="text" name="lead_form_content" value="{{ $content['lead_form_content'] ?: '' }}" class="form-control" id="lead_form_content" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form">Lead Form URL <small>(Leadform will be loaded into iframe)</small></label>
				<input type="text" name="lead_form" value="{{ $content['lead_form'] ? : '' }}" class="form-control" id="lead_form" placeholder="Lead Form Code">
			</div>
		</div>

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
