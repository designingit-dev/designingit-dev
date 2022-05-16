'use strict'

require('../vendor/jquery.mousewheel.js');
require('../vendor/jquery.rwdImageMaps.js');
require('../vendor/jquery.preload.js');
require('../vendor/jquery.touchSwipe.min.js');

window.preloaded = false;
window.playing = true;

var staticImagePath = '/assets/images/m3i-experience/desktop/slides/';
var videoStops = [
  {
    'heading': 'V-Shape Frame',
    'tracking': {
      'slug': 'v-shape-frame',
      'percent': 3
    },
    'forward': {
      'start': 0, 'duration': 0
    },
    'reverse': {
      'start': 71, 'duration': 2
    },
    'staticImage': 'stop 00_00.jpg'
  },
  {
    'heading': 'V-Shape Frame - Industry First',
    'tracking': {
      'slug': 'v-shape-frame/industry-first',
      'percent': 6
    },
    'forward': {
      'start': 0, 'duration': 2
    },
    'reverse': {
      'start': 67, 'duration': 4
    },
    'staticImage': 'stop 00_02.jpg'
  },
  {
    'heading': 'V-Shape Frame - Matches All Riders',
    'tracking': {
      'slug': 'v-shape-frame/matches-all-riders',
      'percent': 9
    },
    'forward': {
      'start': 2, 'duration': 4
    },
    'reverse': {
      'start': 63, 'duration': 4
    },
    'staticImage': 'stop 00_06.jpg'
  },
  {
    'heading': 'Rear-Wheel Design',
    'tracking': {
      'slug': 'rear-wheel-design',
      'percent': 12
    },
    'forward': {
      'start': 6, 'duration': 4
    },
    'reverse': {
      'start': 60, 'duration': 3
    },
    'staticImage': 'stop 00_10.jpg'
  },
  {
    'heading': 'Rear-Wheel Design - Industry First',
    'tracking': {
      'slug': 'rear-wheel-design/industry-first',
      'percent': 15
    },
    'forward': {
      'start': 10, 'duration': 3
    },
    'reverse': {
      'start': 57, 'duration': 3
    },
    'staticImage': 'stop 00_13.jpg'
  },
  {
    'heading': 'Rear-Wheel Design - Drive-Train Protection',
    'tracking': {
      'slug': 'rear-wheel-design/drive-train-protection',
      'percent': 18
    },
    'forward': {
      'start': 13, 'duration': 3
    },
    'reverse': {
      'start': 54, 'duration': 3
    },
    'staticImage': 'stop 00_16.jpg'
  },
  {
    'heading': 'Magnetic Resistance',
    'tracking': {
      'slug': 'magnetic-resistance',
      'percent': 21
    },
    'forward': {
      'start': 16, 'duration': 3
    },
    'reverse': {
      'start': 50, 'duration': 4
    },
    'staticImage': 'stop 00_19.jpg'
  },
  {
    'heading': 'Magnetic Resistance - Quality Control',
    'tracking': {
      'slug': 'magnetic-resistance/quality-control',
      'percent': 24
    },
    'forward': {
      'start': 19, 'duration': 4
    },
    'reverse': {
      'start': 48, 'duration': 2
    },
    'staticImage': 'stop 00_23.jpg'
  },
  {
    'heading': 'Magnetic Resistance - TUV EN947-10 Certification',
    'tracking': {
      'slug': 'magnetic-resistance/true-power-readings',
      'percent': 27
    },
    'forward': {
      'start': 23, 'duration': 2
    },
    'reverse': {
      'start': 46, 'duration': 2
    },
    'staticImage': 'stop 00_25.jpg'
  },
  {
    'heading': 'Magnetic Resistance - Immediate Ride Data',
    'tracking': {
      'slug': 'magnetic-resistance/immediate-ride-data',
      'percent': 30
    },
    'forward': {
      'start': 25, 'duration': 2
    },
    'reverse': {
      'start': 43, 'duration': 3
    },
    'staticImage': 'stop 00_27.jpg'
  },
  {
    'heading': 'Drive-Train',
    'tracking': {
      'slug': 'drive-train',
      'percent': 33
    },
    'forward': {
      'start': 27, 'duration': 3
    },
    'reverse': {
      'start': 37, 'duration': 6
    },
    'staticImage': 'stop 00_30.jpg'
  },
  {
    'heading': 'Drive-Train - Drive-Train Design',
    'tracking': {
      'slug': 'drive-train/drive-train-design',
      'percent': 36
    },
    'forward': {
      'start': 30, 'duration': 6
    },
    'reverse': {
      'start': 35, 'duration': 2
    },
    'staticImage': 'stop 00_36.jpg'
  },
  {
    'heading': 'Drive-Train - Maintenance-Free',
    'tracking': {
      'slug': 'drive-train/maintenance-free',
      'percent': 39
    },
    'forward': {
      'start': 36, 'duration': 2
    },
    'reverse': {
      'start': 29, 'duration': 6
    },
    'staticImage': 'stop 00_38.jpg'
  },
  {
    'heading': 'Drive-Train - Automotive Quality',
    'tracking': {
      'slug': 'drive-train/automotive-quality',
      'percent': 42
    },
    'forward': {
      'start': 38, 'duration': 6
    },
    'reverse': {
      'start': 25, 'duration': 4
    },
    'staticImage': 'stop 00_44.jpg'
  },
  {
    'heading': 'Q Factor',
    'tracking': {
      'slug': 'q-factor',
      'percent': 45
    },
    'forward': {
      'start': 44, 'duration': 4
    },
    'reverse': {
      'start': 22, 'duration': 3
    },
    'stop': 49,
    'staticImage': 'stop 00_48.jpg'
  },
  {
    'heading': 'Q Factor - Curved Crank Arms',
    'tracking': {
      'slug': 'q-factor/curved-crank-arms',
      'percent': 48
    },
    'forward': {
      'start': 48, 'duration': 3
    },
    'reverse': {
      'start': 19, 'duration': 3
    },
    'staticImage': 'stop 00_51.jpg'
  },
  {
    'heading': 'The Pedals',
    'tracking': {
      'slug': 'the-pedals',
      'percent': 51
    },
    'forward': {
      'start': 51, 'duration': 3
    },
    'reverse': {
      'start': 16, 'duration': 3
    },
    'staticImage': 'stop 00_54.jpg'
  },
  {
    'heading': 'The Pedals - For SPD Riders',
    'tracking': {
      'slug': 'the-pedals/for-SPD-riders',
      'percent': 54
    },
    'forward': {
      'start': 54, 'duration': 3
    },
    'reverse': {
      'start': 14, 'duration': 2
    },
    'staticImage': 'stop 00_57.jpg'
  },
  {
    'heading': 'The Digital System',
    'tracking': {
      'slug': 'the-digital-system',
      'percent': 57
    },
    'forward': {
      'start': 57, 'duration': 2
    },
    'reverse': {
      'start': 12, 'duration': 2
    },
    'staticImage': 'stop 00_59.jpg'
  },
  {
    'heading': 'The Digital System - Computer Display',
    'tracking': {
      'slug': 'the-digital-system/computer-display',
      'percent': 60
    },
    'forward': {
      'start': 59, 'duration': 2
    },
    'reverse': {
      'start': 9, 'duration': 3
    },
    'staticImage': 'stop 01_01.jpg'
  },
  {
    'heading': 'The Digital System - M Series App',
    'tracking': {
      'slug': 'the-digital-system/m-series-app',
      'percent': 63
    },
    'forward': {
      'start': 61, 'duration': 3
    },
    'reverse': {
      'start': 4, 'duration': 5
    },
    'staticImage': 'stop 01_04.jpg'
  },
  {
      'heading': 'The Digital System - M Series Group App',
      'tracking': {
          'slug': 'the-digital-system/m-series-group-app',
          'percent': 66
      },
      'forward': {
          'start': 64, 'duration': 5
      },
      'reverse': {
          'start': 0, 'duration': 4
      },
      'staticImage': 'stop 01_08.jpg'
  },
  {
    'heading': 'The Details',
    'tracking': {
      'slug': 'the-details',
      'percent': 69
    },
    'forward': {
      'start': 69, 'duration': 4
    },
    'reverse': {
      'start': 0, 'duration': 0
    },
    'staticImage': 'stop 01_12.jpg'
  }
];

