'use strict'

var data = { };

module.exports = {
	log(attrs) {

		data['action'] = 'keiser-ajax-logging/keiser-ajax-logging/ajax-craft-logging';

		// Populate CSRF field name and token.
		data[window.csrfTokenName] = window.csrfTokenValue;

		// add attributes to data object
		$.extend(true, data, attrs);
		
		this.post();
	},

	post() {
		$.ajax({
			method: 'POST',
			url: '/',
			data: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'Accept': 'application/json'
			}
		})
  }
}