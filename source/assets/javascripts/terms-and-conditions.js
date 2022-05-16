'use strict'

function bootstrap() {
  $('.js--toggleCheckout').on("click", function() {
    var btnClass = 'button--disabled';
    var $els = $('.js--checkoutBtn');

    if (this.checked) {
      $els.removeClass(btnClass);
    } else {
      $els.addClass(btnClass);
    }
  });

  /* Tomfoolery incase someone tries to remove the disabled class from above */
  $('.js--checkoutBtn').on("click", function(e) {
    if (!$('.js--toggleCheckout').is(':checked')) {
      e.preventDefault();
      return false;
    }
  });
}

$(document).ready(bootstrap)

module.exports = {}
