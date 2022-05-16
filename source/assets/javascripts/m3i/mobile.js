'use strict'

require('../vendor/jquery.waypoints.js');
require('../vendor/inview.js');

var trackEvent = require('../track-event.js');
var initialized = false;

function trackProgress(title, percent, segmentUrl) {

  // console.log('tracking title', title + ' - '+percent+'% Viewed');
  // console.log('tracking segmentUrl', '/'+segmentUrl);
  // console.log('tracking location', window.location.href.replace(/\/$/, "")+'/'+segmentUrl);

  // ga('set', 'title', title + ' - '+percent+'% Viewed');
  // ga('set', 'location', window.location.href.replace(/\/$/, "")+'/'+segmentUrl);
  // ga('set', 'page', '/'+segmentUrl);
  // ga('send', 'pageview');
}

function loadImages() {
  $('.view--mobile [data-src]').each(function(){
    $(this).attr('src', $(this).attr('data-src'));
  });
}

function init() {
  initialized = true;

  console.log('Mobile initialized');

  // Content images are only loaded if mobile is initialized to save page weight
  loadImages();

  $('[data-track]').each(function(){
    // data-track="Rear-wheel Design" data-percent="" data-segment="/rear-wheel-design"
    var title = $(this).attr('data-track');
    var percent = $(this).attr('data-percent');
    var segment = $(this).attr('data-segment');
    var tracked = false;

    new Waypoint.Inview({
      element: $(this)[0],
      enter: function(direction) {
        if (tracked == false && direction === 'down') {
          trackProgress(title, percent, segment);
          tracked = true;
        }
      },
    });
  });
}

function bootstrap() {
  // on resize
  $(window).resize(function(){
    if ($(window).width() < 800 && initialized === false){
      // only load elements when really in mobile!
      init();
    }
  });

  // On load
  if ($(window).width() < 800 && initialized === false){
    // only load elements when really in mobile!
    init();
  }

  // Social
  $(document).on('click', '.social--button', function(){
    var type = $(this).attr('data-toggle');
    var $icons = $('.social-wrapper');


    // Is the other option already open?
    var $otherType = $('.social--buttons .social--button:not([data-toggle="'+type+'"])');
    if ($otherType.hasClass('active')) {
      $icons.removeClass('active');
      $icons.find('.'+$otherType.attr('data-toggle')).addClass('hidden');
      $otherType.removeClass('active');
    }

    if (!$(this).hasClass('active')) {
      // show
      $icons.find('.'+type).removeClass('hidden');
      $icons.addClass('active');
    } else {
      // hide
      $icons.removeClass('active');
      $icons.find('.'+type).addClass('hidden');
    }
    $(this).toggleClass('active');
  });
}

$(document).ready(bootstrap)

module.exports = {}
