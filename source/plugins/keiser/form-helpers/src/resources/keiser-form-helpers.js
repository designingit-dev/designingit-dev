$(document).ready(function(){
    $(".field .input .elementselect .element:not(.linked)").each(function(){
        var $this = $(this);
        if (!$this.data("url")) { return false; }
        var $a = $("<a class='icon'> </a>")
            .attr("title", "Download")
            .addClass("download")
            .addClass("sharebtn")
            .addClass("icon")
            .on('click', function(){
                window.downloadAssetFromLink($this.data('url') + '?mtime=' + Date.now()); // cachebusting
            })
            .appendTo($this);
        $this
            .addClass("linked")
            .append($a);
    });
});