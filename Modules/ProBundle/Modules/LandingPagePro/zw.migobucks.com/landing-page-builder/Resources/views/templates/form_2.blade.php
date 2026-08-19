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
				<label for="">Landing Page Name</label>
				<input type="text" name="page_name" value="{{ $content['page_name'] ?: '' }}" class="form-control" id="" placeholder="Enter landing page name">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Landing Page Category</label>
				<select class="form-control" name="page_category" id="exampleSelect">
					<option value="" disabled selected>Select Category</option>
					@foreach($categories as $category)
						<option value="{{ $category->id }}" @if($selectedCategory == $category->id) selected @endif>{{ $category->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Publishing Status</label>
				<select class="form-control" name="page_status" id="exampleSelect">
					<option value="2" @if($selectedStatus == 2) selected @endif>Draft</option>
					<option value="1" @if($selectedStatus == 1) selected @endif>Active</option>
					<option value="0" @if($selectedStatus == 0) selected @endif>Deactive</option>
				</select>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="page_title">Page Title</label>
				<input type="text" name="page_title" value="{{ $content['page_title'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_h1">Banner Header</label>
				<input type="text" name="banner_h1" value="{{ $content['banner_h1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_text">Banner Text</label>
				<input type="text" name="banner_text" value="{{ $content['banner_text'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_url">'Learn More' URL</label>
				<input type="text" name="banner_url" value="{{ $content['banner_url'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_app1">Banner Image 1</label>
				<input type="text" name="banner_app1" value="{{ $content['banner_app1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="banner_app2">Banner Image 2</label>
				<input type="text" name="banner_app2" value="{{ $content['banner_app2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="app_logo"> App Logo</label>
				<input type="text" name="app_logo" value="{{ $content['app_logo'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_title">About Title</label>
				<input type="text" name="about_title" value="{{ $content['about_title'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_content">About Content</label>
				<input type="text" name="about_content" value="{{ $content['about_content'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="about_image">About Image</label>
				<input type="text" name="about_image" value="{{ $content['about_image'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for=""> Feature Image 1</label>
				<input type="text" name="feature_image1" value="{{ $content['feature_image1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for=""> Feature Image 2</label>
				<input type="text" name="feature_image2" value="{{ $content['feature_image2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature Title Text</label>
				<input type="text" name="feature_title_text" value="{{ $content['feature_title_text'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature Title Header</label>
				<input type="text" name="feature_title_header" value="{{ $content['feature_title_header'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature Content</label>
				<input type="text" name="feature_content" value="{{ $content['feature_content'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature List 1</label>
				<input type="text" name="feature_list1" value="{{ $content['feature_list1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature List 2</label>
				<input type="text" name="feature_list2" value="{{ $content['feature_list2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature List 3</label>
				<input type="text" name="feature_list3" value="{{ $content['feature_list3'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Feature List 4</label>
				<input type="text" name="feature_list4" value="{{ $content['feature_list4'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Title</label>
				<input type="text" name="download_title" value="{{ $content['download_title'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Header 1</label>
				<input type="text" name="download_header1" value="{{ $content['download_header1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Header 2</label>
				<input type="text" name="download_header2" value="{{ $content['download_header2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Content</label>
				<input type="text" name="download_content" value="{{ $content['download_content'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Playstore Link</label>
				<input type="text" name="download_link1" value="{{ $content['download_link1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download App-Store Link</label>
				<input type="text" name="download_link2" value="{{ $content['download_link2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Banner 1</label>
				<input type="text" name="download_banner1" value="{{ $content['download_banner1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">App Download Banner 2</label>
				<input type="text" name="download_banner2" value="{{ $content['download_banner2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for=""> Carousel Title</label>
				<input type="text" name="carousel_title" value="{{ $content['carousel_title'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Header</label>
				<input type="text" name="carousel_header" value="{{ $content['carousel_header'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Image 1</label>
				<input type="text" name="carousel_banner1" value="{{ $content['carousel_banner1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Image 2</label>
				<input type="text" name="carousel_banner2" value="{{ $content['carousel_banner2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Image 3</label>
				<input type="text" name="carousel_banner3" value="{{ $content['carousel_banner3'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Image 4</label>
				<input type="text" name="carousel_banner4" value="{{ $content['carousel_banner4'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Carousel Image 5</label>
				<input type="text" name="carousel_banner5" value="{{ $content['carousel_banner5'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Partner Image 1</label>
				<input type="text" name="partner_image1" value="{{ $content['partner_image1'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Partner Image 2</label>
				<input type="text" name="partner_image2" value="{{ $content['partner_image2'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Partner Image 3</label>
				<input type="text" name="partner_image3" value="{{ $content['partner_image3'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Partner Image 4</label>
				<input type="text" name="partner_image4" value="{{ $content['partner_image4'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Lead Form Header</label>
				<input type="text" name="lead_form_header" value="{{ $content['lead_form_header'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="">Lead Form Content</label>
				<input type="text" name="lead_form_content" value="{{ $content['lead_form_content'] ?: '' }}" class="form-control" id="" placeholder="Enter value">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-sm-12">
				<label for="lead_form">Lead Form Code</label>
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