var initialized = false;
var activeSlideIndex = 0;
var activeSectionIndex = 0;
var videoForward = document.getElementById('video-player-forward');

var videoScrollHandlerInit = false;

function buildSlides() {
  var slidesBuilt = $.Deferred();

  var $slideContainer = $('.view--desktop .section--elements .slide-container');

  $.each(videoStops, function(i, stop){
    var slideIndex = (i+1);

    if (stop.staticImage) {
      var $slide = $('<div/>').addClass('slide slide-' + slideIndex).append($('<img/>').attr('data-src', staticImagePath + stop.staticImage));
      $slideContainer.append($slide);
    }

    if (i == videoStops.length-1) {
      slidesBuilt.resolve();
    }
  });

  return slidesBuilt.promise();
}

function buildScrollNav() {
  var navBuilt = $.Deferred();

  var $secondaryNavContainer = $('.navigation--secondary');

  var $scrollNavElements = $('.primary-nav [data-show="section--ultimate"], .primary-nav [data-slide]');

  $scrollNavElements.each(function(i, $item){
    var index = (i+1);

    $secondaryNavContainer.find('.nav--pages').append($('<li/>').append($item.outerHTML));

    if (index == $scrollNavElements.length) {
      navBuilt.resolve();
    }
  });

  return navBuilt.promise();
}

