var validationService = require('./checkout-validation-service');

window.createFunctionWithTimeout = function(callback, opt_timeout){
    var called = false;
    function fn() {
        if (!called) {
            called = true;
            callback();
        }
    }
    setTimeout(fn, opt_timeout || 1000);
    return fn;
};

$(document).ready(function(){
    if($('#checkout-orderReview').length){

        $('#checkout-step-1').on('submit', function(e){
            e.preventDefault();
        });

        $('#checkout-step-2').on('submit', function(e){
            e.preventDefault();
            window.checkout.displayPaymentOptions();
        });

        $('input[name="billingAddressSameAsShipping"]').on('change', function(){
            if($(this).is(':checked')){
                $('#checkout-billing-address-container').addClass('is--hidden');
                $('.billingAddress-requiredField').prop('required', false);
            } else {
                $('#checkout-billing-address-container').removeClass('is--hidden');
                $('.billingAddress-requiredField').prop('required', true);
            }
        });

        $('#checkout-orderReview').stickybits();

        window.checkout.attachOrderReviewToggleHandlers();
    }
});

window.checkout = {

    displayShippingOptions: function () {
        if (!($('#checkout-step-1')[0].checkValidity())) {
            $('#checkout-step-1')[0].reportValidity();
            return false;
        }
        var contactAndShippingInfo = $('#checkout-step-1').serializeArray();
        var data = {
            action: '/keiser-contact-helpers/keiser-contact-helpers/get-available-shipping-methods'
        };
        $.each(contactAndShippingInfo, function (i, v) {
            data[v.name] = v.value;
        });
        var $step1SubmitButton = $('#checkout-step-1 button[type="submit"]');
        var self = this;
        $.ajax({
            url: '/',
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            data: data,
            beforeSend: function () {
                $step1SubmitButton.addClass('button--disabled').html('Fetching Shipping Methods...');
            },
            success: function (response) {
                if (response.status == 'success') {
                    $('#checkout-shipping-methods-container').html(response.shippingOptions);
                    $('#checkout-orderReview').html(response.cart);
                    if(response.whiteGloveRemoved){
                        $('#whiteGloveUnavailable__modal__trigger').click();
                    };
                    if(response.nonWhiteGloveFeesAdded){
                        $('#nonWhiteGloveFeesAdded__modal__content').html(response.nonWhiteGloveFeesAddedContent);
                        $('#nonWhiteGloveFeesAdded__modal__trigger').click();
                    };
                    self.attachOrderReviewToggleHandlers();
                    $('#checkout-step-1').addClass('is--hidden');
                    $('#checkout-step-2').removeClass('is--hidden');
                    $('#checkout-step-3').addClass('is--hidden');
                    rudderanalytics.identify(response.rudderAnalyticsIdentify.userId,response.rudderAnalyticsIdentify.traits);
                    rudderanalytics.track('Checkout Step Completed',{
                        'checkout_id': window.cartNumber,
                        'step': 1,
                        site: window.location.hostname
                    })
                    rudderanalytics.track('Checkout Step Viewed',{
                        'checkout_id': window.cartNumber,
                        'step': 2,
                        site: window.location.hostname
                    })
                    rudderanalytics.track('Checkout Step Viewed',{
                        'checkout_id': window.cartNumber,
                        'step': 3,
                        site: window.location.hostname
                    })
                    if(typeof ga !== 'undefined'){
                        ga('keisergtm.ec:setAction','checkout', {
                            'step': 2,
                        });
                        ga('keisergtm.send', 'event', 'ecommerce', 'checkout');
                        ga('keisergtm.ec:setAction','checkout', {
                            'step': 3,
                        });
                        ga('keisergtm.send', 'event', 'ecommerce', 'checkout');
                    }
                    self.displayContactAndAddressSummary();
                } else if(response.error){
                    if(response.error == 'Cart is empty'){
                        window.location.href = '/cart';
                    } else {
                        $('#checkoutError__modal__trigger').click();
                    }
                }
            },
            error: function (e) {
                $('#checkoutError__modal__trigger').click();
            },
            complete: function () {
                $step1SubmitButton.removeClass('button--disabled').removeAttr('disabled').html($step1SubmitButton.data('default-text'));
            }
        });
    },

    displayPaymentOptions: function () {
        var shippingHandle = $('#checkout-step-2').serializeArray();
        var data = {
            action: '/keiser-contact-helpers/keiser-contact-helpers/get-available-payment-methods'
        };
        $.each(shippingHandle, function (i, v) {
            data[v.name] = v.value;
        });
        var $step2SubmitButton = $('#checkout-step-2 button[type="submit"]');
        $step2SubmitButton.data('default-text', $step2SubmitButton.text());
        var self = this;
        $.ajax({
            url: '/',
            method: 'POST',
            data: data,
            beforeSend: function () {
                $step2SubmitButton.addClass('button--disabled').html('Calculating Taxes...');
            },
            success: function (response) {
                if (response.status == 'success') {
                    $('#checkout-payment-methods-container').html(response.paymentOptions);
                    $('#checkout-orderReview').html(response.cart);
                    self.attachOrderReviewToggleHandlers();
                    $('#checkout-step-1').addClass('is--hidden');
                    $('#checkout-step-2').addClass('is--hidden');
                    $('#checkout-step-3').removeClass('is--hidden');
                    rudderanalytics.track('Checkout Step Completed',{
                        'checkout_id': window.cartNumber,
                        'step': 2,
                        'shipping_method': data['shippingMethodHandle'],
                        site: window.location.hostname
                    })
                    rudderanalytics.track('Checkout Step Completed',{
                        'checkout_id': window.cartNumber,
                        'step': 3,
                        'shipping_method': data['shippingMethodHandle'],
                        site: window.location.hostname
                    })
                    rudderanalytics.track('Checkout Step Viewed',{
                        'checkout_id': window.cartNumber,
                        'step': 4,
                        'shipping_method': data['shippingMethodHandle'],
                        site: window.location.hostname
                    })
                    if(typeof ga !== 'undefined'){
                        ga('keisergtm.ec:setAction','checkout', {
                            'step': 4,
                        });
                        ga('keisergtm.send', 'event', 'ecommerce', 'checkout');
                    }
                    self.attachPaymentMethodHandlers();
                    self.displayShippingMethodSummary();
                } else if(response.error){
                    if(response.error == 'Cart is empty'){
                        window.location.href = '/cart';
                    } else {
                        $('#checkoutError__modal__trigger').click();
                    }
                }
            },
            error: function (e) {
                $('#checkoutError__modal__trigger').click();
            },
            complete: function () {
                $step2SubmitButton.removeClass('button--disabled').removeAttr('disabled').html($step2SubmitButton.data('default-text'));
            }
        });
    },

    displayContactAndAddressSummary: function () {
        $('#checkout-summary-email').html($('#checkout-email').val());
        $('#checkout-summary-shipping-address').html(this.getAddressSummary('shippingAddress'));
        if (!$('input[name="billingAddressSameAsShipping"]').is(':checked')) {
            $('#checkout-summary-billing-address').html(this.getAddressSummary('billingAddress'));
            $('#checkout-summary-billing-address-container').removeClass('is--hidden');
        } else {
            $('#checkout-summary-billing-address-container').addClass('is--hidden');
        }
        $('.checkout__customerInfo__summary').removeClass('is--hidden');
        $('#checkout-summary-shipping-method-container').addClass('is--hidden');
        if($('#checkout-orderReview-mobileToggle').is(':visible')){
            //Scroll to shipping method container
            window.smoothScroll($('#checkout-step-2').offset().top - $('#checkout-orderReview-mobileToggle').height());
        } else {
            //Scroll to top of page
            window.smoothScroll(0);
        }
    },

    getAddressSummary: function (addressType) {
        var address = `
        ${$('#' + addressType + '-firstName').val()} ${$('#' + addressType + '-lastName').val()}
        <br>
        ${$('#' + addressType + '-address1').val()}
        `;
        if ($('#' + addressType + '-address2').val()) {
            address += '<br>' + $('#' + addressType + '-address2').val();
        }
        address += '<br>' + $('#' + addressType + '-city').val();
        if ($('#' + addressType + '-stateId').length) {
            address += ', ' + $('#' + addressType + '-stateId option:selected').text();
        }
        address += ', ' + $('#' + addressType + '-zipCode').val();
        address += '<br>' + $('#' + addressType + '-countryId option:selected').text();
        return address;
    },

    displayShippingMethodSummary: function () {
        var summary = $('input[name="shippingMethodHandle"]:checked').data('summary').split(';');
        $('#checkout-summary-shipping-method').html(
            summary[0] + '<span id="checkout-summary-shipping-method-separator"> . </span>' + summary[1]
        );
        $('#checkout-summary-shipping-method-container').removeClass('is--hidden');
    },

    attachPaymentMethodHandlers: function () {

        $('.cart__paymentOption').on('click', function () {
            $('.cart__paymentOption.selected').removeClass('selected');
            $(this).addClass('selected');
            switch ($(this).attr('data-gatewayhandle')) {
                case 'keiserStripe':
                    $('.cart__paymentOption__submit').html('<span>PAY WITH CARD</span>');
                    if($('#stripe-card-errors').text()){
                        window.checkout.togglePaymentSubmit('off');
                    } else {
                        window.checkout.togglePaymentSubmit('on');
                    }
                    break;
                case 'affirm':
                    $('.cart__paymentOption__submit').html('<span>BUY WITH </span><img class="cart__paymentOption__affirmButton" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iOTQ4cHgiIGhlaWdodD0iMjg3cHgiIHZpZXdCb3g9IjAgMCA5NDggMjg3IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCA1Mi4zICg2NzI5NykgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+d2hpdGVfbG9nby1zb2xpZF9iZzwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPgogICAgICAgIDxwb2x5Z29uIGlkPSJwYXRoLTEiIHBvaW50cz0iMTM5IDI3NyA4MDguMTM5IDI3NyA4MDguMTM5IDEwIDEzOSAxMCI+PC9wb2x5Z29uPgogICAgPC9kZWZzPgogICAgPGcgaWQ9IndoaXRlX2xvZ28tc29saWRfYmciIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxwYXRoIGQ9Ik0xODIuODgwNCwyNTQuMDA2NyBDMTc0LjYxMjQsMjU0LjAwNjcgMTcwLjQ3MjQsMjQ5LjkyOTcgMTcwLjQ3MjQsMjQzLjIyMTcgQzE3MC40NzI0LDIzMC43ODM3IDE4NC4zOTE0LDIyNi41Mzc3IDIwOS43OTM0LDIyMy44MzY3IEMyMDkuNzkzNCwyNDAuNDc2NyAxOTguNTQzNCwyNTQuMDA2NyAxODIuODgwNCwyNTQuMDA2NyBNMTkzLjgzNDQsMTYwLjM5MDcgQzE3NS42ODU0LDE2MC4zOTA3IDE1NC43OTk0LDE2OC45NDI3IDE0My40NjQ0LDE3Ny45Nzc3IEwxNTMuODE4NCwxOTkuNzY2NyBDMTYyLjkxMDQsMTkxLjQ1MTcgMTc3LjYwODQsMTg0LjMzODcgMTkwLjg2NzQsMTg0LjMzODcgQzIwMy40NjY0LDE4NC4zMzg3IDIxMC40MjI0LDE4OC41NTE3IDIxMC40MjI0LDE5Ny4wMzQ3IEMyMTAuNDIyNCwyMDIuNzQ0NyAyMDUuODEyNCwyMDUuNjMxNyAxOTcuMTAwNCwyMDYuNzYyNyBDMTY0LjU0MTQsMjEwLjk4NzcgMTM5LjAwMDQsMjE5Ljk3NjcgMTM5LjAwMDQsMjQ1LjA3MDcgQzEzOS4wMDA0LDI2NC45Njg3IDE1My4xNzA0LDI3Ni45OTk3IDE3NS4yOTk0LDI3Ni45OTk3IEMxOTEuMDg2NCwyNzYuOTk5NyAyMDUuMTQ3NCwyNjguMjIzNyAyMTEuODMxNCwyNTYuNjQ0NyBMMjExLjgzMTQsMjczLjc2MTcgTDI0MS4yNjU0LDI3My43NjE3IEwyNDEuMjY1NCwyMDIuMDM2NyBDMjQxLjI2NTQsMTcyLjQyMTcgMjIwLjY3MzQsMTYwLjM5MDcgMTkzLjgzNDQsMTYwLjM5MDciIGlkPSJGaWxsLTEiIGZpbGw9IiNGRkZGRkYiPjwvcGF0aD4KICAgICAgICA8cGF0aCBkPSJNNDg5LjM3MTYsMTYzLjYyODcgTDQ4OS4zNzE2LDI3My43NjE3IEw1MjAuODcyNiwyNzMuNzYxNyBMNTIwLjg3MjYsMjIwLjY5MTcgQzUyMC44NzI2LDE5NS40NzM3IDUzNi4xNDI2LDE4OC4wNjk3IDU0Ni43ODY2LDE4OC4wNjk3IEM1NTAuOTUwNiwxODguMDY5NyA1NTYuNTUyNiwxODkuMjc0NyA1NjAuMjU0NiwxOTIuMDUxNyBMNTY1Ljk5MDYsMTYyLjkzNTcgQzU2MS4xMzA2LDE2MC44NTI3IDU1Ni4wNDA2LDE2MC4zOTA3IDU1MS44NzU2LDE2MC4zOTA3IEM1MzUuNjgwNiwxNjAuMzkwNyA1MjUuNDk5NiwxNjcuNTYzNyA1MTguNzg5NiwxODIuMTM5NyBMNTE4Ljc4OTYsMTYzLjYyODcgTDQ4OS4zNzE2LDE2My42Mjg3IFoiIGlkPSJGaWxsLTMiIGZpbGw9IiNGRkZGRkYiPjwvcGF0aD4KICAgICAgICA8cGF0aCBkPSJNNzEyLjAzNzEsMTYwLjM5MDUgQzY5NS4zNzkxLDE2MC4zOTA1IDY4Mi45MjQxLDE3MC4yNDA1IDY3Ni40NDYxLDE3OS43MjY1IEM2NzAuNDMxMSwxNjcuNDYzNSA2NTcuNjc5MSwxNjAuMzkwNSA2NDIuNDA4MSwxNjAuMzkwNSBDNjI1Ljc1MDEsMTYwLjM5MDUgNjE0LjIxODEsMTY5LjY0NTUgNjA4Ljg5NjEsMTgwLjI4NzUgTDYwOC44OTYxLDE2My42Mjg1IEw1NzguNTM3MSwxNjMuNjI4NSBMNTc4LjUzNzEsMjczLjc2MTUgTDYxMC4wNTQxLDI3My43NjE1IEw2MTAuMDU0MSwyMTcuMDc2NSBDNjEwLjA1NDEsMTk2LjcxNTUgNjIwLjcxNDEsMTg2Ljk1NDUgNjMwLjY2MzEsMTg2Ljk1NDUgQzYzOS42NjYxLDE4Ni45NTQ1IDY0Ny45NDMxLDE5Mi43ODI1IDY0Ny45NDMxLDIwNy44MjA1IEw2NDcuOTQzMSwyNzMuNzYxNSBMNjc5LjQyMTEsMjczLjc2MTUgTDY3OS40MjExLDIxNy4wNzY1IEM2NzkuNDIxMSwxOTYuNDg0NSA2ODkuODI2MSwxODYuOTU0NSA3MDAuMjM4MSwxODYuOTU0NSBDNzA4LjU2NjEsMTg2Ljk1NDUgNzE3LjM1MzEsMTkzLjAxMjUgNzE3LjM1MzEsMjA3LjU4OTUgTDcxNy4zNTMxLDI3My43NjE1IEw3NDguODI1MSwyNzMuNzYxNSBMNzQ4LjgyNTEsMTk3LjY0MDUgQzc0OC44MjUxLDE3Mi44ODM1IDczMi4xNjYxLDE2MC4zOTA1IDcxMi4wMzcxLDE2MC4zOTA1IiBpZD0iRmlsbC01IiBmaWxsPSIjRkZGRkZGIj48L3BhdGg+CiAgICAgICAgPHBhdGggZD0iTTQxMi42ODk1LDE2My42Mjg3IEwzODQuMTQ5NSwxNjMuNjI4NyBMMzg0LjE0OTUsMTUyLjQzMDcgQzM4NC4xNDk1LDEzNy44NTU3IDM5Mi40Njg1LDEzMy42OTA3IDM5OS42NDE1LDEzMy42OTA3IEM0MDcuNTY4NSwxMzMuNjkwNyA0MTMuNzQ0NSwxMzcuMjA2NyA0MTMuNzQ0NSwxMzcuMjA2NyBMNDIzLjQ1NjUsMTE0Ljk5NjcgQzQyMy40NTY1LDExNC45OTY3IDQxMy42MTU1LDEwOC41NjM3IDM5NS43MDc1LDEwOC41NjM3IEMzNzUuNTc4NSwxMDguNTYzNyAzNTIuNjcyNSwxMTkuOTAwNyAzNTIuNjcyNSwxNTUuNTMwNyBMMzUyLjY3MjUsMTYzLjYyODcgTDMwNC44OTc1LDE2My42Mjg3IEwzMDQuODk3NSwxNTIuNDMwNyBDMzA0Ljg5NzUsMTM3Ljg1NTcgMzEzLjIxNTUsMTMzLjY5MDcgMzIwLjM4ODUsMTMzLjY5MDcgQzMyNC40NjI1LDEzMy42OTA3IDMyOS45NDU1LDEzNC42MzM3IDMzNC40OTE1LDEzNy4yMDY3IEwzNDQuMjAzNSwxMTQuOTk2NyBDMzM4LjQwNDUsMTExLjU5ODcgMzI5LjA4NzUsMTA4LjU2MzcgMzE2LjQ1NDUsMTA4LjU2MzcgQzI5Ni4zMjU1LDEwOC41NjM3IDI3My40MTk1LDExOS45MDA3IDI3My40MTk1LDE1NS41MzA3IEwyNzMuNDE5NSwxNjMuNjI4NyBMMjU1LjE0MjUsMTYzLjYyODcgTDI1NS4xNDI1LDE4Ny45MjI3IEwyNzMuNDE5NSwxODcuOTIyNyBMMjczLjQxOTUsMjczLjc2MTcgTDMwNC44OTc1LDI3My43NjE3IEwzMDQuODk3NSwxODcuOTIyNyBMMzUyLjY3MjUsMTg3LjkyMjcgTDM1Mi42NzI1LDI3My43NjE3IEwzODQuMTQ5NSwyNzMuNzYxNyBMMzg0LjE0OTUsMTg3LjkyMjcgTDQxMi42ODk1LDE4Ny45MjI3IEw0MTIuNjg5NSwxNjMuNjI4NyBaIiBpZD0iRmlsbC03IiBmaWxsPSIjRkZGRkZGIj48L3BhdGg+CiAgICAgICAgPG1hc2sgaWQ9Im1hc2stMiIgZmlsbD0id2hpdGUiPgogICAgICAgICAgICA8dXNlIHhsaW5rOmhyZWY9IiNwYXRoLTEiPjwvdXNlPgogICAgICAgIDwvbWFzaz4KICAgICAgICA8ZyBpZD0iQ2xpcC0xMCI+PC9nPgogICAgICAgIDxwb2x5Z29uIGlkPSJGaWxsLTkiIGZpbGw9IiNGRkZGRkYiIG1hc2s9InVybCgjbWFzay0yKSIgcG9pbnRzPSI0MzEuNDc4IDI3My43NjIgNDYyLjkyNSAyNzMuNzYyIDQ2Mi45MjUgMTYzLjYyOSA0MzEuNDc4IDE2My42MjkiPjwvcG9seWdvbj4KICAgICAgICA8cGF0aCBkPSJNNjE0Ljk0NDMsOS45OTk4IEM1MjkuOTEzMyw5Ljk5OTggNDU0LjE0MjMsNjkuMDE4OCA0MzIuNjM4MywxNDQuOTAxOCBMNDYzLjQ0ODMsMTQ0LjkwMTggQzQ4MS40MTczLDg4LjQwMjggNTQyLjM5NzMsMzguNzkzOCA2MTQuOTQ0MywzOC43OTM4IEM3MDMuMTM2MywzOC43OTM4IDc3OS4zNDQzLDEwNS45Mjg4IDc3OS4zNDQzLDIxMC40NDg4IEM3NzkuMzQ0MywyMzMuOTAzOCA3NzYuMzEwMywyNTUuMDcwOCA3NzAuNTQ4MywyNzMuNzYxOCBMODAwLjQ0ODMsMjczLjc2MTggTDgwMC43NDQzLDI3Mi43MzE4IEM4MDUuNjUwMywyNTMuNDQ5OCA4MDguMTM5MywyMzIuNDk5OCA4MDguMTM5MywyMTAuNDQ4OCBDODA4LjEzOTMsOTMuODkyOCA3MjMuMjA4Myw5Ljk5OTggNjE0Ljk0NDMsOS45OTk4IiBpZD0iRmlsbC0xMSIgZmlsbD0iI0ZGRkZGRiIgbWFzaz0idXJsKCNtYXNrLTIpIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==" alt="Affirm"/>');
                    window.checkout.togglePaymentSubmit('on');
                    break;
                case 'klarna':
                    $('.cart__paymentOption__submit').html('<span>BUY WITH KLARNA</span>');
                    window.checkout.togglePaymentSubmit('on');
                    break;
            }
        });

        $('.cart__paymentOption__submit').on('click', function () {
            window.checkout.togglePaymentSubmit('off');
            validationService.get().then(order => {
                if (order.status.cart && order.status.user) {
                    window.checkout.checkStockValidity(window.checkout.submitPaymentInformation);
                } else if (order.status.cart && !order.status.user) {
                        window.location = '/checkout/addresses'
                } else {
                    // Error isn't needed as cart will be empty
                    window.location = '/cart'
                }
            });
        });

        if ($('.cart__paymentOption').length) {
            $('.cart__paymentOption:first').click();
        }

        window.addEventListener('blur', function () {
            window.setTimeout(function () {
                if ( (document.activeElement == document.querySelector('#klarna-pay-later-main')) || (document.activeElement == document.querySelector('#klarna-pay-over-time-main')) ) {
                    $('.cart__paymentOption.selected').removeClass('selected');
                    $('.cart__paymentOption[data-gatewayhandle="klarna"]').addClass('selected');
                    $('.cart__paymentOption__submit').html('<span>BUY WITH KLARNA</span>');
                    window.checkout.togglePaymentSubmit('on');
                }
            }, 0);
        });
    },

    editContactInformation: function(){
        $('#checkout-step-1').removeClass('is--hidden');
        $('#checkout-step-2').addClass('is--hidden');
        $('#checkout-step-3').addClass('is--hidden');
        $('.checkout__customerInfo__summary').addClass('is--hidden');
    },

    editShippingMethod: function(){
        $('#checkout-step-1').addClass('is--hidden');
        $('#checkout-step-2').removeClass('is--hidden');
        $('#checkout-step-3').addClass('is--hidden');
        $('#checkout-summary-shipping-method-container').addClass('is--hidden');
    },

    attachOrderReviewToggleHandlers: function(){
        $('#checkout-orderReview-mobileToggle').on('click', function(){
            $('.checkout__orderReview__toggle').toggleClass('opened');
            $('#checkout-orderReview-details').toggleClass('is--hidden');
        });
    },

    togglePaymentSubmit: function(nextState){
        if(nextState == 'off'){
            $('.checkoutValidate--btn', document).prop('disabled', true);
            $('.cart__paymentOption__submit').attr('disabled', 'disabled');
        } else {
            $('.checkoutValidate--btn', document).prop('disabled', false);
            $('.cart__paymentOption__submit').removeAttr('disabled');
        }
    },

    checkStockValidity: function(nextFn){
        $.ajax({
            url: '/',
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            data: {
                action: '/keiser-contact-helpers/keiser-contact-helpers/check-order-validity'
            },
            success: function(response){
                if (response.status == 'success'){
                    if(response.orderValid){
                        nextFn();
                    } else {
                        alert('Sorry, one of the items in your cart is now out of stock. You have not been charged. For more information, please visit our COVID-19 information page.');
                        window.location.href = 'https://www.keiser.com/pages/keiser-covid-19-update';
                    }
                } else if(response.error){
                    if(response.error == 'Cart is empty'){
                        window.location.href = '/cart';
                    } else {
                        $('#checkoutError__modal__trigger').click();
                    }
                }
            },
            error: function (e) {
                $('#checkoutError__modal__trigger').click();
            }
        });
    },

    submitPaymentInformation: function(){
        var $selectedPaymentOption = $('.cart__paymentOption.selected');
        switch ($selectedPaymentOption.attr('data-gatewayhandle')) {
            case 'keiserStripe':
                $('#gatewayId').val($selectedPaymentOption.attr('data-gatewayid'));
                window.processStripePayment();
                break;
            case 'affirm':
                $('#gatewayId').val($selectedPaymentOption.attr('data-gatewayid'));
                affirm.checkout.open({
                    onFail: function () {
                        window.checkout.togglePaymentSubmit('on');
                    },
                    onSuccess: function (response) {
                        $('#gatewayToken').val(response.checkout_token);
                        window.checkout.checkStockValidity(window.checkout.processAffirmPayment);
                    }
                });
                break;
            case 'klarna':
                $('#gatewayId').val($selectedPaymentOption.attr('data-gatewayid'));
                try {
                    Klarna.Payments.authorize({
                        payment_method_category: 'pay_over_time'
                    }, function (res) {
                        if(res.approved && res.authorization_token !== undefined){
                            $('#gatewayToken').val(res.authorization_token);
                            window.checkout.checkStockValidity(window.checkout.processKlarnaPayment);
                        } else if(!res.approved && res.show_form){
                            alert(res.error.toString());
                            window.checkout.togglePaymentSubmit('on');
                        } else {
                            $('#klarna-payments-container').hide();
                            window.checkout.togglePaymentSubmit('on');
                        }
                    })
                } catch (e) {
                    console.log(e);
                }
                break;
        }
    },

    processAffirmPayment: function(){
        rudderanalytics.track('Payment Info Entered', {
            checkout_id: window.cartNumber,
            order_id: window.cartNumber,
            step: 4,
            payment_method: 'Affirm',
            site: window.location.hostname
        });
        rudderanalytics.track('Checkout Step Completed', {
            checkout_id: window.cartNumber,
            step: 4,
            payment_method: 'Affirm',
            site: window.location.hostname
        });
        if (typeof ga !== 'undefined') {
            ga('keisergtm.ec:setAction', 'checkout_option', {'step': 4, 'option': 'Affirm'});
            ga('keisergtm.send', 'event', 'Checkout', 'Option', {
                hitCallback: window.createFunctionWithTimeout(window.submitPaymentForm),
            });
        } else {
            window.submitPaymentForm();
        }
    },

    processKlarnaPayment: function(){
        rudderanalytics.track('Payment Info Entered', {
            checkout_id: window.cartNumber,
            order_id: window.cartNumber,
            step: 4,
            payment_method: 'Klarna',
            site: window.location.hostname
        });
        rudderanalytics.track('Checkout Step Completed', {
            checkout_id: window.cartNumber,
            step: 4,
            payment_method: 'Klarna',
            site: window.location.hostname
        });
        if (typeof ga !== 'undefined') {
            ga('keisergtm.ec:setAction', 'checkout_option', {'step': 4, 'option': 'Klarna'});
            ga('keisergtm.send', 'event', 'Checkout', 'Option', {
                hitCallback: window.createFunctionWithTimeout(window.submitPaymentForm),
            });
        } else {
            window.submitPaymentForm();
        }
    }

};

window.submitPaymentForm = function(){
    $('#payment-form').submit()
};
