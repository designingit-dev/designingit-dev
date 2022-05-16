window.addVideoToYTTracker = function(videoId, domObj){
    if(typeof gtmYTListeners !== 'undefined' && typeof YT !== 'undefined'){

        var videoExists = false;

            $.each(gtmYTListeners, function(j, w){
                if(videoId === w.getVideoData().video_id){
                    videoExists = true;
                }
            });
        if(!videoExists){
            gtmYTListeners.push(new YT.Player(domObj, {
                events: {
                    onStateChange: onPlayerStateChange // these event handlers are defined in a GTM tag
                }}));
        }
    }
};

$(document).ready(function(){
    $('.youtube-embed').on('click', function(){
        var iframe = document.createElement( "iframe" );
        iframe.setAttribute( "frameborder", "0" );
        iframe.setAttribute( "allowfullscreen", "" );
        iframe.setAttribute( "src", "https://www.youtube.com/embed/"+ $(this).data('ytvideoid') +"?rel=0&enablejsapi=1&autoplay=1" );
        iframe.setAttribute( "allow", "autoplay; encrypted-media;" );
        iframe.setAttribute( "data-action", "Embedded Click" );
        iframe.setAttribute("width", $(this).width());
        iframe.setAttribute("height", $(this).height());

        $(this).html('');
        $(this).append( iframe );
        addVideoToYTTracker($(this).data('ytvideoid'), iframe);
    });
});
