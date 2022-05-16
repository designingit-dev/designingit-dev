window.campaignTrackingField = {

    fill: function(){
        var $campaignTrackingField = $('.campaignTrackingField');
        if($campaignTrackingField.length !== 0){
            $campaignTrackingField.val(window.campaignParameters);
        }
    }

};
