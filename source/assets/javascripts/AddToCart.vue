<script>
  "use strict"

  var trackEvent = require('./track-event.js')

  module.exports = {
    props: {
      productName: {
        type: String,
        required: false,
        default: ""
      },

      price: {
        type: String,
        required: false,
        default: ""
      },

      sku: {
        type: String,
        required: false,
        default: ""
      },

      category: {
        type: String,
        required: false,
        default: ""
      },

      productUrl: {
        type: String,
        required: false,
        default: ""
      },

      productImageUrl: {
        type: String,
        required: false,
        default: ""
      }
    },

    data: function () {
      return {
        quantity: 0,
      }
    },

    watch: {
      // whenever quantity changes, this function will run
      quantity: function (quantity) {
        this.quantity = quantity
      }
    },

    methods: {
      trackAddToCart: function() {
        ga('keisergtm.ec:addProduct', {'id': this.sku,
          'name': this.productName,
          'category': this.category,
          'price': this.price,
          'quantity': this.quantity
        });
        ga('keisergtm.ec:setAction', 'add');

        ga('keisergtm.send', 'event', 'ecommerce', 'add to cart', {'nonInteraction': true});

        rudderanalytics.track('Product Added',{
          product_id: this.sku,
          sku: this.sku,
          category: this.category,
          name: this.productName,
          brand: 'Keiser',
          price: this.price,
          quantity: this.quantity,
          url: this.productUrl,
          image_url: this.productImageUrl,
          site: window.location.hostname
        });
      },
    },

  }
</script>
