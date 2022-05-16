// Vendor JS
var Vue = require ('vue');
window.URI = require('urijs');
require('./vendor/bootstrap-dropdown.js');
require('./vendor/jquery.typeahead.min.js');
require('./vendor/magic360.js');
require('./vendor/magiczoomplus.js');
require('./vendor/jquery.stickybits.min.js');
require('lazysizes');


// Our JS
var App = require('./App.js');
var setupTrackForm = require('./track-form.js');
var slideDown = require('./transitions/slide-down.js');

require('./faq.js');
require('./hideshow.js');
require('./resizer.js');
require('./sticky-header.js');
require('./carousel.js');
require('./modal-target.js');
require('./select-val-dropdown.js');
require('./select-rep-north-america.js');
require('./confirm-email.js');
require('./scrollToId.js');
require('./terms-and-conditions.js');
require('./product.js');
require('./product-review-form.js');
require('./demo.js');
require('./support.js');
require('./search.js');
require('./commerce-product-reviews.js');
require('./landing-pages/ihrsa-2018-promo.js');
require('./user-location-field.js');
require('./geo-location-field.js');
require('./page-title-field.js');
require('./campaign-tracking-field.js');
require('./geo-personalisation.js');
require('./form.js');
require('./global-distributor-network.js');
require('./contact.js');
require('./country-redirect.js');
require('./youtube-video-tracking.js');
require('./smoothScroll.js');
require('./cart.js');
require('./siteWideInfoBanner.js');
require('./product-mpp.js');
require('./top-menu-dropdown.js');

$.fn.hasAttr = function(name){
    return this.attr(name) !== undefined;
};

$.expr[':'].textEquals = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().trim().match("^" + arg + "$");
    };
});

Vue.transition("slide-down", slideDown);

window.vueInst = new Vue(App);

