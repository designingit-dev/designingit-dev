'use strict'

function bootstrap() {
  $('.js--showPedal').on("click", function() {
    $(this).addClass("is--hidden");
    $('.js--pedalExpanded').slideDown().removeClass("is--hidden");
  });

  $('.js--hidePedal').on("click", function() {
    $('.js--showPedal').removeClass("is--hidden");
    $('.js--pedalExpanded').slideUp().addClass("is--hidden");
  });

  $('.js--showMSeries').on("click", function() {
    $(this).addClass("is--hidden");
    $('.js--mSeriesExpanded').slideDown().removeClass("is--hidden");
  });

  $('.js--hideMSeries').on("click", function() {
    $('.js--showMSeries').removeClass("is--hidden");
    $('.js--mSeriesExpanded').slideUp().addClass("is--hidden");
  });

  $('.js--showStatusQuo').on("click", function() {
    $(this).addClass("is--hidden");
    $('.js--statusQuoExpanded').slideDown().removeClass("is--hidden");
  });

  $('.js--hideStatusQuo').on("click", function() {
    $('.js--showStatusQuo').removeClass("is--hidden");
    $('.js--statusQuoExpanded').slideUp().addClass("is--hidden");
  });

  $('.js--showCartNote').on("click", function() {
    $(this).addClass("is--hidden");
    $(this).siblings(".js--cartNoteExpanded").slideDown("fast").removeClass("is--hidden");
  });

  $(".js--cancelNotes").on("click", function() {
    $(this).closest(".js--cartNoteExpanded").slideUp("fast").addClass("is--hidden");
    $(this).parents(".js--cartNoteExpanded").siblings(".js--showCartNote").removeClass("is--hidden");
  });
}

$(document).ready(bootstrap)

module.exports = {}
