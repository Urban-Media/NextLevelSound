jQuery(document).ready(function($) {
  /*$('.collapse_expand').on('click', function() {
    var target = $(this).data('target');
    var type = $(this).data('type');
    var src = $("img", this).attr('src');
    var templateUrl = globalVars.templateUrl;
    var imageFilename = src.replace(/^.*[\\\/]/, '')

    // change image depending on what it currently is
    if (imageFilename == "plus.png") {
      $("img", this).fadeOut().attr("src", templateUrl + '/img/minus.png').fadeIn();
    } else {
      $("img", this).fadeOut().attr("src", templateUrl + '/img/plus.png').fadeIn();
    }
  });*/

  $('.view_study_options').on('click', function() {
    $('html, body').animate({
        scrollTop: $( '#studyOptions' ).offset().top
    }, 500);
  });
});
