'use strict'

// populates a field input with the set value in query string
// e.g. ?fields-yourName=Bob%20Smith&fields-email=example@example.com
// selects by the field inputs ID $('#fields-yourName')

$(function () {
	// if there is a URL query
	if (window.location.href.indexOf('?') != -1) {
		var urlParams = getUrlVars();
		$.each(urlParams, function(key, param) {
			var paramVal = urlParams[param]
			// target by element field id and set value
			$('#' + param).val(decodeURIComponent(paramVal))
		})
	}
})

// Read a page's GET URL variables and return them as an associative array.
function getUrlVars() {
	var vars = [], hash
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&')
	for(var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=')
		vars.push(hash[0])
		vars[hash[0]] = hash[1]
	}
	return vars
}