function preloadPageAssets() {
  var canPlay = false;
  var v = document.createElement('video');
  if(v.canPlayType && v.canPlayType('video/mp4').replace(/no/, '')) {
    canPlay = true;
  }

  var angle = 0;
  var total = 0;
  var looped = 0;
  function load(newTotal) {
    var loop = (newTotal - total);

    for (var i = loop; i > 0; i--) {
      var circle = document.getElementById('redrim');
      var fullAngle = 575
      var angle_increment = 5.75;

      circle.setAttribute("stroke-dasharray", angle + ", 20000");
      if (angle < fullAngle) {
        angle += angle_increment;
      }
      looped++;
    }

    total = (total + loop);
  }

  window.loaded = $.Deferred();

  $('.preloader .logo').addClass('active');
  if (preloaded === false) {
    $.when(buildSlides()).then(buildScrollNav()).then(function(){

      // Collect assets
      // data-src, data-src-unload
      var pageAssets = [];
      $('[data-src], [data-src-unload]').each(function(i, asset){
        if ($(asset).attr('data-src').indexOf("mp4") >= 0) {
          if (canPlay === true) {
            pageAssets.push( ($(asset).attr('data-src-unload') ? $(asset).attr('data-src-unload') : $(asset).attr('data-src')) );
          }
        } else if ($(asset).attr('data-src').indexOf("webm") >= 0) {
          if (canPlay === false) {
            pageAssets.push( ($(asset).attr('data-src-unload') ? $(asset).attr('data-src-unload') : $(asset).attr('data-src')) );
          }
        } else {
          pageAssets.push( ($(asset).attr('data-src-unload') ? $(asset).attr('data-src-unload') : $(asset).attr('data-src')) );
        }
      });

      $.preload(pageAssets).then(function() {
        // Remove loading animation and show overlay image
        setTimeout(function(){
          $('.preloader .loader').removeClass('active');
        }, 300);

        // Show scroll instructions and initialize desktop view
        setTimeout(function(){
          window.preloaded = true;
          window.playing = false;
          loaded.resolve();
        }, 500);
      }, function() {
      }, function(progress) {
        if (!$('.preloader .loader').hasClass('active')) {
          $('.preloader .loader').addClass('active');
        }


        load(Math.round(progress * 100));
      });
    });
  } else {
    loaded.resolve();
  }

  return loaded.promise();
}

function goToNext(index) {
  var slideObject = videoStops[(index-1)];

  // pulled from slides object
  var playDuration = (slideObject.forward.duration * 1000);
  videoForward.currentTime = slideObject.forward.start;

  /*

  1. Unload current active page content
  2. play video
  3. after timeout (duration of play) activate slide
  4. Track play

  */

  // Make sure video has "active" class
  $('#video-player-forward').addClass('active');

  // Remove active slide and text containers
  setTimeout(function(){
    $('.section--elements .slide-container .active, .section--elements .text-container .active').removeClass('active');
  }, 100);

  // Wait for unload of active animation
  setTimeout(function(){
    var isPlaying = videoForward.currentTime > 0 && !videoForward.paused && !videoForward.ended && videoForward.readyState > 2;
    if (!isPlaying) {
      videoForward.pause();
      videoForward.play();
    }
  }, 475);

  setTimeout(function(){
    if (!videoForward.paused) {
      videoForward.pause();
    }

    loadSlide(index);
    scrollNavigationHandler($('.navigation [data-slide="'+index+'"]'));
    window.playing = false;
  }, (playDuration + 475));
}

