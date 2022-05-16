<script>
var Modal = require ('./Modal.vue')

module.exports = {
  extends: Modal,
  
  ready: function(){
    this.load()
  },

  methods: {
    load: function() {
      this.$parent.activeModal = "checkoutError"
    },
  }
}
</script>