$(document).ready(function(){

    $.ajax({
        url: '/',
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        data: {
            'action': 'keiser-contact-helpers/keiser-contact-helpers/get-visitor-customisation-data',
            'path': window.location.pathname,
            'url': window.location.href
        },
        success: function(response){
            if(response.status == 'success'){

                window.countryISO = response.geolocation.country;
                window.zip = response.geolocation.zip;
                window.currentGeolocation = response.geolocation.label;
                window.subdivision = response.geolocation.subdivision;

                window.isForeignVisitor = response.isForeignVisitor;

                window.userLocationField.show();
                window.geoLocationField.fill();
                window.geoPersonalisation.hideForeignModules();

                if(window.isForeignVisitor){
                    $('.siteHeader__nav .vertical--align--bottom').removeClass('vertical--align--bottom');
                }

                window.campaignParameters = response.campaignParameters;
                window.campaignTrackingField.fill();

                $('#absoluteTopBannersContainer').html(response.banners.absoluteTopBanners);
                $('#belowTopNavigationBannersContainers').html(response.banners.belowTopNavigationBanners);
                window.siteWideInfoBanner.init();
                $(document).trigger('bannersLoaded');
                if(response.numItemsInCart > 0){
                    $('.cartIconContainer').removeClass('is--hidden');
                    $('.cartIconContainer .cart__qty').removeClass('is--hidden').html(response.numItemsInCart);
                }
            }
        },
        complete: function(){
            if(typeof window.currentGeolocation !== 'undefined' && window.currentGeolocation){
                var geolocation = window.currentGeolocation.split(';');
                var geolocationStub = {};
                if(typeof geolocation[0] !== 'undefined'){
                    geolocationStub['geolocation_city'] = geolocation[0];
                }
                if(typeof geolocation[1] !== 'undefined'){
                    geolocationStub['geolocation_state'] = geolocation[1];
                }
                if(typeof geolocation[2] !== 'undefined' && typeof window.countriesList[geolocation[2]] !== 'undefined'){
                    geolocationStub['geolocation_country'] = window.countriesList[geolocation[2]];
                }
                if(typeof geolocation[3] !== 'undefined'){
                    geolocationStub['geolocation_zip'] = geolocation[3];
                }
                rudderanalytics.getUserId((userId) => {
                    rudderanalytics.identify(
                        userId,
                        geolocationStub
                    );
                });
            }
        }
    });

    window.pageTitleField.fill();
    setupTrackForm();

    $('.caseStudy__filters__toggle').on('click', function(){
       $('.caseStudy__filters__container').toggleClass('is--hidden');
       $('.caseStudy__filters__plusIcon').toggleClass('caseStudy__filters__crossIcon');
    });

    if($('.caseStudy__solutions__bigBannerSlider').length){
       $('.caseStudy__solutions__bigBannerSlider').slick({
           prevArrow: '<svg class="caseStudy__solutions__bigBannerSlider__arrow caseStudy__solutions__bigBannerSlider__prevArrow"><use xlink:href="#slider-arrow-left"></use></svg>',
           nextArrow: '<svg class="caseStudy__solutions__bigBannerSlider__arrow caseStudy__solutions__bigBannerSlider__nextArrow"><use xlink:href="#slider-arrow-right"></use></svg>'
       });
    }

    if($('#demoVan__stickyButton').length){
        var demoVanIntersectionObserver = new IntersectionObserver(
            entries => {
                if(entries[0].isIntersecting){
                    $('#demoVan__stickyButton').addClass('is--hidden');
                } else {
                    $('#demoVan__stickyButton').removeClass('is--hidden');
                }
            },
            {
                rootMargin: '-55px 0px 0px 0px'
            }
        );
        $.each(['demoVan__topSubmit','demoVan__form__submit'], function(i,v){
            if(document.getElementById(v)){
                demoVanIntersectionObserver.observe(document.getElementById(v));
            }
        });
    }

    if($('#productOffers__stickyButton').length){
        var productOffersButtonIntersectionObserver = new IntersectionObserver(
            entries => {
                if(entries[0].isIntersecting){
                    $('#productOffers__stickyButton,#productOffers__stickyOptions').hide();
                    $('#productOffers__stickyOptions').removeAttr('style');
                } else {
                    $('#productOffers__stickyButton').show();
                }
            },
            {
                rootMargin: '-55px 0px 0px 0px'
            }
        );
        $.each(['strengthMpp__productHero','product__offers__setup__conversation__button','product__offers__demovan__button','product__offers__leasing__options__button','product__offers__space__design__button'], function(i,v){
            if(document.getElementById(v)){
                productOffersButtonIntersectionObserver.observe(document.getElementById(v));
            }
        });
    }

    $(document).on('click', function(e){
        var $e = $(e.target);
        if($e.hasClass('modalTrigger')){
            window.vueInst.openModal($e.data('modal-trigger'));
        }
        return true;
    });

    $('.loadAfterPageLoaded').each(function(){
        $(this).attr('src', $(this).data('src'));
        if($(this).data('srcset')){
            $(this).attr('srcset', $(this).data('srcset'));
        }
    });


});

document.addEventListener('lazybeforeunveil', function(e){
    var bg = e.target.getAttribute('data-bg');
    if(bg){
        e.target.style.backgroundImage = 'url(' + bg + ')';
    }

    var embedVideo = e.target.getAttribute('data-embed-video');
    if(embedVideo){
        var $target = $(e.target);
        $target.html($target.attr('data-embed-code'));
        $('video', $target)[0].play();
    }
});


document.addEventListener('lazyloaded', function(e){
    var ytAction = e.target.getAttribute('data-action');
    var ytSrc = e.target.getAttribute('data-src1');
    if(ytAction && ytSrc && typeof gtmYTListeners!== 'undefined' && typeof YT !== 'undefined' && /youtube.com\/embed/.test(ytSrc)){
        e.target.setAttribute('src', ytSrc)
        gtmYTListeners.push(new YT.Player(e.target, {
            events: {
                onStateChange: onPlayerStateChange
            }}));
    }
});

document.addEventListener('mousedown', function(e){
    var $e = $(e.target);
    if( ($e.is('button') || $e.is('a') || $e.is('input[type="submit"]')) && !$e.hasClass('is--hidden') && !$e.hasClass('rudder--notrack')){
        rudderanalytics.track('Click', {
            element_html_type: $e.is('a') ? 'link' : 'button',
            element_css_id: $e.attr('id'),
            element_label: $e.text(),
            element_css_classes: $e.attr('class'),
            element_custom_track: $e.data('rudder-track'),
            category: 'Click',
            label: $e.text(),
            value: $e.attr('id'),
            site: window.location.hostname
        });
    }
    return true;
})
