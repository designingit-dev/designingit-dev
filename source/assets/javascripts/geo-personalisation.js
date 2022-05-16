window.geoPersonalisation = {

    hideForeignModules: function(){
        if(window.isForeignVisitor){
            var self = this;
            $('[data-hideForeign="true"]').addClass('is--hidden');
            $('[data-showForeign="true"]').removeClass('is--hidden');

            var uri = new URI(window.location.href);
            if($.inArray('shop', uri.segment()) !== -1){
                window.executeOnce(self.triggerShopConfirmation, null, 'foreign_shop_confirmation', false);
            } else if($.inArray('demo', uri.segment()) !== -1){
                window.executeOnce(self.triggerDemoConfirmation, null, 'foreign_demo_confirmation', false);
            }

            $('#footerShopLink').on('click', function(e){
                window.executeOnce(function(){self.triggerShopConfirmation(e)}, null, 'foreign_shop_confirmation', false);
            });
            $('#footerDemoLink').on('click', function(e){
                window.executeOnce(function(){self.triggerDemoConfirmation(e)}, null, 'foreign_demo_confirmation', false);
            });
        }
    },

    triggerShopConfirmation: function(e){
        if(typeof e !== 'undefined'){
            e.preventDefault();
        }
        $('#northAmericaShopConfirmation__trigger').click();
    },

    triggerDemoConfirmation: function(e){
        if(typeof e !== 'undefined'){
            e.preventDefault();
        }
        $('#northAmericaDemoConfirmation__trigger').click();
    }

};
