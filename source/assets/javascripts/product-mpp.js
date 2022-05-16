$(document).ready(function(){

    $(".product__offers__button").on("click", function() {
        $('#' + $(this).data('toggle')).slideToggle("fast");
    });

    $('.keiser__benefits__modal__anchor').on('click', function(){
        var modalName = $(this).attr('id').replace('__anchor', '');
        window.vueInst.openModal(modalName);
    });

    $('#product__offers__space__design__button').on('click', function(){
        window.vueInst.openCommercialEnquiryModal('strengthMppTalkToADesignPro');
        window.vueInst.trackFormAnchor('Talk to a Design pro', 'talkToADesignPro');
    })

})