"use strict"

function filterNorthAmericaReps(data) {

	// filter down to north america reps only
	var northAmericaReps = $.grep(data.data, function(val, i) {
		return (val.region == "north-america");
	});

	// remove unneeded keys from object
	// remove duplicates
	var reps = [];
	var lookup = {};

	for (var key in northAmericaReps) {
		delete northAmericaReps[key].title;
		delete northAmericaReps[key].region;
		delete northAmericaReps[key].slug;

		lookup[northAmericaReps[key].rep] = northAmericaReps[key];

	}

	for (key in lookup) {
		reps.push(lookup[key]);
	}

	var repOptions = '<option value="">Select a Sales Rep</option>';
	for (var i=0;i<reps.length;i++) {
		repOptions += '<option value="'+ reps[i].repNiceName +'">'+ reps[i].repNiceName +'</option>';
	}

	return repOptions;
}

$(function() {

	// cancel if either form does not exiset
	if (!$('#deliveryQuestionnaireResidential-form').length && !$('#deliveryQuestionnaireCommercial-form').length) {
		return;
	}

	// territories api
	var territoriesService = require('./territories-service');
	
	var salesRepField = '#fields-salesRep';

	// add class to div wrapper
	$('#fields-salesRep-field').addClass('sproutfields_select');

	$(salesRepField).replaceWith('<select id="fields-salesRep" class="salesRep" name="fields[salesRep]" required>');

	var repData = territoriesService.fetch().then(function(data) {
		var repOptions = filterNorthAmericaReps(data);		
		$(salesRepField).append(repOptions);
	});
});
