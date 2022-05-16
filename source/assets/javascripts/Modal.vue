<template>
  <div class="modal">
    <div class="modal__mask"
         v-if="show"
         transition="modal"
         v-on:click="closeModal">
      <div class="modal__wrapper" v-on:click.stop>
        <button class="modal__close" v-if="showModalCloseButton" v-on:click="closeModal">X</button>

        <div class="modal__header scale--xLarge">
          <slot name="header">
          </slot>
        </div>

        <div class="modal__body">
          <slot name="body">
          </slot>
        </div>

        <div class="modal__footer">
          <slot name="footer">
            <button class="modal__defaultButton"
              v-on:click="closeModal">
              OK
            </button>
          </slot>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
var setupTrackForm = require('./track-form.js')

module.exports = {

 props: {

   activeModal: {
     type: String,
     required: true,
     twoWay: false
   },

   name: {
     type: String,
     required: true
   },

   showModalCloseButton: {
     type: Boolean,
     required: false,
     default: false
   },

   closeModalOnOutsideClick: {
      type: Boolean,
      required: false,
      default: true
   },

  },

  watch: {
    activeModal(val) {
      var self = this
      if (!val) { return }

      self.$nextTick(function () {
        setupTrackForm(self.$el)
        window.userLocationField.show()
        window.geoLocationField.fill()
        window.pageTitleField.fill()
        window.campaignTrackingField.fill()
        if(typeof gtmYTListeners !== 'undefined' && typeof YT !== 'undefined'){
            jQuery('.modal__wrapper iframe').each(function(i, v){
                if(/youtube.com\/embed/.test($(this).attr('src'))){
                    var currentVideo = new window.URI($(this).attr('src'));
                    var currentVideoId = currentVideo.filename();
                    addVideoToYTTracker(currentVideoId, $(this)[0]);
                }
            });
        }
      })
    },
  },

  computed: {

    show: function () {
      return this.activeModal === this.name
    }

  },

  methods: {

    closeModal: function () {
      this.$dispatch('close-modal', this.name)
      if(typeof gtmYTListeners !== 'undefined' && typeof YT !== 'undefined'){
              $.each(gtmYTListeners, function(i, v){
                  if($(v.getIframe()).parents().filter('div.modal__wrapper').length > 0){
                      gtmYTListeners.splice(i,1);
                  }
              });
      }
    }

  }
}
</script>
