<script>
  "use strict"

  var trackEvent = require('./track-event.js')


  module.exports = {

    props: {
      formSubmitLabel: {
        type: String,
        required: false,
        default: ""
      },

      formSubmitAction: {
        type: String,
        required: false,
        default: "Submit"        
      },

      downloadTrackLabel: {
        type: String,
        required: false,
        default: ""
      }
    },

    data: function () {
      return {
        hasSubmittedForm: false,
        hasFormErrors: false,
      }
    },

    methods: {
      handleFormDownload: function (e) {
        var self = this

        if (!self.downloadTrackLabel) {
          throw new TypeError("download-track-label prop required when using handleFormDownload method")
        }

        var $el = $(e.target)
        var targetUrl = $el.attr("href")
        function navigateToTarget() {
          window.location = targetUrl
        }

        trackEvent("brochure", "download", self.downloadTrackLabel, navigateToTarget, 1)
      },

      handleFormSubmit: function (e) {
        var self = this
        if (self.hasSubmittedForm) { return }
        if (!self.$els.form) { return console.error("form element not found") }

        self.hasFormErrors = !self.$els.form.checkValidity()
        if (self.hasFormErrors) { return }

        // If the form fails to submit then the user still gets the brochure
        // because this isn't a checkout flow.
        self.hasSubmittedForm = true

        var formData = $(self.$els.form).serialize()
        $.ajax({
          type: "POST",
          url: "sprout-forms/entries/save-entry",
          data: formData,
          dataType: "json",
          encode: true,
          headers: {
            'Accept': 'application/json'
          },
        }).then(function (data) {
          if (!data.success) {
            console.error("Form failed to submit: ", data, formData)
            return
          }
          if (self.formSubmitLabel) {
            trackEvent("Form", self.formSubmitAction, self.formSubmitLabel, function () {}, 1)
          }
        }, function (err) {
          console.error("Form failed to submit: ", err)
        })

      },
    },
    
  }
</script>
