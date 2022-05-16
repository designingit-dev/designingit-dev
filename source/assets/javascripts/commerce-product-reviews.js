$(document).on('ready', function(){

    if($('#commerceProductReview__sortBy').length > 0){
        var uri = new URI(window.location.href);
        var queryParams = URI.parseQuery(uri.query());
        if(typeof queryParams['sortBy'] !== 'undefined'){
            $('#commerceProductReview__sortBy option[value="'+ queryParams['sortBy'] +'"]').attr('selected', 'selected');
            $('#commerceProductReview__pagination a:not(.link--disableClick)').each(function(i, link){
                var url = new URI($(link).attr('href'));
                url.setQuery('sortBy', queryParams['sortBy']);
                $(link).attr('href', url.resource());
            });
        } else {
            $('#commerceProductReview__sortBy option:first').attr('selected', 'selected');
        }
        $('#commerceProductReview__sortBy').on('change', function(){
            uri.setQuery('sortBy', $(this).val());
            uri.removeQuery('page');
            window.location.href = uri.resource();
        });
    }

    $('.commerceProductReview__readMore').on('click', function(){
        $(this).parents($(this).attr('data-parent')).first().html($(this).siblings('.commerceProductReview__readMoreContent').first().html());
    });

});