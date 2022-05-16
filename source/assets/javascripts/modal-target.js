'use strict'

// This darling bit of code moves the `vue-modal` html
// elements into a div that lives outside of the main
// site container. This is to prevent interference between
// our modals and our site navigation.
function moveVueModals() {
  var $target = $('#js--vueModalTarget')
  var $modals = $('vue-modal')
  $modals.detach()

  $target.append($modals)
}

moveVueModals()

module.exports = []
