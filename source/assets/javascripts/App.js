var ProductsFilter = require ('./products-filter/ProductsFilter.vue')
var CheckoutAddresses = require ('./CheckoutAddresses.vue')
var Modal = require('./Modal.vue')
var CheckoutErrorModal = require ('./CheckoutErrorModal.vue')
var Gallery = require('./Gallery.vue')
var Brochure = require('./Brochure.vue')
var AddToCart = require('./AddToCart.vue')
var Job = require('./Job.vue')
var SharePage = require('./SharePage.vue')
var jobTitle = require('./job-title.js')
var trackEvent = require('./track-event.js')

module.exports = {

    el: 'body',

    data: {
        menuOpen: false,
        scrollPosition: 0,
        activeModal: '',
        productTitle: '',
    },

    ready: function() {
        var video = jQuery("#js--ensurePlayWhenVueLoads")

        if (video[0]) {
            video[0].play()
        }
    },

    methods: {

        openModal: function (modalName, productTitle) {
            if (this.activeModal) { return }
            this.activeModal = modalName
            if (productTitle) {
                this.productTitle = productTitle
            }
            if (modalName == 'job') {
                jobTitle.title = productTitle
            }
            if(modalName == 'contactUs__contactRep'){
                var uri = new URI(window.location.href);
                var queryParams = URI.parseQuery(uri.query());
                if(typeof queryParams['repHandle'] !== 'undefined') {
                    this.getRep(queryParams['repHandle']);
                }
            }
            this.$nextTick(function(){
                window.initialiseFAQ('.marketSpecificBenefits__modal .faq');
                window.sproutFormValidation.initialiseRecaptcha();
            })
        },

        closeModal: function(modalName){
            this.$emit('close-modal')
        },

        openModalWithTracking: function (modalName, tracking) {
            if (tracking) {
                var meta = JSON.parse(tracking.replace(/'/g, '"'));
                trackEvent(meta[0], meta[1], meta[2]);
            }

            if (this.activeModal) { return }
            this.activeModal = modalName
        },

        openMenu: function (object, event) {
            event.preventDefault()
            var $content = jQuery('.js--siteContent')

            if(this.menuOpen === true) {
                // Menu will hide
                this.menuOpen = false
                jQuery('html').css('overflow', '')
            } else {
                // Menu will open
                this.menuOpen = true
                jQuery('html').css('overflow', 'hidden')
                jQuery('#siteMenu .algoliaSearchBox').focus();
            }
        },

        populateDistributorInfo: function () {
            trackEvent('Distributor Redirect', 'View Distributor Contact Info', window.countryISO);
            if(typeof window.distributors !== 'undefined'){
                var distributor = distributors[window.countryISO];
                jQuery('.distributorRedirect__contactInfo').html(
                    '<div class="l--medium--6 columns">' +
                        '<h2 class="text--red distributorRedirect__countryName">' + distributor.name + '</h2>' +
                        '<a class="distributorRedirect__website" href="' + distributor.website + '" target="_blank" v-on:click="trackEvent(\'Distributor Redirect\', \'Click Distributor Redirect\',\''+ window.countryISO +'\')">' + distributor.website + '</a>' +
                    '</div>' +
                    '<div class="l--medium--5 columns">' +
                        distributor.address +
                        '<a class="distributorRedirect__phone" href="tel:' + distributor.phone + '">Tel: ' + distributor.phone + '</a>' +
                        '<br>' +
                        '<a class="distributorRedirect__email" href="email:' + distributor.email + '" >Email: ' + distributor.email + '</a>' +
                    '</div>'
                );
                jQuery('.distributorRedirect__contactInfo').addClass('margin--top');
                this.$compile(jQuery('.distributorRedirect__contactInfo:first').get(0));
            }
        },

        trackEvent: function(category, action, label, callback, value) {
            trackEvent(category, action, label, callback, value)
        },

        showPage: function(contentId, containerId, fieldToValidate, fieldType, errorContainer, hideHeaderActions){
            var validated = true;
            if(typeof fieldToValidate !== 'undefined' && fieldToValidate){
                switch(fieldType){
                    case 'radio':
                    case 'checkbox':
                        var $checked = jQuery('input[name="'+ fieldToValidate +'"]:checked');
                        if($checked.length === 0){
                            validated = false;
                            jQuery('#' + errorContainer).removeClass('is--hidden');
                        }
                        break;
                }
            }
            if(validated){
                jQuery('#' + errorContainer).addClass('is--hidden');
                jQuery('#' + containerId + ' .modalContentPage').hide();
                switch(contentId){
                    case 'contactUs__commercialSales__geolocationConfirmation':
                        var location = '';
                        var values = window.currentGeolocation.split(';');
                        if(window.countryISO === 'US'){
                            location = values[0] + ', ' + values[1] + ' ' + values[3];
                        } else {
                            location = values[2];
                        }
                        jQuery('#contactUs__commercialSales__geolocationConfirmation__currentLocation').text(location);
                        break;
                    case 'contactUs__commercialSales__countrySelection':
                        window.userLocationField.activateCountryDropdown(jQuery('#contactUs__commercialSales__country'));
                        break;
                }
                jQuery('#' + contentId).show();
                if(typeof hideHeaderActions !== 'undefined' && hideHeaderActions){
                    jQuery('.contactUs__modal__headerAction').hide();
                } else {
                    jQuery('.contactUs__modal__headerAction').show();
                }
            }
        },

        geolocationConfirmation: function(errorContainer){
            var $checked = jQuery('input[name="contactUs__commercialSales__geolocationConfirmation"]:checked');
            if($checked.length > 0){
                if($checked.val() == 'yes'){
                    if(typeof window.zip !== 'undefined'){
                        this.findRep(window.countryISO, window.zip);
                    } else {
                        this.findRep(window.countryISO);
                    }
                    this.showPage('contactUs__commercialSales__contactDetails', 'contactUs__modal__fieldsContainer');
                } else {
                    if(window.countryISO === 'US'){
                        this.showPage('contactUs__commercialSales__zipCodeSelection', 'contactUs__modal__fieldsContainer');
                    } else {
                        this.showPage('contactUs__commercialSales__countrySelection', 'contactUs__modal__fieldsContainer');
                    }
                }
            } else {
                jQuery('#' + errorContainer).removeClass('is--hidden');
            }
        },

        locationConfirmation: function(locationType, errorContainer){
            if(locationType == 'zipCode'){
                var zip = jQuery('#contactUs__commercialSales__zipCode').val();
                if(zip === ''){
                    jQuery('#' + errorContainer).removeClass('is--hidden');
                } else {
                    var data = {
                        'action': 'keiser-contact-helpers/keiser-contact-helpers/validate-u-s-zip-code',
                        'zip': zip
                    };
                    var self = this;
                    jQuery.ajax({
                        url: '/',
                        method: 'POST',
                        data: data,
                        headers: {
                            'Accept': 'application/json'
                        },
                        beforeSend: function(){
                            jQuery('#' + errorContainer).addClass('is--hidden');
                            jQuery('.contactUs__commercialSales__zipCodeConfirmationButton').html('<img src="/assets/images/tail-spin.svg" class="margin--right--half loading--tailspin" /> Validating...');
                        },
                        success: function(response){
                            if(response.status === 'success'){
                                self.findRep('US', zip);
                                self.showPage('contactUs__commercialSales__contactDetails', 'contactUs__modal__fieldsContainer');
                            } else {
                                jQuery('#' + errorContainer).removeClass('is--hidden');
                            }
                        },
                        error: function(error){
                            console.log(error);
                        },
                        complete: function(){
                            jQuery('.contactUs__commercialSales__zipCodeConfirmationButton').html('Next');
                        }
                    });
                }
            } else if(locationType == 'country'){
                if(typeof window.countriesList[jQuery('#contactUs__commercialSales__country').val()] !== 'undefined'){
                    var countryISO = window.countriesList[jQuery('#contactUs__commercialSales__country').val()];
                    if(countryISO === 'US'){
                        this.showPage('contactUs__commercialSales__zipCodeSelection', 'contactUs__modal__fieldsContainer');
                    } else {
                        this.findRep(countryISO);
                        this.showPage('contactUs__commercialSales__contactDetails', 'contactUs__modal__fieldsContainer');
                    }
                } else {
                    jQuery('#' + errorContainer).removeClass('is--hidden');
                }
            }
        },

        findRep: function(countryISO, zip){
            var country = null;
            $.each(window.countriesList, function(i,v){
                if(v == countryISO){
                    country = i;
                }
            });
            if(country !== null){
                jQuery('#contactUs__commercialSales__form input[name="fields[country]"]').val(country);
                jQuery('#strengthMpp__commercialEnquiry__form input[name="fields[country]"]').val(country);
            }
            if(typeof zip !== 'undefined'){
                jQuery('#contactUs__commercialSales__form input[name="fields[zip]"]').val(zip);
                jQuery('#strengthMpp__commercialEnquiry__form input[name="fields[zip]"]').val(zip);
            }
            jQuery('#contactUs__commercialSales__form input[name="fields[countryISO]"]').val(countryISO);
            jQuery('#strengthMpp__commercialEnquiry__form input[name="fields[countryISO]"]').val(countryISO);
            window.userLocationField.findKeiserRep(
                countryISO,
                (typeof zip !== undefined ? zip : null),
                jQuery('#contactUs__commercialSales__form input[name="fields[institutionType]"]:checked').val(),
                jQuery('#contactUs__commercialSales__form input[name="fields[interestedProducts][]"]:checked').map(function(){ return jQuery(this).val() }).toArray(),
                function(){
                    jQuery('.contactUs__commercialSales__repName').html('');
                    jQuery('.strengthMpp__commercialEnquiry__modal__repName').text('');
                    jQuery('.strengthMpp__commercialEnquiry__modal__repDesignation').text('');
                    jQuery('.strengthMpp__commercialEnquiry__modal__repPhoneTollFree').hide();
                    jQuery('.strengthMpp__commercialEnquiry__modal__repPhone').text('');
                    jQuery('.strengthMpp__commercialEnquiry__modal__repTollFree').text('');
                    jQuery('.contactUs__commercialSales__repFirstName').text('');
                    jQuery('#contactUs__commercialSales__repImageColumn').removeClass();
                    jQuery('#contactUs__commercialSales__repContactColumn').removeClass();
                    jQuery('.contactUs__commercialSales__repImage').html('<img src="/assets/images/tail-spin-red.svg" class="margin--right--half loading--tailspin" />')
                    jQuery('#strengthMpp__commercialEnquiry__modal__repImageColumn').show();
                    jQuery('.contactUs__commercialSales__repContact').html(' Finding your representative...');
                },
                function(response){
                    if(response.status === 'success'){
                        jQuery('.contactUs__commercialSales__repName').text(response.repName);
                        jQuery('.strengthMpp__commercialEnquiry__modal__repName').text(response.repName + ',');
                        jQuery('.strengthMpp__commercialEnquiry__modal__repDesignation').text(response.repDesignation);
                        jQuery('.strengthMpp__commercialEnquiry__modal__repPhone').text('Phone: ' + response.repPhone).attr('href','tel:'+response.repPhone);
                        if(typeof response.repTollFree !== 'undefined' && response.repTollFree){
                            jQuery('.strengthMpp__commercialEnquiry__modal__repTollFree').text('Toll Free: ' + response.repTollFree).attr('href','tel:' + response.repTollFree);
                            jQuery('.strengthMpp__commercialEnquiry__modal__repTollFree__container').show();
                        } else {
                            jQuery('.strengthMpp__commercialEnquiry__modal__repTollFree__container').hide();
                        }
                        jQuery('.strengthMpp__commercialEnquiry__modal__repPhoneTollFree').show();
                        jQuery('.contactUs__commercialSales__repFirstName').text('EMAIL ' + response.repName.split(' ')[0]);
                        jQuery('.contactUs__commercialSales__repContact').html(response.repContact);
                        if(typeof response.repImage !== 'undefined'){
                            jQuery('#contactUs__commercialSales__repImageColumn').addClass('l--medium--4 columns');
                            jQuery('#contactUs__commercialSales__repContactColumn').addClass('l--medium--8 columns');
                            jQuery('#strengthMpp__commercialEnquiry__modal__repImageColumn').show();
                            jQuery('#strengthMpp__commercialEnquiry__modal__repContactDetailsColumn').removeClass('l--small--12').addClass('l--small--10');
                            jQuery('.contactUs__commercialSales__repImage').html('<img src="' + response.repImage +'" />')
                            if(typeof response.repRoundedCornersImage !== 'undefined'){
                                jQuery('.contactUs__commercialSales__repRoundedCornersImage').html('<img src="' + response.repRoundedCornersImage +'" />')
                            }
                        } else {
                            jQuery('#contactUs__commercialSales__repContactColumn').addClass('l--medium--12 columns');
                            jQuery('#strengthMpp__commercialEnquiry__modal__repImageColumn').hide();
                            jQuery('#strengthMpp__commercialEnquiry__modal__repContactDetailsColumn').removeClass('l--small--10').addClass('l--small--12');
                            jQuery('.contactUs__commercialSales__repImage').html('');
                        }
                        jQuery('#contactUs__commercialSales__form input[name="fields[repEmail]"]').val(response.repEmail);
                        jQuery('#contactUs__commercialSales__form input[name="fields[repVPEmail]"]').val(response.repVPEmail);
                        jQuery('#strengthMpp__commercialEnquiry__form input[name="fields[repEmail]"]').val(response.repEmail);
                        jQuery('#strengthMpp__commercialEnquiry__form input[name="fields[repVPEmail]"]').val(response.repVPEmail);
                        jQuery('.contactUs__modal input[name="redirect"]').val(response.redirect);
                    }
                },
                function(error){
                    console.log(error);
                }
            );
        },

        getRep: function(repHandle){
            jQuery.ajax({
                url: '/',
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                data: {
                    'action': 'keiser-contact-helpers/keiser-contact-helpers/get-keiser-rep',
                    'repHandle': repHandle
                },
                beforeSend: function(){
                    jQuery('.contactUs__contactRep__repName').html('');
                    jQuery('.contactUs__contactRep__repFirstName').text('');
                    jQuery('.contactUs__contactRep__repContact').html('<img src="/assets/images/tail-spin-red.svg" class="margin--right--half loading--tailspin" /> Finding your representative...');
                },
                success: function(response){
                    if(response.status === 'success'){
                        jQuery('.contactUs__contactRep__repName').text(response.repName);
                        jQuery('.contactUs__contactRep__repFirstName').text('EMAIL ' + response.repName.split(' ')[0]);
                        jQuery('.contactUs__contactRep__repContact').html(response.repContact);
                        jQuery('#contactUs__contactRep__form input[name="fields[repEmail]"]').val(response.repEmail);
                        jQuery('#contactUs__contactRep__form input[name="fields[repVPEmail]"]').val(response.repVPEmail)
                    }
                },
                error: function(){
                    console.log(error);
                }
            });
        },

        validateUSZipCode: function(zip, beforeSend, success, error, complete, $errorContainer){
            window.userLocationField.validateUSZipCode(zip, beforeSend, success, error, complete, $errorContainer);
        },

        disableButton: function(){
            jQuery(this).addClass('button--disabled');
        },

        validateDemoVanRequest: function(type){
            if(type === 'form'){
                if(jQuery('#demoVan__form input[name="fields[fullName]"]').val() === '' || jQuery('#demoVan__form input[name="fields[email]"]').val() === ''){
                    jQuery('#demoVan__form .contactUs__modal__errorContainer').removeClass('is--hidden');
                } else {
                    this.openModal('demoVan__modal');
                }
            } else if(type === 'location'){
                if(jQuery('input[name="fields[locationType]"]:checked').val() !== 'commercialSales'){
                    this.showPage('demoVan__modal__consumerSales', 'demoVan__modal__fieldsContainer');
                } else {
                    this.showPage('demoVan__modal__zipCodeSelection', 'demoVan__modal__fieldsContainer')
                }
                window.sproutFormValidation.initialiseRecaptcha();
            }
        },

        demoVanLocationConfirmation: function(){
            var requiredFields = [
                {
                    'inputName': 'fields[customerTitle]',
                    'inputType': 'singleLine',
                    'containerType': 'modal'
                },
                {
                    'inputName': 'fields[institutionType]',
                    'inputType': 'dropdown',
                    'containerType': 'modal'
                },
                {
                    'inputName': 'fields[interestedProducts][]',
                    'inputType': 'checkboxes',
                    'containerType': 'modal'
                }
            ];
            if(!window.sproutFormValidation.validateFields(requiredFields)){
                return false;
            }
            var countryISO = jQuery('#demoVan__modal__countrySelection option:selected').val();
            var $marketingOptInField = jQuery('input[name="fields[marketingOptIn]"]:checked');
            if($marketingOptInField.length === 0){
                jQuery('#marketingOptInErrorContainer').removeClass('is--hidden');
            } else if(countryISO == 'US'){
                var zip = jQuery('#contactUs__commercialSales__zipCode').val();
                var $errorContainer = jQuery('#demoVan__modal__zipCodeSelection__error');
                var self = this;
                this.validateUSZipCode(
                    zip,
                    function(){
                        jQuery('.demoVan__modal__zipCodeConfirmationButton').addClass('button--disabled').html('<img src="/assets/images/tail-spin.svg" class="margin--right--half loading--tailspin" /> Validating...');
                    },
                    function(response){
                        if(response.status == 'success'){
                            self.submitDemoVanForm(countryISO, zip, $marketingOptInField)
                        } else {
                            $errorContainer.removeClass('is--hidden');
                            jQuery('.demoVan__modal__zipCodeConfirmationButton').removeClass('button--disabled').html('Next');
                        }
                    },
                    function(error){
                        console.log(error);
                    },
                    function(){},
                    $errorContainer
                )
            } else if($('#fields-zipCode').length){
                this.submitDemoVanForm(countryISO, $('#fields-zipCode').val(), $marketingOptInField)
            } else {
                this.submitDemoVanForm(countryISO, null, $marketingOptInField)
            }
        },

        submitDemoVanForm: function(countryISO, zip, $marketingOptInField){
            jQuery('#demoVan__form input[name="fields[countryISO]"]').val(countryISO);
            jQuery('#demoVan__form input[name="fields[countryName]"]').val(window.userLocationField.getCountryNameFromISO(countryISO));
            if(zip){
                jQuery('#demoVan__form input[name="fields[zipCode]"]').val(zip);
            }
            jQuery('#demoVan__form input[name="fields[marketingOptIn]"]').val($marketingOptInField.val());
            jQuery('#demoVan__form').append($('input[name="fields[customerTitle]"]'));
            jQuery('#demoVan__form').append($('input[name="fields[phoneNumber]"]'));
            jQuery('#demoVan__form').append($('select[name="fields[institutionType]"]'));
            jQuery('#demoVan__form').append($('input[name="fields[interestedProducts][]"]'));
            if($('textarea[name="g-recaptcha-response"]').length){
                jQuery('#demoVan__form').append($('textarea[name="g-recaptcha-response"]'));
            }
            jQuery('.demoVan__modal__zipCodeConfirmationButton').html('Submitting...').addClass('button--disabled');
            jQuery('#demoVan__form').submit();
        },

        disableSubmit: function(formId){
            var $form = jQuery('#' + formId);
            var form = $form.get()[0];
            if(form.checkValidity()){
                jQuery('input[type="submit"]', $form).addClass('button--disabled').val('Submitting..');
            }
        },

        openSalesModal: function(){
            this.closeModal('contactUs__sales');
            if(jQuery('input[name="fields[locationType]"]:checked').val() == 'commercialSales'){
                this.openModal('contactUs__commercialSales');
            } else {
                this.openModal('contactUs__consumerSales');
            }
        },

        changeModal: function(currentModal, newModal){
            this.closeModal(currentModal);
            this.openModal(newModal);
        },

        shopNoticeConfirmation: function(){
            var uri = new URI(window.location.href);
            if($.inArray('shop', uri.segment()) !== -1){
                this.closeModal('northAmericaShopConfirmation__modal');
            } else {
                window.location.href = '/shop';
            }
        },

        demoNoticeConfirmation: function(){
            var uri = new URI(window.location.href);
            if($.inArray('demo', uri.segment()) !== -1){
                this.closeModal('northAmericaDemoConfirmation__modal');
            } else {
                window.location.href = '/demo';
            }
        },

        demoVanCountrySelection: function(){
            var countryISO = jQuery('#demoVan__modal__countrySelection option:selected').val();
            if(countryISO == 'US'){
                jQuery('#demoVan__modal__zipSelectionContainer').removeClass('is--hidden');
                jQuery('.demoVan__modal__zipCodeConfirmationButton').removeClass('button--disabled');
            } else if(countryISO !== '') {
                jQuery('#demoVan__modal__zipSelectionContainer').addClass('is--hidden');
                jQuery('.demoVan__modal__zipCodeConfirmationButton').removeClass('button--disabled');
                jQuery('#demoVan__modal__zipCodeSelection__error').addClass('is--hidden');
            } else {
                jQuery('.demoVan__modal__zipCodeConfirmationButton').addClass('button--disabled');
            }
        },

        openCommercialSalesSimplifiedModal: function(productTitle){
            this.openModal('contactUs__commercialSalesSimplified');
            var self = this;
            if(window.homeCountryISO && window.homeCountryISO == 'US'){
                self.$nextTick(function(){
                    self.showPage('contactUs__commercialSales__geolocationConfirmation', 'contactUs__modal__fieldsContainer');
                });
            }
        },

        openCommercialEnquiryModal: function(formSlug){
            this.closeModal(this.activeModal);
            this.openModal('strengthMpp__commercialEnquiry__' + formSlug);
            var self = this;
            if(window.homeCountryISO && window.homeCountryISO == 'US'){
                self.$nextTick(function(){
                    self.showPage('contactUs__commercialSales__geolocationConfirmation', 'contactUs__modal__fieldsContainer');
                });
            }
        },

        addToCartWithQuestionnaire: function(modalName, e){
            var $form = jQuery('form[name="'+ modalName +'"]');
            var form = $form.get()[0];
            if(form.reportValidity()){
                var defaultVariantPurchasableId = $form.attr('data-defaultvariantpurchasableid');
                var $addToCartForm = jQuery('#form' + defaultVariantPurchasableId);
                var data = $addToCartForm.serializeArray();
                if($form.length > 0){
                    var formFields = $form.serializeArray();
                    $.each(formFields, function(i, v){
                        var question = jQuery('input[name="'+ v.name +'"]:first').attr('data-question');
                        data.push({
                            'name': 'options['+ question +']',
                            'value': v.value
                        });
                    });
                }
                var $submitLabel = jQuery('label[for=submit' + jQuery('[name="purchasableId"]', $addToCartForm).val() + ']');
                var self = this;
                $.ajax({
                    type: 'POST',
                    url: '/',
                    data: data,
                    headers: {
                        'Accept': 'application/json'
                    },
                    beforeSend: function(){
                        jQuery(e.target).addClass('button--disabled').val('Adding...');
                    },
                    success: function(response){
                        if(response.cart){
                            $submitLabel.html('Added');
                            $submitLabel.addClass('button--disabled');
                            jQuery('.cart__qty').html(response.cart.totalQty).removeClass('is--hidden');
                            jQuery('.cart__link').removeClass('is--hidden');

                            // Added to ensure tracking only fires after an Ajax item is successfully added to the cart.
                            ga('keisergtm.ec:addProduct', {'id': $addToCartForm.attr('data-sku'),
                                'name': $addToCartForm.attr('data-product-name'),
                                'category': $addToCartForm.attr('data-category'),
                                'price': $addToCartForm.attr('data-price'),
                                'quantity': $addToCartForm.find('[name="qty"]').val()
                            });
                            ga('keisergtm.ec:setAction', 'add');
                            ga('keisergtm.send', 'event', 'ecommerce', 'add to cart', {'nonInteraction': true});

                            rudderanalytics.track('Product Added',{
                                product_id: $addToCartForm.attr('data-sku'),
                                sku: $addToCartForm.attr('data-sku'),
                                category: $addToCartForm.attr('data-category'),
                                name: $addToCartForm.attr('data-product-name'),
                                brand: 'Keiser',
                                price: parseFloat($addToCartForm.attr('data-price')),
                                quantity: parseFloat($addToCartForm.find('[name="qty"]').val()),
                                url: $addToCartForm.attr('data-product-url'),
                                image_url: $addToCartForm.attr('data-product-image-url'),
                                site: window.location.hostname
                            });

                            var uri = new window.URI(window.location.href);
                            if(uri.filename() == 'cart'){
                                window.location.reload();
                            } else {
                                self.closeModal(modalName);
                                jQuery('div[for="form'+ defaultVariantPurchasableId +'"]').show();
                            }
                        }
                    }
                });
            }
        },

        trackFormAnchor:function(formName, formAnchor){
            window.formName = formName;
            window.formAnchor = formAnchor;
        }
    },

    events: {

        'close-modal': function (modalName) {
            this.activeModal = ''
            this.productTitle = ""
        },

    },

    components: {
        vueModal: Modal,
        vueProductsFilter: ProductsFilter,
        vueGallery: Gallery,
        vueBrochure: Brochure,
        vueCheckoutAddresses: CheckoutAddresses,
        vueCheckoutErrorModal: CheckoutErrorModal,
        vueAddtocart: AddToCart,
        vueJob: Job,
        vueSharePage: SharePage,
    }

}
