$('#globalDistributorNetwork__form__userLocationField__submit').on('click', function(e){
    e.preventDefault();
    window.userLocationField.validateSubmit(
        $('#globalDistributorNetwork__form'),
        $('#globalDistributorNetwork__form__userLocationField__zip'),
        $('#globalDistributorNetwork__form__userLocationField__country'),
        $(this),
        function(){
            $('#globalDistributorNetwork__form__userLocationField').addClass('is--hidden');
            $('#globalDistributorNetwork__form__contactFields').removeClass('is--hidden');
            var locationComponents = window.userLocationField.getCombinedFieldValue('globalDistributorNetwork__form', 'userLocation');
            var countryName = window.userLocationField.getCountryNameFromISO(locationComponents['countryISO']);
            if(countryName !== 'United States of America'){
                $('#globalDistributorNetwork__form__countryTitle').text(countryName);
            }
            window.userLocationField.findKeiserRep(
                locationComponents['countryISO'],
                (typeof locationComponents['zip'] !== 'undefined' ? locationComponents['zip'] : null),
                null,
                null,
                function(){
                    jQuery('.globalDistributorNetwork__repContactDetails__repName').html('');
                    jQuery('.globalDistributorNetwork__repContactDetails__repFirstName').text('');
                    jQuery('.globalDistributorNetwork__repContactDetails__repContact').html('<img src="/assets/images/tail-spin-red.svg" class="margin--right--half loading--tailspin" /> Finding your representative...');
                    jQuery('.globalDistributorNetwork__repContactDetails__repWebsite').html('');
                },
                function(response){
                    if(response.status === 'success'){
                        jQuery('.globalDistributorNetwork__repContactDetails__repName').text(response.repName);
                        jQuery('.globalDistributorNetwork__repContactDetails__repFirstName').text('EMAIL ' + response.repName.split(' ')[0]);
                        jQuery('.globalDistributorNetwork__repContactDetails__repContact').html(response.repContact);
                        if(typeof response.repWebsite !== 'undefined'){
                            jQuery('.globalDistributorNetwork__repContactDetails__repWebsite').html('Visit <a data-countryISO="'+ locationComponents['countryISO'] +'" href="' + response.repWebsite + '">'+ response.repWebsite +'</a>');
                        }
                        jQuery('#globalDistributorNetwork__form input[name="fields[repEmail]"]').val(response.repEmail);
                        jQuery('#globalDistributorNetwork__form input[name="fields[repVPEmail]"]').val(response.repVPEmail);
                        jQuery('#globalDistributorNetwork__form input[name="redirect"]').val(response.redirect);
                    }
                },
                null,
            );
        }
    )
});

$(document).on('ready', function(){
    if($('#globalDistributorNetwork__form').length !== 0){
        switch(window.homeCountryISO){
            case 'US':
                if($.inArray(window.countryISO, ['CA', 'US']) === -1){
                    $('#globalDistributorNetwork__form__userLocationField__country').val(window.userLocationField.getCountryNameFromISO(window.countryISO));
                }
                break;
            case 'UK':
                if($.inArray(window.countryISO, ['GB', 'US']) === -1){
                    $('#globalDistributorNetwork__form__userLocationField__country').val(window.userLocationField.getCountryNameFromISO(window.countryISO));
                }
                break;
        }
    }

    $(document).on('click', function(e){
        if($(e.target).parent().hasClass('globalDistributorNetwork__repContactDetails__repWebsite')){
            ga('keisergtm.send', 'event', 'Distributor Redirect', 'Visit Distributor via Link', $(e.target).attr('data-countryISO'));
        }
    })
});
