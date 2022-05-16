<script>
"use strict"

var Modal = require ('./Modal.vue')
var addressService = require('./address-service')

module.exports = {
  props: {

    shippingAddressId: {
      type: Number,
      required: false
    },
    billingAddressId: {
      type: Number,
      required: false
    }
  },

  data: function () {
    return {
      isValidatedAddress: false,
      isSameAddress: false,
      activeModal: '',
      addresses: {}
    }
  },

  ready: function () {
    if (this.shippingAddressId) {
      if (this.shippingAddressId === this.billingAddressId) {
        this.isSameAddress = true
      }
    }
  },

  methods: {
    requestManualReview: function() {
      var self = this;

      // Add custom field to trigger manual review
      self.$form.append('<input type="hidden" name="fields[requiresManualReview]" value="1" />');

      // Close modal
      self.closeModal();
      window.checkout.displayShippingOptions();
    },

    validateShippingAddress: function() {
      var self = this

      self.addresses = {}
      self.errors = {}

      var shippingAddress = this.$form.find('.addressBox.shippingAddress');
      var validAddress = true

      this.disableButton(true)

      var promises = [];

      var type = $(shippingAddress).attr('data-type')
      var $addressObj = $(shippingAddress).find('input, select').serializeArray()
      var address = self.createValidatableAddressObject($addressObj)
      self.addresses[type] = address

      if(address.country == 'US'){
        var def = new $.Deferred();
        addressService.get(address).then(validatedAddr => {
          def.resolve(validAddress);
        },
        (error, textStatus, errorThrown) => {
          validAddress = false
          self.errors['Error'] = "We were unable to validate the address you entered. Please review the address to ensure it has been entered correctly. If the issue persists, please call our in-home specialist at 559-256-8020. If you're sure the address is correct, you can proceed by requesting a \"manual review\" and one of our specialists will contact you to confirm your address details."
          // Logging
          if(error.status > 400 && error.status < 600){
            craft.log({ 'log': [
              {
                'type': 'error',
                'plugin': 'KeiserAddressAPI',
                'message': 'Request to AWS Keiser Address API failed' + '\nResponse Code: ' + error.status + '\nResponse Body: ' + error.statusText + '\nQuery: ' + $.param(address)},
              ]});
          }

          // Return false to trigger modal
          def.resolve(validAddress);
        })
        promises.push(def);
      }

      $.when.apply($, promises).then(function(){
        if (!validAddress) {
          // Display modal
          if (Object.keys(self.errors).length > 0) {
            self.activeModal = 'addressError'
          }
        } else {
          window.checkout.displayShippingOptions();
        }
      })
    },

    handleSubmit: function(evt) {
      var self = this;
      self.$form = $('#checkout-step-1');

      if (self.$form[0].checkValidity()) {
        self.validateShippingAddress()
      } else {
          self.$form[0].reportValidity()
      }
      evt.preventDefault()
      return false
    },

    createValidatableAddressObject: function(address) {
      var self = this
      var validatableAddress = {};

      /*
       * Map field names for address validation API
       */
      // address1 = street1
      // address2 = street2
      // city = city
      // stateValue = state (name)
      // zipCode = postalCode
      // countryId = country (name)

      $.each(address, function(i, field){
        var fieldName = field.name.replace(/(^.*\[|\].*$)/g, '');

        switch(fieldName) {
          case "address1":
            validatableAddress['street1'] = field.value
            break;

          case "address2":
            validatableAddress['street2'] = field.value
            break;

          case "city":
            validatableAddress['city'] = field.value
            break;

          case "stateValue":
            validatableAddress['state'] = self.$form.find('[name="'+field.name+'"] option:selected').text()
            break;

          case "zipCode":
            validatableAddress['postalcode'] = field.value
            break;

          case "countryId":
            validatableAddress['country'] = self.$form.find('[name="'+field.name+'"] option:selected').attr('data-country-abbr')
            break;
        }
      })

      return validatableAddress;
    },

    disableButton: function(disable) {
      if (this.$form) {
        var $el = $('#checkout-step-1-submit');

        if (disable) {
          $el.attr('data-default-text', $el.text())
          $el.text('Validating addresses...')
          $el.prop( "disabled", true ).addClass('button--disabled')
        } else {
          $el.text($el.attr('data-default-text'))
          $el.prop( "disabled", false ).removeClass('button--disabled')
        }
      }
    },

    closeModal: function() {
      this.disableButton(false)
      this.activeModal = ''
    }
  },

  components: {
    vueAddressModal: Modal
  }
}
</script>
