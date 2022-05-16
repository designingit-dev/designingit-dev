<script>
  "use strict"

  var trackEvent = require('./track-event.js')


  module.exports = {

    props: {
      formSubmitLabel: {
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
          url: "/",
          data: formData,
          encode: true,
        }).then(function (data) {
          if (!data.success) {
            console.error("Form failed to submit: ", data, formData)
            return
          }
          if (self.formSubmitLabel) {
            // trackEvent("Form", "Submit", self.formSubmitLabel, function () {}, 1)
            alert('track submit event');
          }
        }, function (err) {
          console.error("Form failed to submit: ", err)
        })
      },
    },
    
  }
</script>
