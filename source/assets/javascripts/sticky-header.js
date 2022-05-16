var throttle = require('lodash.throttle')

var stickyHeader = {
  $topNavigationSecondaryMenu: $('.siteHeader__topContainer').first(),
  $topNavigationPrimaryMenu: $('.js--stickyHeader').first(),
  $topNavigation: $('.siteHeader').first(),
  $spacer: $('#js--stickyHeaderSpacer'),
  frameId: null,
  $absoluteTopBannersContainer: $('#absoluteTopBannersContainer'),

  setSpacerHeight: function(headerHeight){
    stickyHeader.$spacer.outerHeight(headerHeight)
  },

  transitionSpacerHeight: function(){
    cancelAnimationFrame(stickyHeader.frameId);
    stickyHeader.frameId = requestAnimationFrame(function () {
      var currentHeight = stickyHeader.$spacer.outerHeight();
      var targetHeight = stickyHeader.$absoluteTopBannersContainer.outerHeight() + stickyHeader.$topNavigationSecondaryMenu.outerHeight() + stickyHeader.$topNavigationPrimaryMenu.outerHeight();
      if($(document).scrollTop() > 1){
        targetHeight -= stickyHeader.$topNavigationSecondaryMenu.outerHeight();
      }
      if (targetHeight === currentHeight) {
        return
      }
      stickyHeader.setSpacerHeight(targetHeight)
    })
  },

  handleScroll: function(){
    if($(document).scrollTop() > 1){
      stickyHeader.$topNavigationSecondaryMenu.removeClass('is--visible--medium');
      stickyHeader.$topNavigationPrimaryMenu.addClass('is--sticky')
      stickyHeader.$topNavigation.addClass('is--sticky');
    } else {
      stickyHeader.$topNavigationSecondaryMenu.addClass('is--visible--medium');
      stickyHeader.$topNavigation.removeClass('is--sticky');
      stickyHeader.$topNavigationPrimaryMenu.removeClass('is--sticky');
    }
    stickyHeader.transitionSpacerHeight()
  }
}

$(window).on('delayedResize', function() {
  stickyHeader.transitionSpacerHeight();
})

$(document).on('scroll', stickyHeader.handleScroll)

$(document).on('bannersLoaded absoluteTopBannerClosed', function(){
  stickyHeader.transitionSpacerHeight();
})

$(document).ready(function(){
  stickyHeader.transitionSpacerHeight();
})
