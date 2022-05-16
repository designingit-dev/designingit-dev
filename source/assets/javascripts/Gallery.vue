<script>
module.exports = {
  data: function () {
    return {
      activeThumb: 0,
      displayedImageHeight: 270,
      targetHeight: null,
    }
  },

  ready: function () {
    var self = this

    self.$nextTick(function () {
      self.setPrimaryImageHeight()
    })
  },

  computed: {

  },

  methods: {
    setPrimaryImageHeight: function () {
      var self = this

      if (!self.$els.displayedImageWrapper) return

      var primaryImage = self.$els.displayedImageWrapper.querySelector("img")

      if (!primaryImage) return

      primaryImage.addEventListener("load", function () {
        if (!primaryImage.offsetHeight) return
        self.displayedImageHeight = primaryImage.offsetHeight
      })

    },

    transitionHeightTo: function (targetHeight) {
      var self = this
      var stepSize = 15
      var delayBeforeTransition = 250
      self.targetHeight = Math.floor(targetHeight)

      function loop () {
        if (self.targetHeight === self.displayedImageHeight) return self.loopId = false

        if (self.targetHeight > self.displayedImageHeight) {
          self.displayedImageHeight += stepSize
          if (self.targetHeight < self.displayedImageHeight) self.displayedImageHeight = self.targetHeight
        } else {
          self.displayedImageHeight -= stepSize
          if (self.targetHeight > self.displayedImageHeight) self.displayedImageHeight = self.targetHeight
        }

        return self.loopId = requestAnimationFrame(loop)
      }

      setTimeout(function () {
        if (self.loopId) self.loopId = cancelAnimationFrame(self.loopId)
        loop()
      }, delayBeforeTransition)
    },

    handleImageThumb: function (loopIndex) {
      var self = this
      var queryString = ".js--galleryItem" + loopIndex
      self.activeThumb = loopIndex
      self.$nextTick(function() {
        var loopedElement = self.$els.displayedImageWrapper.querySelector(queryString)

        if (!loopedElement) return console.error("could not find element: " + queryString)

        if($(loopedElement).hasClass('Magic360')){
          $(loopedElement).parents('.Magic360-container').first().show();
        } else {
          $('.Magic360-container').hide();
        }

        if (loopedElement.querySelector("iframe")) {
          self.transitionHeightTo(loopedElement.querySelector("iframe").offsetHeight)
        } else if (loopedElement.querySelector("img")) {
          self.transitionHeightTo(loopedElement.querySelector("img").offsetHeight)
        } else {
          console.error("Expected iframe or img element as child of " + queryString)
        }

        if(typeof gtmYTListeners !== 'undefined' && typeof YT !== 'undefined'){
            jQuery('.gallery__videoWrapper iframe').each(function(i, v){
                if(/youtube.com\/embed/.test($(this).attr('src'))){
                    gtmYTListeners.push(new YT.Player($(this)[0], {
                        events: {
                            onStateChange: onPlayerStateChange
                        }}));
                }
            });
        }
      })
    },

    isVisible: function(loopIndex) {
      var self = this
      return self.activeThumb === loopIndex
    },

  }
}
</script>
