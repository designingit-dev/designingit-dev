var duration = 300

module.exports = {
  css: false,

  enter: function (el, done) {
    var $el = $(el)
    $el.toggle(false).slideDown({duration: duration, complete: done})
  },

  leave: function (el, done) {
    var $el = $(el)
    $el.slideUp({duration: duration, complete: done})
  }

}
