'use strict'

function bootstrap() {
  // $(".responsive--text span").scalem();

  $('.responsive--text span').each(function(){
    // console.log();
    $(this).scalem();
  });

  // $('.heading-title.responsive--text').flowtype({fontRatio : 8});
  // $('.heading-subtitle.responsive--text').flowtype();

  // $(".responsive--text").fitText();

  // $(".responsive--text").boxfit();

  setTimeout(function() {
    $('.preloader .logo, .preloader .loader').addClass('active');
  }, 1000);

  setTimeout(function() {
    $('.preloader .loader').removeClass('active');    
  }, 5000);

  setTimeout(function(){
    $('.preloader .sprite').addClass('play');
  }, 5500);

  setTimeout(function(){
    $('.preloader .text .site-heading').addClass('active');
  }, 6500);

  setTimeout(function(){
    $('.preloader .text .scroll-to-continue').addClass('active');
  }, 7000);



  $('.js--scrollto').on('click', function(evt){
    evt.preventDefault();

    var id = $(this).attr('href');
    var $targetEl = $(id);

    var $mobileMenuBtn = $('.siteHeader__menuButton');
    if ($mobileMenuBtn.hasClass('is--active')) {
      $mobileMenuBtn[0].click();
    }

    $('html, body').animate({
        scrollTop: ($targetEl.offset().top - 76) 
    }, 2000);

    return false;
  });

  // var loop = "M35,65a30,30,0,0,0,0-60a30,30,0,0,0,0,60";
  // var loopLength = Snap.path.getTotalLength(loop);

  // var s = Snap();

  // var circle = s.path({
  //   path: loop,
  //   fill: "#fff",
  //   fillOpacity: "0.4",
  //   stroke: "#d10a2c",
  //   strokeWidth: 0,
  //   strokeLinecap: "round"
  // });

  // var circleOutline = s.path({
  //   path: Snap.path.getSubpath(loop, 0, 0),
  //   stroke: "#d10a2c",
  //   fillOpacity: 0,
  //   strokeWidth: 0,
  //   strokeLinecap: "round"
  // });

  // setTimeout(function () {
  //   Snap.animate(0, loopLength,
  //     function(step){ //step function
  //       console.log('step', step);
    
  //       circleOutline.attr({
  //         path: Snap.path.getSubpath(loop, 0, step),
  //         strokeWidth: 8
  //       });
        
  //     }, // end of step function
  //     800, //duration
  //     mina.easeInOut, //easing
  //     function(){ //callback
  //       circleOutline.attr({
  //         path: Snap.path.getSubpath(loop, 0, 0),
  //         strokeWidth: 0
  //       });
  //       // setTimeout(function(){
  //       //   circleOutline.attr({
  //       //     path: Snap.path.getSubpath(loop, 0, 0),
  //       //     strokeWidth: 0
  //       //   });
  //       // }, 1000);//setTimeout
  //     }//callback
  //   );//Snap.animate
  // }, 1000);
}


$(document).ready(bootstrap)

module.exports = {}
