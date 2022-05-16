window.pageTitleField = {

    fill: function(){
        var $pageTitleField = $('.pageTitleField');
        if($pageTitleField.length !== 0){
            var pageTitle = $('head title').text();
            var pageUrl = window.location.href;
            $pageTitleField.val(pageTitle + '; ' + pageUrl);
        }
    }

};