function goToPrevious(index) {
  var slideObject = videoStops[(index-1)];
  $('.section--elements .slide-container .active, .section--elements .text-container .active').removeClass('active');
  loadSlide(index);
  scrollNavigationHandler($('.navigation [data-slide="'+index+'"]'));
  setTimeout(function(){
    window.playing = false;
  }, 2000);
}

function loadSlide(index) {
  var $currentActive = $('.slide.active');
  var $loadSlide = $('.slide-'+index);

  if (!$('.section--elements').hasClass('active')) {
    $('.section--elements').removeClass('unload').addClass('active');
    videoScrollHandler();
  }

  // Remove active class from other sections
  $('.section.active:not(.section--elements)').removeClass('active');

  checkSecondaryNavDisplay();

  if (!$loadSlide.hasClass('active')) {
    $currentActive.each(function(){ $(this).removeClass('active') });
    $('.section--reviews, .details.active').removeClass('active');
    $loadSlide.addClass('active');
    activeSlideIndex = index;
  }

  if (index == 23) {
    window.playing = true;
    buildSprite('.slide-23 .sprite-canvas');

    var $elementsSection = $('.section--elements');
    if ($elementsSection.hasClass('unload')) {
      $elementsSection.removeClass('unload');
    }

    handleHotspotScroll();

    // Map image maps responsive
    $('img[usemap]').rwdImageMaps();

    setTimeout(function(){
      window.playing = false;

      if (!$('.section--details').hasClass('active')) {
        $('.section--details').addClass('active');
      }
    }, 2000);
  } else {
    // Disable hotspots scrolling event
    $('.hotspots-map').off('wheel');
  }
}

function loadPage(blade) {
  var $blade = $('.'+blade);
  var $pages = $('.view--desktop .section');
  var pageIndex = $pages.index($blade[0]);

  if ($blade.hasClass('section--details')) {
    activeSectionIndex = pageIndex;

    $pages.each(function(i, page){
      var $page = $(page);

      if ($page.index() > activeSectionIndex && $page.hasClass('unload')) {
        $page.removeClass('unload');
      }
    });
    checkSecondaryNavDisplay();
    return true;
  }

  if (pageIndex <= activeSectionIndex) {
    $blade.addClass('unload');
  }

  // Load blade
  if ($blade.hasClass('unload')) {
    $blade.css('z-index', 20);
    $blade.addClass('override').removeClass('unload');
  } else {
    $blade.addClass('active');
  }

  activeSectionIndex = pageIndex;

  setTimeout(function(){
    $pages.each(function(i, page){
      var $page = $(page);

      // skip the details section
      if ($page.hasClass('section--details')) {
        return true;
      }

      if ($page.index() != activeSectionIndex && $page.hasClass('active')) {
        if ((i == $pages.length - 1 || $page.index() > pageIndex) && $page.index() != activeSectionIndex) {
          $page.removeClass('active unload');
        } else if ($page.index() != activeSectionIndex) {
          $page.removeClass('active');
        }
      } else if ($page.index() == activeSectionIndex) {
        $page.removeClass('override');
        $page.removeAttr('style');
      }

      if ($page.index() < activeSectionIndex) {
        $page.addClass('unload');
      } else if ($page.index() > activeSectionIndex) {
        $page.removeClass('unload');
      }
    });
  }, 400);

  setTimeout(function(){
    checkSecondaryNavDisplay();
  }, 450);
}

function videoScrollHandler() {
  if (videoScrollHandlerInit) {
    return false;
  }

  window.playing = false;
  videoScrollHandlerInit = true;
  var totalSlides = $('.section--elements .text-container .slide').length;

  // console.log('video scroll initiated');

  $('.section--elements.active').on('wheel', function(evt) {
    evt.preventDefault();

    // Only handle scrolls if the video isn't playing.
    if (window.playing == false) {
      window.playing = true;
      // playing = true;
      var goTo = Number(activeSlideIndex);

      if (evt.originalEvent.deltaY > 0) {
        goTo = goTo + 1;
        // Does a next slide exist? /// go to next page
        if (goTo <= totalSlides) {
          goToNext(goTo);
        }
      } else {
        goTo = goTo - 1;
        // Does a previous slide exist?
        if (goTo > 0) {
          goToPrevious(goTo);
        } else {
          // this would mean we're on the first slide already
          $('.section.section--ultimate').toggleClass('unload active');

          setTimeout(function(){
            window.playing = false;
          }, 2000);
        }
      }
    }

    return false;
  });

  $(".section--elements.active").swipe({
    //Generic swipe handler for all directions
    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
      if (window.playing == false && $(event.target).hasClass('details-image') == false) {
        window.playing = true;
        var goTo = Number(activeSlideIndex);

        switch(direction) {
          case "up":
            goTo = goTo + 1;
            // Does a next slide exist? /// go to next page
            if (goTo <= totalSlides) {
              goToNext(goTo);
            }
            break;

          case "down":
            goTo = goTo - 1;
            // Does a previous slide exist?

            if (goTo > 0) {
              goToPrevious(goTo);
            } else {
              // this would mean we're on the first slide already
              $('.section.section--ultimate').toggleClass('unload active');

              setTimeout(function(){
                window.playing = false;
              }, 2000);
            }
            break;
        }
      }
    }
  });
}

