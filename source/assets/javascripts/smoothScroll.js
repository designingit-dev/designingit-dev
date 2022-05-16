window.smoothScroll = function(offset, containerType){
    var stickyHeaderOffset = 0;
    if($('#js--stickyHeaderSpacer').length){
        stickyHeaderOffset = parseInt($('#js--stickyHeaderSpacer').css('height').replace('px',''));
    }
    if(typeof containerType == 'undefined'){
        $('html, body').animate({
            scrollTop: (offset - stickyHeaderOffset - 4)
        }, 500);
    } else if(containerType == 'modal'){
        $('.modal__wrapper').animate({
            scrollTop: (offset - stickyHeaderOffset - 4)
        }, 500);
    }
}
