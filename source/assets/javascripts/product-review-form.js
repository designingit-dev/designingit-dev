$(document).ready(function(){
    if($('.productReview').length){
        function getURLQueryParam(key){
            key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // escape RegEx meta chars
            var match = location.search.match(new RegExp("[?&]"+key+"=([^&]+)(&|$)"));
            return match && decodeURIComponent(match[1].replace(/\+/g, " "));
        }
        var message = getURLQueryParam('message');
        if(message && message == 'thankyouforsubmitting'){
            $('#productReviewThankYouButton').click();
        }

        function checkIfTermsAccepted(){
            var $termsCheckbox = $('#fields-termsConditions-1');
            if($termsCheckbox.is(":checked")){
                $('#fields-termsConditions-field ul.errors').remove();
                return true;
            } else {
                $('#fields-termsConditions-field ul.errors').remove();
                $('#fields-termsConditions-field').append('' +
                '<ul class="errors">' +
                '<li>You must agree with our terms and conditions</li>' +
                '</ul>');
            }
            return false;
        }

        function updateCheckboxClasses($elem){
            var $checkBoxDiv = $('div.productReview__checkbox__input[data-for="' + $elem.attr('id') +'"]');
            if($elem.is(':checked')){
                $checkBoxDiv.addClass('productReview__checkbox__input__checked');
            } else {
                $checkBoxDiv.removeClass('productReview__checkbox__input__checked');
            }
        }

        $('.productReview__checkbox__input, .productReview__checkbox__label').on('click', function(){
            var checkboxId = '#' + $(this).attr('data-for');
            var $checkbox = $(checkboxId);
            var $checkboxNegative = $(checkboxId + '-negative');
            if($checkbox.is(':checked')){
                $checkbox.prop('checked', false);
                $checkboxNegative.prop('disabled', false);
            } else {
                $checkbox.prop('checked', true);
                $checkboxNegative.attr('disabled', true);
            }
            updateCheckboxClasses($checkbox);
            if($(this).attr('data-for') == 'fields-termsConditions-1'){
                checkIfTermsAccepted();
            }
        });

        function updateRadioButtonClasses($elem){
            $('.productReview__radio__input[data-input="'+ $elem.attr('name') + '"]').removeClass('productReview__radio__input__checked');
            $('.productReview__radio__input[data-for="'+ $elem.attr('id') +'"]').addClass('productReview__radio__input__checked');
        }

        $('.productReview__radio__input, .productReview__radio__label').on('click', function(){
            var $radioButton = $('#' + $(this).attr('data-for'));
            $('input[type="radio"][name="'+ $radioButton.attr('name') + '"]').prop('checked', false);
            $radioButton.prop('checked', true);
            updateRadioButtonClasses($radioButton);
        });

        $('.rating__star').on('click', function(){
            var rating = parseInt($(this).attr('data-value'));
            var $stars = $('.rating__star');
            $stars.removeClass('active');
            $stars.filter(function(){
                return parseInt($(this).attr('data-value')) <= rating;
            }).addClass('active');
            $('#fields-rating').val(rating);
            $('.productReview__error').text('').hide();
        });

        $('#fields-email-field').append(
            '<p class="instructions text--greyLight text--small margin--top--narrow margin--bottom--none">' +
                'We will only use this to mark your review as a verified purchase and to follow up if we have any questions about your review. We will not sell your information to any third party.' +
            '</p>');

        $('#fields-reviewImage-field').append(
            '<span id="reviewImageFilename" class="text--greyLight text--small"></span>' +
            '<div id="reviewImagePreviewContainer"></div>'
        );

        $('#fields-reviewImage').on('change', function(){
            $('#reviewImageFilename').text($(this).val().split('\\').pop());
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#reviewImagePreviewContainer').html('<img src="'+ e.target.result +'" />');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        $('#productReview__form').on('submit', function(e){
            if(!$('#fields-rating').val()){
                $('.productReview__error').text('Please select a rating').show();
                $(document).scrollTop(100);
                e.preventDefault();
                return false;
            } else if(!checkIfTermsAccepted()){
                e.preventDefault();
                return false;
            }
            $('#productReview__form input[type="submit"]').addClass('button--disabled').val('Submitting..');
            return true;
        });

        if(typeof entryContent !== 'undefined'){
            $('span.rating__star[data-value="'+ entryContent.rating + '"]').click();
            $('#fields-reviewHeadline').val(entryContent.reviewHeadline);
            $('#fields-comments').text(entryContent.comments);
            if(entryContent.recommend && entryContent.recommend[0].selected){
                $('div.productReview__checkbox__input[data-for="fields-recommend-1"]').click();
            }
            $('#fields-nickname').val(entryContent.nickname);
            $('#fields-email').val(entryContent.email);
            $('#fields-city').val(entryContent.city);
            $('#fields-state').val(entryContent.state);
            $('#fields-zipCode').val(entryContent.zipCode);
            if(entryContent.age && entryContent.age.selected){
                $('#fields-age option[value="' + entryContent.age.value + '"]').prop('selected','selected');
            }
            if(entryContent.gender && entryContent.gender.selected){
                var genderId = $('input[name="fields[gender]"][value="'+ entryContent.gender.value +'"]').attr('id');
                $('div.productReview__radio__input[data-for="'+ genderId +'"]').click();
            }
            if(entryContent.newsletterSignup.length > 0 && entryContent.newsletterSignup[0].selected && entryContent.newsletterSignup[0].value === 'true'){
                $('div.productReview__checkbox__input[data-for="fields-newsletterSignup-1"]').click();
            }
            if(entryContent.reviewImage){
                $('#reviewImagePreviewContainer').html('<img src="'+ entryContent.reviewImage +'" />');
            }
        }

    }
});