function checkSecondaryNavDisplay() {
  var $nav = $('.navigation--secondary');

  var showSecondary = true;
  $('.section').each(function(i){

    if ($(this).hasClass('active') && !$(this).hasClass('js--show-secondary-nav')) {
      showSecondary = false;
    }
  });

  if (showSecondary) {
    $nav.addClass('show');
  } else {
    $nav.removeClass('show');
  }

  if (activeSlideIndex < videoStops.length) {
    $nav.find('.nav--direction-next').addClass('show');
  } else {
    $nav.find('.nav--direction-next').removeClass('show');
  }

  if ($('.section--ultimate').hasClass('active')) {
    $nav.find('.nav--direction-prev').removeClass('show');
  } else {
    $nav.find('.nav--direction-prev').addClass('show');
  }
}

function scrollNavigationHandler($ele) {
  if ($ele.hasClass('active')) {
    return false;
  } else {
    $('.navigation .active, .navigation--secondary .active').removeClass('active');
  }

  // is parent already active
  if (!$ele.closest('.segment').hasClass('current')) {
    $('.navigation .current').removeClass('current');
  }

  $ele.closest('li.segment').addClass('current');
  $ele.addClass('active');

  // Secondary Nav
  if ($ele.attr('data-slide')) {
    $('.navigation--secondary a[data-slide="'+$ele.attr('data-slide')+'"]').addClass('active');
  } else {
    var page = $ele.attr('data-show');
    var $section = $('.section.'+page);
    if ($section.hasClass('unload')) {
      $section.toggleClass('unload active');
    } else {
      $section.addClass('load active');
    }

    $('.navigation a[data-show="'+page+'"], .navigation--secondary a[data-show="'+page+'"]').addClass('active');
  }
}

function navigationHandler() {
  $('.navigation a, .navigation--secondary li a').on('click', function(evt){
    evt.preventDefault();

    scrollNavigationHandler($(this));

    // is slide?
    if ($(this).attr('data-slide')) {
      var slideIndex = $(this).attr('data-slide');

      $('.section--ultimate').addClass('unload');
      $('.section--ultimate.active, .details.active, .section--reviews.active, .section--smartdisplay.active, .section--education.active, .section--videos.active').removeClass('active');

      loadSlide(slideIndex);
    }
    else if ($(this).hasClass('page')) {
      // is page
      var blade = $(this).attr('data-show');
      loadPage(blade);
    }
    else if ($(this).attr('data-target')) {
      // Details section
      $('.section--reviews.active, .section--elements .active').removeClass('active');
    }

    return false;
  });
}

function secondaryNavigationHandler() {
  $('.navigation--secondary a.nav--direction').on('click', function(evt){

    var $active = $('.primary-nav a.active');
    var goTo = parseInt(activeSlideIndex);

    // next/prev handling
    if ($(this).hasClass('nav--direction-prev')) {
      if ($active.attr('data-slide')) {
        if (activeSlideIndex > 1) {
          $('.navigation a[data-slide="'+(--goTo)+'"]').click();
        } else {
          $('.navigation a[data-show="section--ultimate"]').click();
        }
      }
    } else {
      if ($active.attr('data-slide')) {
        if (activeSlideIndex < videoStops.length) {
          $('.navigation a[data-slide="'+(++goTo)+'"]').click();
        }
      } else if ($active.hasClass('page')) {
        $('.navigation a[data-slide="1"]').click();
      }
    }

    checkSecondaryNavDisplay();

    return false;
  });
}

