window.siteWideInfoBanner = {

    init: function(){
        if($('.siteWideInfoBanner').length){
            $('.siteWideInfoBanner__close').on('click', function(){
                window.siteWideInfoBanner.close($(this).data('banner-id'), $(this).data('is-absolute-top-banner'));
            });
        }
    },

    close: function(bannerId, isAbsoluteTopBanner){
        $('.siteWideInfoBanner[data-banner-id='+ bannerId +']').hide();
        if(isAbsoluteTopBanner){
            $(document).trigger('absoluteTopBannerClosed');
        }
        $.ajax({
            url: '/',
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            data: {
                'action': '/keiser-contact-helpers/keiser-contact-helpers/close-banner',
                'bannerId': bannerId
            }
        });
    }

};
