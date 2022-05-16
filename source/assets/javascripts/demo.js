$('#demoVan__stickyButton,#demoVan__topSubmit').on('click', function(e){
  e.preventDefault();
  var $div = $($(this).attr('href'));
  window.smoothScroll($div.offset().top);
  return false;
});
