$(document).on('ready', function(){
    var uri = new URI(window.location.href);
    var queryParams = URI.parseQuery(uri.query());
    if(typeof queryParams['modal'] !== 'undefined') {
        switch (queryParams['modal']) {
            case 'contactRep':
                $('#contactRepButton').click();
                break;
            case 'contactCustomerSupport':
                $('#contactCustomerSupportButton').click();
                break;
            case 'contactSales':
                $('#contactSalesButton').click();
                break;
            case 'contactKeiserApps':
                $('#contactKeiserAppsButton').click();
                break;
            case 'contactPublicRelations':
                $('#contactPublicRelationsButton').click();
                break;
            case 'contactEducationServices':
                $('#contactEducationServicesButton').click();
                break;
            case 'contactGeneralMailbox':
                $('#contactGeneralMailboxButton').click();
                break;
            case 'contactCommercialSales':
                $('#contactCommercialSalesButton').click();
                break;
            case 'contactCommercialSalesSimplified':
                $('#contactCommercialSalesSimplifiedButton').click();
                break;
            case 'fedex_ground_delivery_service_map':
                window.vueInst.openModal('fedex_ground_delivery_service_map');
                break;
        }
    }
});
