'use strict'

var trackEvent = require('./track-event.js')

var category = 'Form'

function detectSpammer(form) {
  var $honeyPotEls = $(form).find('input[id*=beesknees]')
  var spammedInputs = $honeyPotEls
  .toArray()
  .filter(honeyPotInput => honeyPotInput.value)

  return spammedInputs[0] !== undefined
}

function handleSproutSubmit(event) {
  var $el = $(event.target)

  if (detectSpammer($el)) { return }

  // Default the action name in case future us removes the
  // form id
  var action = location.pathname

  // Sproutform generated forms have the id in the form
  // myCoolFormSlug-form
  // Assuming the id exists, grab evertything before the -
  var elId = $el.attr('id')
  if (elId) {
    action = elId.split('-')[0]
  }

  // Try getting the Keiser Rep's slug if we're in that form
  var eventVal = $el.find('.js--repSlug').first().val()

  // Grab the inquiry option if we are not in the Keiser Rep Form
  // The eventVal will be undefined for everything but the contact forms
  // and that is fine
  if (!eventVal) {
    eventVal = $el.find('#fields-pleaseSelectAnInquiryOption option:selected').text()
  }

  // Pause the form submit to give ga time to send
  event.preventDefault()

  // Prevent duplicate tracking events by turning off the event handler
  $el.off()

  trackEvent(category, action, eventVal, function () {

    // Now that we know that the ga event happened, submit
    $el.submit()
  })
}



function bootstrap(scopingEl) {
  var queryString = 'form[class*="form"]'
  var $el = $(queryString)
  if (scopingEl) {
    $el = $(scopingEl).find(queryString)
  }

  // Attach our submit handler to every sproutforms style form
  $el.on('submit', handleSproutSubmit)
}

module.exports = bootstrap