function handleUltimateScroll() {
  // console.log('handleUltimateScroll');
  $('.section--ultimate.active').on('wheel', function(evt) {
    var $current = $(this);
    evt.preventDefault();

    // Only handle scrolls if the video isn't playing.
    if (window.playing == false) {
      window.playing = true;

      if (evt.originalEvent.deltaY > 0) {

        // make next active
        $current.addClass('unload');

        // update navigation
        $('.navigation a[data-slide="1"]').click();
      }

      setTimeout(function(){
        window.playing = false;
      }, 1500);
    }
    return false;
  });

  $(".section--ultimate.active").swipe({
    //Generic swipe handler for all directions
    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
      var $current = $(event.target);

      if (window.playing == false) {
        window.playing = true;

        if (direction == "up") {

          // make next active
          $current.addClass('unload');

          // update navigation
          $('.navigation a[data-slide="1"]').click();
        }

        setTimeout(function(){
          window.playing = false;
        }, 1500);
      }
    }
  });
}

function handleHotspotScroll() {
  // console.log('handleHotspotScroll');
  $('.hotspots-map').on('wheel', function(evt) {
    // console.log('hotspots-map');

    evt.preventDefault();

    // Only handle scrolls if the video isn't playing.
    if (window.playing == false) {
      window.playing = true;

      if (evt.originalEvent.deltaY > 0) {
        // make next active
        $('.details.details-2').addClass('active');

        // update navigation
        scrollNavigationHandler($('.navigation [data-target="'+$('.details.details-2').attr('data-nav')+'"]'));
      } else {
        // go up
        // Should fade out details and play video in reverse
        $('slide.slide-23.active').removeClass('active');
        goToPrevious(22);
      }

      setTimeout(function(){
        window.playing = false;
      }, 1500);
    }
    return false;
  });

  $(".hotspots-map").swipe({
    //Generic swipe handler for all directions
    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
      if (window.playing == false) {
        window.playing = true;

        if (direction == "up") {
          // make next active
          $('.details.details-2').addClass('active');
          // update navigation
          scrollNavigationHandler($('.navigation [data-target="'+$('.details.details-2').attr('data-nav')+'"]'));
        } else {
          // go up
          // Should fade out details and play video in reverse
          $('slide.slide-23.active').removeClass('active');
          goToPrevious(22);
        }

        setTimeout(function(){
          window.playing = false;
        }, 1500);
      }
    }
  });
}

function detailsScrollHandler() {
  $('.section--details').on('wheel', function(evt) {
    evt.preventDefault();

    var $current = $(this).find('.details.active');

    // Only handle scrolls if the video isn't playing.
    if (window.playing == false) {
      window.playing = true;

      if (evt.originalEvent.deltaY > 0) {
        // go down
        if ($current.next('.details').length) {
          // make next active
          $current.next('.details').addClass('active');
          $current.removeClass('active');

          // update navigation
          scrollNavigationHandler($('.navigation [data-target="'+$current.next('.details').attr('data-nav')+'"]'));
        }
      } else {
        // go up
        if ($current.prev('.details').length) {
          // console.log('details go to previous sprite');

          // make next active
          $current.prev('.details').addClass('active');
          $current.removeClass('active');

          // update navigation
          scrollNavigationHandler($('.navigation [data-target="'+$current.prev('.details').attr('data-nav')+'"]'));

        } else {
          // if first go to slide 23
          loadSlide(23);
          $current.removeClass('active');
          scrollNavigationHandler($('.navigation [data-slide="23"]'));
        }
      }

      setTimeout(function(){
        window.playing = false;
      }, 1500);
    }

    return false;
  });

  $(".section--details").swipe({
    //Generic swipe handler for all directions
    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
      var $current = $(event.target).closest('.section--details').find('.details.active');

      // Only handle scrolls if the video isn't playing.
      if (window.playing == false) {
        window.playing = true;

        switch(direction) {
          case "up":
            // go down
            if ($current.next('.details').length) {
              // console.log('details go to next sprite');

              // make next active
              $current.next('.details').addClass('active');

              $current.removeClass('active');

              // update navigation
              scrollNavigationHandler($('.navigation [data-target="'+$current.next('.details').attr('data-nav')+'"]'));
            }
            break;

          case "down":
            // go up
            if ($current.prev('.details').length) {
              // make next active
              $current.prev('.details').addClass('active');
              $current.removeClass('active');
              // update navigation
              scrollNavigationHandler($('.navigation [data-target="'+$current.prev('.details').attr('data-nav')+'"]'));

            } else {
              // if first go to slide 23
              loadSlide(23);
              $current.removeClass('active');
              scrollNavigationHandler($('.navigation [data-slide="23"]'));
            }
            break;
        }

        setTimeout(function(){
          window.playing = false;
        }, 1500);
      }
    }
  });
}

