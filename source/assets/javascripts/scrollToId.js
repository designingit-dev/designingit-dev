// Scroll to an id attribute on the same page by dding `data-scroll` to
// any `<a>`

$(function() {
  $('[data-scroll][href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html, body').animate({
          // Add a little space to account for sticky header
          scrollTop: target.offset().top - 120
        }, 1000);
        return false;
      }
    }
  });
});
