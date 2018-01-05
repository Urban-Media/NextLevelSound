jQuery(document).ready(function($) {
  $('.go_next_level').on('click', function() {
    $('html, body').animate({
        scrollTop: $( '#available_courses' ).offset().top
    }, 500);
  });
});
