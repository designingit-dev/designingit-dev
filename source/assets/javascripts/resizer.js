// Resize/ orientationchange event with a debounce
// Use:
// $(window).on('delayedResize', function() {
// // do stuff
// })

function bootstrap() {
  var gspDelayedWindowWidth = $(document).width()

  $(window).on("resize", function() {
    clearTimeout(resizeTimeout)

    var resizeTimeout = setTimeout(function() {
      if (gspDelayedWindowWidth !== $(document).width()) {
        triggerResize()
      }
      gspDelayedWindowWidth = $(document).width()
    }, 100)

  })

  $(window).on('orientationchange', function() {
    triggerResize()
  })

  function triggerResize() {
    $(window).triggerHandler('delayedResize')
  }
}

$(document).ready(bootstrap)
