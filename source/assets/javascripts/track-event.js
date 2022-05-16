'use strict'

module.exports = function (category, action, label, callback, value) {
  var isExecutingCallback = false

  function executeCallback() {
    if (isExecutingCallback || !callback) { return }
    isExecutingCallback = true
    callback()
  }

  if (!window.ga) { return executeCallback() }

  if (value === undefined) {
    value = null
  }

  var options

  if (callback) {
    options = {
      hitCallback: executeCallback
    }
  }

  // Sometimes hitCallback doesn't fire, so we use setTimeout as a fallback
  setTimeout(executeCallback, 250)

  ga('keisergtm.send', 'event', category, action, label, value, options)
}
