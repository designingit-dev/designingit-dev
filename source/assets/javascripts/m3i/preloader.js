'use strict'

// var trackEvent = require('../track-event.js');
require('../vendor/jquery.preload.js');

window.preloaded = false;
window.playing = false;

window.timer = null;

var angle = 0;
function load() {
  var circle = document.getElementById('redrim');
  var fullAngle = 575
  var angle_increment = 5.75;

  circle.setAttribute("stroke-dasharray", angle + ", 20000");
  if (angle < fullAngle) {
    angle += angle_increment;
  // } else {
  //   // Demo only
  //   window.clearInterval(window.timer);
  }
}

function bootstrap() {
  // Collect Assests into array!

  var images =
  var


  $('.preloader .logo').addClass('active');
  $.preload([
    '/assets/images/m3i-experience/desktop/video/m3i.mp4',
    '/assets/images/m3i-experience/mobile/headings/heading-v-shape-frame.svg',
    '/assets/images/m3i-experience/headings/rear-wheel-design.svg',
    '/assets/images/m3i-experience/headings/guaranteed-accuracy.svg'
  ]).then(function() {
    window.preloaded = true;

    console.log("All done.")
  }, function() {
    console.log("Something went wrong.")
  }, function(progress) {
    if (!$('.preloader .loader').hasClass('active')) {
      $('.preloader .loader').addClass('active');
    }

    console.log(Math.round(progress * 100) + '%')

    // load();
  });

  // Preload splash assets
  // Preload videos
  // Preload slides
  // Preload sprite images
  // preload why the m3i assets

  // window.preloaded = false;
  // setTimeout(function() {
  //   $('.preloader .logo, .preloader .loader').addClass('active');
  // }, 1000);

  // setTimeout(function() {
  //   $('.preloader .loader').removeClass('active');
  //   $('.preloader .overlay').addClass('active');
  //   $('.preloader .text .site-heading').addClass('active');
  //   $('.preloader .text .scroll-to-continue').addClass('active');
  // }, 1500);


  // Demo preloader

  // setTimeout(function() {
  //   var i = 0;
  //   for (i = 0; i < 50; i++) {
  //     console.log('counting to 100 at ' + i);
  //     // setTimeout(function(){
  //       load();
  //     // }, 300);
  //   }
  // }, 2000);

  // setTimeout(function() {
  //   var interval = 30;
  //   window.timer = window.setInterval(function () {
  //     load();
  //   }, interval);
  // }, 2000);

  // setTimeout(function() {
  //   $('.preloader .loader').removeClass('active');
  // }, 6000);

  // setTimeout(function(){
  //   $('.preloader .overlay').addClass('active');
  // }, 6500);

  // setTimeout(function(){
  //   $('.preloader .text .site-heading').addClass('active');
  // }, 7500);

  // setTimeout(function(){
  //   $('.preloader .text .scroll-to-continue').addClass('active');
  //   window.preloaded = true;
  // }, 8000);
  // window.preloaded = true;
}

$(document).ready(bootstrap)

module.exports = {}
