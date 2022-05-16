<script>
  "use strict"

  var job = require('./job-title.js')

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
        jobTitle: job.title,
        hasSubmittedForm: false,
        hasFormErrors: false,
      }
    },

    ready: function () {
      var uploadField = this.$el.querySelector('#fields-attachResume');
      // console.log('uploadField', uploadField);

      // accept="image/gif, image/jpeg"
      // DOC, DOCX, RTF, PDF

      if (uploadField != null) {
        var fileFormats = [
          '.doc',
          '.docx',
          '.rtf',
          '.pdf',
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'application/rtf',
          'application/x-rtf',
          'text/richtext',
          'application/pdf'
        ];
        uploadField.setAttribute('accept', fileFormats.join(','));
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

        // var formData = $(self.$els.form).serialize()
        var formData = new FormData(self.$els.form);

        $.ajax({
          type: "POST",
          url: "sprout-forms/entries/save-entry",
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          encode: true,
        }).then(function (data) {
          if (!data.success) {
            console.error("Form failed to submit: ", data, formData)
            // return
          }
          // if (self.formSubmitLabel) {
          //   trackEvent("Form", "Submit", self.formSubmitLabel, function () {}, 1)
          // }
        }, function (err) {
          console.error("Form failed to submit: ", err)
        })

      },
    },
    
  }
</script>
