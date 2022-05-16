function checkCountryRedirect(){
    if($.inArray(window.countryISO, window.countryRedirectList) !== -1){
        $(document).ready(function(){
            $('#countryRedirectModal__trigger').click();
        });
    }
}

if(typeof window.countryRedirectEnabled !== 'undefined' && window.countryRedirectEnabled) {
    window.executeOnce(checkCountryRedirect, null, 'country_redirect', false);
}