function handleImageMapClicks() {
  $('map area').on('click', function(evt){
    evt.preventDefault();

    var target = $(this).attr('data-target');
    $('.section--elements').removeClass('display--details');

    if ($('.'+target).hasClass('active')) {
      setTimeout(function(){
        // show clickable view
        $('.section--elements').addClass('display--details');
      }, 5000);
      return false;
    }

    if (window.playing == false) {
      window.playing = true;
        loadDetailView(target);
        scrollNavigationHandler($('.navigation [data-target="'+target+'"]'));
        window.playing = false;
    }

    return false;
  });
}

function handleSpriteClicks() {
  $('.details li a').on('click', function(evt){
    evt.preventDefault();

    var target = $(this).attr('data-target');
    $('.section--elements').removeClass('display--details');

    if ($('.'+target).hasClass('active')) {
      setTimeout(function(){
        // show clickable view
        $('.section--elements').addClass('display--details');
      }, 5000);
      return false;
    }

    if (window.playing == false) {
      window.playing = true;
      loadDetailView(target);
      window.playing = false;
      // }
    }

    return false;
  });
}

function loadDetailView(targetClass) {
  checkSecondaryNavDisplay();

  if (!$('.section--details').hasClass('active')) {
    $('.section').removeClass('active');
    $('.section--details').addClass('active');
  }

  var $details = $('.section--details').find('.'+targetClass);

  if (!$details.hasClass('active')) {
    $details.addClass('active');
  }

  $('.section--details').find('.details.active:not(.'+targetClass+')').removeClass('active');
}

function buildSprite(ele) {
  var container = document.querySelector(ele);

  var sprite = container.parentNode;

  var slideWidth = 1600;
  var slideHeight = 968;

  if (container.getAttribute('data-height')) {
    slideHeight = parseInt(container.getAttribute('data-height'));
  }

  var aspect = slideWidth / slideHeight;

  var windowHeight = $('.view--desktop').height();

  var cHeight = windowHeight;
  var cWidth = Math.round(windowHeight * aspect);

  var canvas = container.querySelector('canvas');
  if (!canvas) {
    canvas = document.createElement('canvas');
    container.appendChild(canvas);
  }

  var ctx = canvas.getContext('2d');
  var TIME_PER_FRAME = container.getAttribute('data-frame-rate');
  var sprite, currY;

  canvas.width = cWidth;
  canvas.height = cHeight;

  var spriteHeight;
  var FRAMES;
  var img = new Image();
  function setAssetReady()
  {
    this.ready = true;
    spriteHeight = this.height;
  }
  img.onload = setAssetReady;
  img.src = container.getAttribute('data-src');

  var preloader = setInterval(preloadSprite, TIME_PER_FRAME);

  function preloadSprite()
  {
    if (img.ready)
    {
      clearInterval(preloader);
      sprite = setInterval(animateSprite, TIME_PER_FRAME);
    }
  }

  currY = 0;
  function animateSprite() {
    ctx.drawImage(img, 0, currY, slideWidth, slideHeight, 0, 0, cWidth, cHeight);

    currY += slideHeight;
    if (currY > spriteHeight)
      clearInterval(sprite);
  }
}

function checkNav() {
  if ($(window).width() >= 800 && $(window).width() <= 1200) {
    $('.primary-nav, .navigation').addClass('collapsed');
  } else if ($(window).width() > 1200) {
    $('.primary-nav, .navigation').removeClass('collapsed');
  }

  // Ensure secondary nav is hidden
  if ($(window).width() < 800) {
    $('.navigation--secondary').removeClass('show');
  } else if ($(window).width() >= 800) {
    // if over 800px run function to determine if secondary nav should be displayed
    checkSecondaryNavDisplay();
  }
}

