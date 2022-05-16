$(document).on('ready', function(){

    $('#contactUs-form, #contactKeiserRep-form, #optOut-form, form[data-preventDuplicateSubmit="true"], .contentBlock form').on('submit', function(){
        $('input[type="submit"], button[type="submit"]', $(this)).prop('disabled',true).addClass('button--disabled').val('Submitting..');
    })

});


window.sproutFormValidation = {
    validate: function(inputName, inputType, containerType){
        var errorMessage = '';
        switch(inputType){
            case 'singleLine':
                var $elem = $('input[name="'+ inputName +'"]');
                if(!$elem.val()){
                    errorMessage = 'This field cannot be left blank';
                    $elem.parents('.field.singleline').first().after(this.generateError(inputName, errorMessage));
                }
                break;
            case 'dropdown':
                var $elem = $('select[name="'+ inputName +'"]');
                if(!$elem.val()){
                    errorMessage = 'Please select one of the options above';
                    $elem.parents('.field.dropdown').first().after(this.generateError(inputName, errorMessage));
                }
                break;
            case 'checkboxes':
                var $elem = $('input[name="'+ inputName +'"]');
                var $checked = $elem.filter(':checked');
                if(!$checked.length){
                    errorMessage = 'Please select at least one of the options above';
                    $elem.first().parents('.field.checkboxes').first().after(this.generateError(inputName, errorMessage));
                }
                break;
        }
        if(errorMessage){
            window.smoothScroll($('#' + this.generateErrorContainerID()).offset().top, containerType);
            return false;
        }
        return true;
    },

    generateError: function(inputName, errorMessage){
        return `
            <div class='userLocationField__errorContainer' id='${this.generateErrorContainerID()}'>
                    ${errorMessage}
            </div>
        `;
    },

    generateErrorContainerID: function(){
        return 'sproutInputValidationError';
    },

    validateFields: function(fieldMap){
        $('#' + this.generateErrorContainerID()).remove();
        var validForm = true;
        var self = this;
        $.each(fieldMap, function(i,v){
            if(!self.validate(v['inputName'], v['inputType'], v['containerType'])){
                validForm = false;
                return false;
            }
        });
        if(!validForm){
            return false;
        }
        return true;
    },

    initialiseRecaptcha: function(){
        if(typeof sproutFormsRecaptchaOnloadCallback !== 'undefined'){
            sproutFormsRecaptchaOnloadCallback();
            if($('textarea[name="g-recaptcha-response"]').length){
                $('textarea[name="g-recaptcha-response"]').attr('required',true);
                $('textarea[name="g-recaptcha-response"]')[0].setCustomValidity('Kindly check this box to help us prevent spam');
            }
        }
    }
};
