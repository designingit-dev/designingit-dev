$(document).on('ready', function(){

    if($('.ihrsaGuarantee__contact').length > 0){

        var uri = new URI(window.location.href);
        var queryParams = URI.parseQuery(uri.query());
        if (typeof queryParams['message'] !== 'undefined' && queryParams['message'] == 'thankyouforsubmitting') {
            $('#ihrsaGuarantee__thankYouButton').click();
        }

        $('.ihrsaGuarantee__equipmentChoices__equipment').on('click', function(){
            $(this).toggleClass('active');
        });

        $('#ihrsaGuarantee__contact__form').on('submit', function(){
            var equipmentSelected = $('.ihrsaGuarantee__equipmentChoices__equipment.active').map(function(){
                return $(this).attr('data-name');
            });
            equipmentSelected = $.makeArray(equipmentSelected).join(', ');
            $('input[name="fields[equipmentSelected]"]').val(equipmentSelected);
            return true;
        })
    }

    if($('.ihrsaNewCatalog__contactForm').length > 0){
        var uri = new URI(window.location.href);
        var queryParams = URI.parseQuery(uri.query());
        if (typeof queryParams['message'] !== 'undefined' && queryParams['message'] == 'thankyouforsubmitting') {
            $('#ihrsaNewCatalog__thankYouButton').click();
        }
    }
});