function resizeCanvases() {
  var slideWidth = 1600;
  var slideHeight = 968;
  var aspect = slideWidth / slideHeight;

  var height = Math.floor($('.view--desktop')[0].getBoundingClientRect().width * 0.605);
  var maxHeight = Math.floor(($('.wrapper')[0].getBoundingClientRect().height - $('.header')[0].getBoundingClientRect().height) * 0.90);

  if ( height > maxHeight ) {
    height = maxHeight;
  }

  var wrapperOffset = Math.floor(($('.wrapper')[0].getBoundingClientRect().height - $('.header')[0].getBoundingClientRect().height) - height);

  $('.view--desktop').height((height > 968 ? 968 : height));

  if (wrapperOffset > 50) {
    $('.view--desktop').addClass('vertically-center');

  } else if ($('.view--desktop').hasClass('vertically-center')) {
    $('.view--desktop').removeClass('vertically-center');
  }

  var windowHeight = $('.view--desktop').height();

  var cHeight = windowHeight;
  var cWidth = Math.round(windowHeight * aspect);

  if (cWidth > $('.view--desktop').width()) {
    cWidth = Math.floor($('.view--desktop')[0].getBoundingClientRect().width);
    cHeight = Math.round(cWidth / aspect);
  }

  $('.section--details .details-slide').each(function(){
    $(this).height(cHeight).width(cWidth);
  });
}

function init() {
  initialized = true;

  // If desktop -- breakpoint/width check
    // Build slides
      // Preload Content

  $.when(preloadPageAssets()).then(function(){

    // Handle navigation clicks
    navigationHandler();
    secondaryNavigationHandler();
    handleImageMapClicks();
    detailsScrollHandler();
    handleSpriteClicks();

    videoForward.load();
    // videoReverse.load();

    $('.preloader').addClass('finished');
    $('.section.section--ultimate').addClass('load active');
    $('.navigation a[data-show="section--ultimate"], .navigation--secondary a[data-show="section--ultimate"]').addClass('active');
    handleUltimateScroll();

    // Load assets
    $('.view--desktop [data-src]').each(function(){
      $(this).attr('src', $(this).attr('data-src'));
    });

    // Resize canvas containers
    resizeCanvases();
    $(window).resize(function(){
      if ($(window).width() >= 800) {
        resizeCanvases();
      }
    });

    checkMaxScreenSize();
    checkNav();
    $(window).resize(function(){
      checkMaxScreenSize();
      checkNav();
    });

  });
}

function checkMaxScreenSize() {
  var isAtMaxWidth = screen.availWidth - window.innerWidth === 0;

  var $notice = $('#js--noticeWindowWidth');
  var displayClass = 'show';

  if (!isAtMaxWidth) {
    $notice.addClass(displayClass);
  } else if ($notice.hasClass(displayClass) && isAtMaxWidth) {
    $notice.removeClass(displayClass);
  }

  $('.js--close-windowWidthNotice').on('click', function(){
    $notice.removeClass(displayClass);
    return false;
  });
}

function demo() {

  var i = setInterval(function() {
    if(videoForward.readyState > 0) {
      clearInterval(i);
    }
  }, 200);
}

function bootstrap() {
  if (document.documentMode || /Edge/.test(navigator.userAgent)) {
    $('ul:hidden').each(function(){
      $(this).parent().append($(this).detach());
    });
  }

  // On resize
  $(window).resize(function(){
    if ($(window).width() >= 800 && initialized === false){
      // only load elements when really in desktop!
      init();
    }
  });

  // On load
  if ($(window).width() >= 800 && initialized === false){
    // only load elements when really in desktop!
    init();
  }

  $('.footer .social--button').on('click', function(){
    var type = $(this).attr('data-type');

    var $modal = $('.social--overlay');

    if ($modal.hasClass('show')) {
      setTimeout(function(){
        $modal.find('.show').removeClass('show');
        $modal.find('[data-type="'+type+'"]').addClass('show');
      },300);
    } else {
      $modal.find('[data-type="'+type+'"]').addClass('show');
      $modal.addClass('show');
    }

    var $desktop = $('.view--desktop');
    if (!$desktop.hasClass('js--overlay-shown')) {
      $desktop.addClass('js--overlay-shown')
    }

    return false;
  });

  $('.view--desktop .social--overlay-close').on('click', function(){
    var $modal = $('.social--overlay');
    $modal.removeClass('show');
    setTimeout(function(){
      $modal.find('.show').removeClass('show');

      var $desktop = $('.view--desktop');
      if ($desktop.hasClass('js--overlay-shown')) {
        $desktop.removeClass('js--overlay-shown')
      }
    },300);

    return false;
  });

  $('ul.primary-nav li.toggleNav button').on('click', function(evt){
    $('.primary-nav, .navigation').toggleClass('collapsed');

    // Reset div scroll position to top
    $('.primary-nav, .navigation').scrollTop(0);

    $('.primary-nav, .navigation').animate({
      scrollTop: 0
    }, 0);

    evt.preventDefault();
    return false;
  });
}

$(document).ready(bootstrap)

module.exports = {}
