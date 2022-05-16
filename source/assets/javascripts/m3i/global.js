'use strict'

/*
 * Global functions used by both screen sizes
 */

function bootstrap() {
  var isTouch = false //var to indicate current input type (is touch versus no touch)
  var isTouchTimer
  var curRootClass = '' //var indicating current document root class ("can-touch" or "")
  setBrowserAttrs();

  function addtouchclass(e){
      clearTimeout(isTouchTimer)
      isTouch = true
      if (curRootClass != 'can-touch'){ //add "can-touch' class if it's not already present
          curRootClass = 'can-touch'
          document.documentElement.classList.add(curRootClass)
      }
      isTouchTimer = setTimeout(function(){isTouch = false}, 500) //maintain "istouch" state for 500ms so removetouchclass doesn't get fired immediately following a touch event
  }

  function removetouchclass(e){
      if (!isTouch && curRootClass == 'can-touch'){ //remove 'can-touch' class if not triggered by a touch event and class is present
          isTouch = false
          curRootClass = ''
          document.documentElement.classList.remove('can-touch')
      }
  }

  function setBrowserAttrs() {
    var meta = getBrowser();
    $('html').attr({"data-ua":meta.name, "data-ua-ver": meta.version});
  }

  function getBrowser() {
    var ua=navigator.userAgent,tem,M=ua.match(/(edge|opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=/\brv[ :]+(\d+)/g.exec(ua) || [];
        return {name:'IE',version:(tem[1]||'')};
        }
    if(/edge/i.test(navigator.userAgent)) {
        tem=ua.match(/\bOPR|Edge\/(\d+)/)
          if(tem!=null) { return {name:'Edge',version:(tem[1]||'')}; }
        }
    if(M[1]==='Chrome'){
        tem=ua.match(/\bOPR|Edge\/(\d+)/)
        if(tem!=null)   {return {name:'Opera', version:tem[1]};}
        }
    M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
    return {
      name: M[0],
      version: M[1]
    };
  }

  document.addEventListener('touchstart', addtouchclass, true) //this event only gets called when input type is touch
  document.addEventListener('mouseover', removetouchclass, true) //this event gets called when input type is everything from touch to mouse/ trackpad

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

  // Share link
  $('[data-share-link]').on('click', function(evt){
    var url = $(this).attr('data-share-link');

    prompt("Copy and share the link below", url);

    evt.preventDefault();
    return false;
  });
}

$(document).ready(bootstrap)

jQuery.fn.redraw = function() {
  return this.hide(0, function(){jQuery(this).show()});
}

jQuery(document).ready(function(){
  jQuery('body').redraw();
});

module.exports = {}
