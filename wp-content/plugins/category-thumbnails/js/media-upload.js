/* category-thumbnails */
var category_thumbnail_window	= null;
var category_thumbnail_data		= window.category_thumbnail_data = window.category_thumbnail_data || {}
var category_thumbnail_clear	= function() { /* Do Nothing */ };

jQuery(function($) {
	// Get Thumbnail
	var settings			= $("#category-thumbnail").val();
	if(typeof(settings) != 'undefined' && settings != "" && settings != "none") {
		settings = Base64.decode(settings);
		category_thumbnail_data = JSON.parse(settings);
		$("#preview-thumbnail a.pct_image").css("background-image", 'url(' + category_thumbnail_data.url + ')');
	}
	
	// Set Thumbnail
	$('#set-category-thumbnail, #reset-category-thumbnail').click(function(event) {
		if(category_thumbnail_window) {
			category_thumbnail_window.open();
			return;
		}

		category_thumbnail_window = wp.media.frames.category_thumbnail_window = wp.media({
			title:		$("input[name=\"lang_category-thumbnails_title\"]").val(),
			button:		{
				text:	$("input[name=\"lang_category-thumbnails_button\"]").val()
			},
			multiple:	false
		});
		
		category_thumbnail_window.on('open',function() {
			var selection	= category_thumbnail_window.state().get('selection');
			id				= category_thumbnail_data.id;
			attachment		= wp.media.attachment(id);
			attachment.fetch();
			selection.add(attachment ? [attachment] : []);
		});

		category_thumbnail_window.on('select', function() {
			category_thumbnail_data = category_thumbnail_window.state().get('selection').first().toJSON();
			console.log(category_thumbnail_data);
			$("#category-thumbnail").val(Base64.encode(JSON.stringify(category_thumbnail_data)));
			$("#preview-thumbnail a.pct_image").css("background-image", 'url(' + category_thumbnail_data.url + ')');
			$("#preview-thumbnail").show();
			$("#create-thumbnail").hide();
			
			console.log(category_thumbnail_window);
		});
		
		category_thumbnail_window.open();
		
		event.preventDefault();
		return false;
	});
	
	// Remove Thumbnail
	$("#let-category-thumbnail").click(function() {
		category_thumbnail_clear();
	});
	
	$("form#addtag").bind("ajaxComplete", function() {
		category_thumbnail_clear();
	});
	
	category_thumbnail_clear = function clearForm() {
		$("#category-thumbnail").val("none");
		$("#preview-thumbnail a.pct_image").css("background-image", 'none');
		$("#preview-thumbnail").hide();
		$("#create-thumbnail").show();
	}
});