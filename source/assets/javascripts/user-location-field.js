window.userLocationField = {

    show: function(){
        var successfulIterations = 0;
        if($('.userLocationField').length !== 0){
            var self = this;
            $('.userLocationField').each(function(){
                if($(this).html() === ''){
                    var formID = $(this).parents('form').first().attr('id');
                    $(this).attr('data-formID', formID);
                    if(window.countryISO === 'US'){
                        self.setCombinedFieldValue(formID, $(this).attr('data-combinedFieldName'), self.getCountryNameFromISO(window.countryISO));
                        if($('#globalDistributorNetwork__form').length !== 0){
                            $(this).html(self.getCountryField(formID, $(this).attr('data-required')));
                            self.activateCountryDropdown($('#' + formID + '__userLocationField__country'));
                            self.monitorCountryDropdown(formID, $('#' + formID + '__userLocationField__country'));
                        } else {
                            $(this).html(self.getZipField(formID, $(this).attr('data-required')));
                        }
                    } else {
                        $(this).html(self.getCountryField(formID, $(this).attr('data-required')));
                        self.activateCountryDropdown($('#' + formID + '__userLocationField__country'));
                        self.monitorCountryDropdown(formID, $('#' + formID + '__userLocationField__country'));
                    }
                    successfulIterations++;
                }
            });
            if(successfulIterations > 0){
                this.handleSubmit();
            }
        }
    },

    handleSubmit: function(){
        var self = this;
        $('form').on('submit', function(e){
            if($('.userLocationField', $(this)).length !== 0 && $(this).attr('data-userLocationValidated') === '' || $(this).attr('data-userLocationValidated') !== 'true'){
                e.preventDefault();
                var $form = $(this);
                var $zipField = $('#' + $form.attr('id') + '__userLocationField__zip');
                var $countryField = $('#' + $form.attr('id') + '__userLocationField__country');
                self.validateSubmit($form, $zipField, $countryField);
            }
        });
    },

    validateSubmit: function($form, $zipField, $countryField, $submitButton, successHandler){
        if($zipField.length !== 0){
            var $errorContainer = $('#' + $form.attr('id') + '__userLocationField__zip__errorContainer');
            this.validateZipField($form, $zipField, $errorContainer, (typeof $submitButton !== 'undefined' ? $submitButton : null), (typeof successHandler !== 'undefined' ? successHandler : null));
        } else if($countryField.length !== 0){
            var $errorContainer = $('#' + $form.attr('id') + '__userLocationField__country__errorContainer');
            this.validateCountryField($form, $countryField, $errorContainer, (typeof successHandler !== 'undefined' ? successHandler : null));
        }
    },

    validateZipField: function($form, $field, $errorContainer, $submitButton, successHandler){
        if(typeof $submitButton === 'undefined' || $submitButton === null ){
            $submitButton = $('input[type="submit"]', $form);
        }
        var submitButtonValue = $submitButton.val();
        var self = this;
        if($field.val() === '' && $field.hasAttr('required')){
            this.displayError($errorContainer)
        } else {
            this.validateUSZipCode(
                $field.val(),
                function(){
                    $submitButton.addClass('button--disabled').val('Verifying...');
                },
                function(response){
                    if(response.status == 'success'){
                        var $userLocationField = $field.parents('.userLocationField').first();
                        var $countryField = $('#' + $form.attr('id') + '__userLocationField__country');
                        var countryName = self.getCountryNameFromISO(window.countryISO);
                        if($countryField.length !== 0 && $countryField.val() !== '' && self.validateCountryName($countryField.val())){
                            countryName = $countryField.val();
                        }
                        self.setCombinedFieldValue($form.attr('id'), $userLocationField.attr('data-combinedFieldName'), countryName, $field.val());
                        self.validationSuccessful($form, (typeof successHandler !== 'undefined' ? successHandler : null));
                    } else {
                        self.displayError($errorContainer);
                        $submitButton.removeClass('button--disabled').val(submitButtonValue);
                    }
                },
                function(){},
                function(){},
                $errorContainer
            )
        }
    },

    validateCountryField: function($form, $field, $errorContainer, submitOnSuccess){
        if($field.val() === '' && $field.hasAttr('required')){
            this.displayError($errorContainer)
        } else {
            if(this.validateCountryName($field.val())){
                this.setCombinedFieldValue($form.attr('id'), $field.parents('.userLocationField').first().attr('data-combinedFieldName'), $field.val());
                this.validationSuccessful($form, (typeof submitOnSuccess !== 'undefined' ? submitOnSuccess : true));
            } else {
                this.displayError($errorContainer);
            }
        }
    },

    validateCountryName: function(countryName){
        if(typeof window.countriesList !== 'undefined' && typeof window.countriesList[countryName] !== 'undefined'){
            return true;
        }
        return false;
    },

    activateCountryDropdown: function($countryField){
        if(typeof window.countriesList !== 'undefined'){

            if($('#globalDistributorNetwork__form').length !== 0){
                if(window.homeCountryISO && window.homeCountryISO == 'UK'){
                    delete window.countriesList['United Kingdom of Great Britain and Northern Ireland'];
                } else {
                    delete window.countriesList['United States of America'];
                    delete window.countriesList['United States Minor Outlying Islands'];
                    delete window.countriesList['Canada'];
                }
            }

            $countryField.typeahead({
                'source': {
                    data: Object.keys(window.countriesList)
                }
            });
        }
    },

    monitorCountryDropdown: function(formID, $countryField){
        var self = this;
        $countryField.on('keyup change mouseout mousein focusin focusout', function(){
            if($(this).val() === 'United States of America'){
                self.addZipField(formID);
            } else {
                self.removeZipField(formID);
            }
        });
    },

    validateUSZipCode: function(zip, beforeSend, success, error, complete, $errorContainer){
        if(zip === ''){
            $errorContainer.removeClass('is--hidden');
        } else {
            var data = {
                'action': 'keiser-contact-helpers/keiser-contact-helpers/validate-u-s-zip-code',
                'zip': zip
            };
            jQuery.ajax({
                url: '/',
                method: 'POST',
                data: data,
                headers: {
                    'Accept': 'application/json'
                },
                beforeSend: beforeSend,
                success: success,
                error: error,
                complete: complete
            });
        }
    },

    validationSuccessful: function($form, successHandler){
        $form.attr('data-userLocationValidated', 'true');
        if(typeof successHandler === 'undefined' || !successHandler){
            var $submitButton = $('input[type="submit"]', $form);
            $submitButton.addClass('button--disabled').val('Submitting');
            $form.submit();
        } else {
            successHandler();
        }
    },

    displayError: function($errorContainer){
        $errorContainer.removeClass('is--hidden');
    },

    hideError: function($errorContainer){
        $errorContainer.addClass('is--hidden');
    },

    switchToCountryField: function(formID){
        var $userLocationField = $('.userLocationField', $('#' + formID));
        var $countryField = $('#' + formID + '__userLocationField__country');
        if($countryField.length === 0){
            $userLocationField.html(this.getCountryField(formID, $userLocationField.attr('data-required')));
            this.activateCountryDropdown($('#' + formID + '__userLocationField__country'));
            this.monitorCountryDropdown(formID, $('#' + formID + '__userLocationField__country'));
        } else {
            $($countryField).val('').focus();
        }
    },

    addZipField: function(formID){
        var $userLocationField = $('.userLocationField', $('#' + formID));
        var zipFieldID = formID + '__userLocationField__zip';
        if($('#' + zipFieldID).length === 0){
            $userLocationField.append(this.getZipField(formID, $userLocationField.attr('data-required')));
        }
    },

    removeZipField: function(formID){
        $('#' + formID + '__userLocationField__zip__container').remove();
    },

    setCombinedFieldValue: function(formID, combinedFieldName, countryName, zip){
        var value = countryName;
        if(typeof zip !== 'undefined'){
            value += ';' + zip;
        }
        $('input[name="fields[' + combinedFieldName + ']"]', $('#' + formID)).val(value);
    },

    getCombinedFieldValue: function(formID, combinedFieldName){
        var combinedFieldValue = $('input[name="fields[' + combinedFieldName + ']"]', $('#' + formID)).val();
        combinedFieldValue = combinedFieldValue.split(';');
        var result = {
            'countryISO': window.countriesList[combinedFieldValue[0]]
        };
        if(typeof combinedFieldValue[1] !== 'undefined'){
            result['zip'] = combinedFieldValue[1];
        }
        return result;
    },

    getZipField: function(formID, required){
        var fieldID = formID + '__userLocationField__zip';
        var html = '' +
            '<div id="'+ fieldID +'__container">' +
                '<div class="is--hidden userLocationField__errorContainer" id="'+ fieldID +'__errorContainer">' +
                '<p class="margin--bottom--none">Please provide a valid US ZIP Code</p>' +
                '</div>' +
                '<div class="field plaintext required">' +
                    '<div class="heading">' +
                        '<label for="'+ fieldID +'">Enter your ZIP Code (US Only)</label>' +
                    '</div>' +
                    '<div class="input">' +
                        '<input type="text" name="userLocationField__zip" id="'+ fieldID +'" maxlength="5"' + required + '>' +
                    '</div>' +
                '</div>' +
                '<div>' +
                    '<a class="userLocationField__notInUSLink" onclick="window.userLocationField.switchToCountryField(\''+ formID +'\')">Not in the U.S.? Click here.</a>' +
                '</div>' +
            '</div>';
        return html;
    },

    getCountryField: function(formID, required){
        var fieldID = formID + '__userLocationField__country';
        var html = '' +
            '<div class="is--hidden userLocationField__errorContainer" id="'+ fieldID +'__errorContainer">' +
                '<p class="margin--bottom--none">Please select from one of the countries from the dropdown</p>' +
            '</div>' +
            '<div class="field plaintext required">' +
                '<div class="heading">' +
                    '<label for="'+ fieldID +'">Country</label>' +
                '</div>' +
                '<div class="input">' +
                    '<div class="typeahead__container">' +
                        '<div class="typeahead__field">' +
                            '<span class="typeahead__query">' +
                                '<input class="js-typeahead" name="userLocationField__country" id="'+ fieldID +'" type="text" autocomplete="new-password" ' + required +'>' +
                            '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        return html;
    },

    getCountryNameFromISO: function(countryISO){
        var countryName = false;
        if(typeof window.countriesList !== 'undefined'){
            $.each(window.countriesList, function(i, v){
                if(v === countryISO){
                    countryName = i;
                }
            });
        }
        return countryName;
    },

    findKeiserRep: function(countryISO, zip, institutionType, interestedProducts, beforeSend, success, error){
        var data = {
            'action': 'keiser-contact-helpers/keiser-contact-helpers/find-keiser-rep',
            'countryISO': countryISO
        };
        if(typeof zip !== 'undefined' && zip){
            data['zip'] = zip;
        }
        if(typeof institutionType !== 'undefined' && institutionType){
            data['institutionType'] = institutionType;
        }
        if(typeof interestedProducts !== 'undefined' && interestedProducts){
            data['interestedProducts'] = interestedProducts;
        }
        if(typeof window.formName !== 'undefined' && window.formName.length){
            data['formName'] = window.formName;
        }
        if(typeof window.formAnchor !== 'undefined' && window.formAnchor.length){
            data['formAnchor'] = window.formAnchor;
        }
        jQuery.ajax({
            url: '/',
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            data: data,
            beforeSend: beforeSend,
            success: success,
            error: error
        });
    }

};
