window.geoLocationField = {
    fill: function(){
        var $geoLocationField = $('.geoLocationField');
        if($geoLocationField.length !== 0){
            $geoLocationField.val(window.currentGeolocation);
        }
    }
};