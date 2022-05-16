$(function(){
    var $resultsContainer = $('.viewer__container'), $loading = $('.viewer__loading'), $meta, $file, $log;

    $('#log__selector').on('change', function() {
        $file = $(this).find('option:selected');
        toggleLoading();
        loadLog();
    });

    function toggleLoading() {
        if (!$loading.is(':visible')) {

            if ($resultsContainer.is(':visible')) {
                $resultsContainer.hide();
            }

            $meta = $.parseJSON($file.attr('data-file'));
            var metaModifiedDate = $file.attr('date-modified-date');
            var metaFilesize = $file.attr('data-filesize');

            $loading.find('.filename').html($meta.filename);
            $loading.find('.modified').html(metaModifiedDate);

            $loading.show();
        } else {
            $loading.hide();
        }
    }

    function getLog() {
        var postData = {
            action: "logViewer/getLog"
        };

        postData = Object.assign(postData, $meta);

        return $.ajax({
            method: 'POST',
            url: '/',
            data: postData
        });
    }

    function showLog() {
        $resultsContainer.find('.filename').html($log.filename + ' (last modified: ' + $file.attr('date-modified-date') + ')');
        $resultsContainer.find('.logfile').html($log.html);
        $resultsContainer.show();
    }

    function loadLog() {
        $.when( getLog() ).then(function(data) {
            $log = data;

            toggleLoading();
            showLog();
        });
    }
});