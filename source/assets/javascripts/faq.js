$(function() {
  window.initialiseFAQ();
});

window.initialiseFAQ = function(faqClassSelector){
  if(typeof faqClassSelector == "undefined") {
    faqClassSelector = '.faq'
  }
  $(faqClassSelector).on("click", function(){
    $(this).find(".faq__icon").toggleClass("is--rotated");
    $(this).find(".faq__answer").slideToggle("fast");
  });
}
