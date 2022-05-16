$(document).ready(function(){

    $('.quantity__subtract').on('click', function(){
        var $quantityCounter = $(this).siblings('.quantity__counter');
        var quantity = parseInt($quantityCounter.html()) - 1;
        quantity = quantity > 1 ? quantity : 1;
        $quantityCounter.html('' + quantity);
        $('#' + $quantityCounter.attr('for')).val(quantity);
        var maxQty = $(this).parents('div.quantity:first').attr('data-max-qty');
        if(maxQty && quantity < maxQty){
            $(this).siblings('.quantity__add').removeClass('disabled');
        }
    });

    $('.quantity__add').on('click', function(){
        var $quantityCounter = $(this).siblings('.quantity__counter');
        var quantity = parseInt($quantityCounter.html()) + 1;
        var maxQty = $(this).parents('div.quantity:first').attr('data-max-qty');
        var purchasableId = $quantityCounter.attr('for');
        if(maxQty && quantity > maxQty){
            $('.maxQtyAlert[for='+ purchasableId +']').show();
            $(this).addClass('disabled');
        } else {
            $quantityCounter.html('' + quantity);
            $('#' + purchasableId).val(quantity);
        }
    });

    function renderTShirtSizeListOptions(options){
        var $tShirtSizeList = $('.tShirtSize__list__size');
        $tShirtSizeList.html('');
        $.each(options, function(size, availability){
            var option = '<li class="margin--left--none textCenter tShirtSize__list__size__option ';
            if(!availability){
                option += 'disabled';
            }
            option += '" data-option="' + size + '">' + size.toUpperCase() + '</li>';
            $tShirtSizeList.append(option);
        });
        $('.tShirtSize__list__option__disabled').attr('title', 'This size is currently out of stock');
        $tShirtSizeList = $('.tShirtSize__list__size li:not(.disabled)');
        $tShirtSizeList.on('click', function(){
            $('.tShirtSize__list__size li.active').removeClass('active');
            $(this).addClass('active');
            $('#tShirtSize__error').hide();
        });
        $('#tShirtOptionsDivider').show();
    };

    $('.tShirtSize__list__style li').each(function(){
        var available = false;
        $.each(tShirtSizeAvailability[$(this).attr('data-option')], function(size, availability){
            if(availability){
                available = true;
            }
        });
        if(!available){
            $(this).addClass('disabled');
        }
    });

    var $tShirtSizeList = $(".tShirtSize__list__style li:not('.disabled')");
    $tShirtSizeList.filter(':not(.active)').on('click', function(){
        $('.tShirtSize__list__style li.active').removeClass('active');
        $(this).addClass('active');
        renderTShirtSizeListOptions(tShirtSizeAvailability[$(this).attr('data-option')]);
    });

    $('.product__promoItem').on('change', function(){
        if($(this).val()){
            $('#product__promoItem__error').hide();
        }
    });

    $('.ajaxAddToCart').on('submit', function(e){
        e.preventDefault();
        var formData = $(this).serializeArray();
        if($('.tShirtSize__list__size[for=tShirt' + $('[name="purchasableId"]', $(this)).val() + ']').length){
            var $size = $('.tShirtSize__list__size li.active');
            var $style = $('.tShirtSize__list__style li.active');
            if(!$size.length || !$style.length){
                $('#tShirtSize__error').removeClass('shake');
                $('ul.tShirtSize__list__style').removeClass('shake');
                $('#tShirtSize__error').show().addClass('shake');
                $('ul.tShirtSize__list__style').addClass('shake');
                return false;
            } else {
                var size = $size.attr('data-option').toUpperCase();
                var style = $style.attr('data-option');
                style = style.charAt(0).toUpperCase() + style.substr(1,style.length - 2) + "'s";
                formData.push({
                    name: 'options[tShirtSize]',
                    value: size + ' (' + style + ')'
                });
            }
        } else if($('[data-clothing-size-list]').length){
            var $list = $('[data-clothing-size-list]');
            var $errorBox = $('#clothingSize__error')
            if ($errorBox.length && !$list.find(".active").length) {
                $errorBox.removeClass('is--hidden shake');
                $list.removeClass('shake');
                $errorBox.show().addClass('shake');
                $list.addClass('shake');
                return false;
            }
        }
        var $promoItemDropdown = $('.product__promoItem[for=promoItem' + $('[name="purchasableId"]', $(this)).val() + ']');
        if($promoItemDropdown.length){
            if(!$promoItemDropdown.val()){
                $('#product__promoItem__error').removeClass('shake');
                $promoItemDropdown.removeClass('shake');
                $('#product__promoItem__error').show().addClass('shake');
                $promoItemDropdown.addClass('shake');
                return false;
            } else {
                formData.push({
                    name: 'options[promoItem]',
                    value: $promoItemDropdown.val()
                });
            }
        }
        var $submitLabel = $('label[for=submit' + $('[name="purchasableId"]', $(this)).val() + ']');
        var $self = $(this);

        var defaultVariantPurchasableId = $(this).attr('id').replace('form','');

        if($('#whiteGlove' + defaultVariantPurchasableId ).length && $('#whiteGlove' + defaultVariantPurchasableId ).is(':checked') ){
            formData.push({
                name: 'options[whiteGloveDelivery]',
                value: true
            })
        }

        if($('#'+ defaultVariantPurchasableId + '__productQuestionnaire__trigger').length > 0){
            $('#'+ defaultVariantPurchasableId + '__productQuestionnaire__trigger').click();
            return;
        }

        $.ajax({
            type: 'POST',
            url: '/',
            data: formData,
            headers: {
                'Accept': 'application/json'
            },
            beforeSend: function(){
                $submitLabel.prop('disabled', true);
                $submitLabel.html('<img src="/assets/images/tail-spin.svg" class="margin--right--half loading--tailspin" /> Adding...')
            },
            success: function(response){
                if(response.cart){
                    $submitLabel.html('Added')
                    $submitLabel.addClass('button--disabled');
                    $('.cartIconContainer').removeClass('is--hidden');
                    $('.cart__qty').html(response.cart.totalQty).removeClass('is--hidden');
                    $('.cart__link').removeClass('is--hidden');

                    // Added to ensure tracking only fires after an Ajax item is successfully added to the cart.
                    ga('keisergtm.ec:addProduct', {'id': $self.attr('data-sku'),
                      'name': $self.attr('data-product-name'),
                      'category': $self.attr('data-category'),
                      'price': $self.attr('data-price'),
                      'quantity': $self.find('[name="qty"]').val()
                    });
                    ga('keisergtm.ec:setAction', 'add');

                    ga('keisergtm.send', 'event', 'ecommerce', 'add to cart', {'nonInteraction': true});

                    rudderanalytics.track('Product Added',{
                        product_id: $self.attr('data-sku'),
                        sku: $self.attr('data-sku'),
                        category: $self.attr('data-category'),
                        name: $self.attr('data-product-name'),
                        brand: 'Keiser',
                        price: parseFloat($self.attr('data-price')),
                        quantity: parseFloat($self.find('[name="qty"]').val()),
                        url: $self.attr('data-product-url'),
                        image_url: $self.attr('data-product-image-url'),
                        site: window.location.hostname
                    });

                    var uri = new window.URI(window.location.href);
                    if(uri.filename() == 'cart'){
                        window.location.reload();
                    } else {
                        var purchasableId = $self.attr('id');
                        $('div[for="'+ purchasableId +'"]').show();
                    }
                }
            },
            complete: function(){
                $submitLabel.prop('disabled', false);
            }
        });
    });

    $('.jumpLinkContainer a').on('click', function(e){
        e.preventDefault();
        var $div = $($(this).attr('href'));
        window.smoothScroll($div.offset().top);
        return false;
    });

    $('.clothingSize__list__size__option').on('click', function(){
        var productId = $(this).parent().attr('for');
        var $addToCartForm = $('#form' + productId);
        $addToCartForm.attr({
            'data-product-name' : $(this).attr('data-product-name'),
            'data-sku': $(this).attr('data-sku'),
            'data-price': $(this).attr('data-price')
        });
        $('input[name="purchasableId"]', $addToCartForm).val($(this).attr('data-purchasableId'));
        $('#price' + productId).text($(this).attr('data-price'));
        var $quantity = $('#quantity' + productId);
        var previousAllowedQty = parseInt($quantity.attr('data-max-qty'));
        $quantity.attr('data-max-qty', $(this).attr('data-allowedQty'));
        $('[data-clothing-size-list] li.active').removeClass('active');
        $(this).addClass('active');
        $('#clothingSize__error').hide();
        var newAllowedQty = parseInt($(this).attr('data-allowedQty'))
        if(newAllowedQty > previousAllowedQty){
            $quantity.children('.quantity__add').first().removeClass('disabled');
        }
    });

    function updateCheckboxClasses($elem){
        var $checkBoxDiv = $('div.productReview__checkbox__input[data-for="' + $elem.attr('id') +'"]');
        if($elem.is(':checked')){
            $checkBoxDiv.addClass('productReview__checkbox__input__checked');
        } else {
            $checkBoxDiv.removeClass('productReview__checkbox__input__checked');
        }
    }

    $('.whiteGlove__checkbox__input, .whiteGlove__checkbox__label').on('click', function(){
        var checkboxId = '#' + $(this).attr('data-for');
        var $checkbox = $(checkboxId);
        if($checkbox.is(':checked')){
            $checkbox.prop('checked', false);
        } else {
            $checkbox.prop('checked', true);
        }
        if($checkbox.hasClass('whiteGlove__checkbox__input__cart')){
            updateWhiteGloveShippingForCartItem($checkbox);
        }
        updateCheckboxClasses($checkbox);
    });

    function updateWhiteGloveShippingForCartItem($checkbox){
        var action = '/keiser-contact-helpers/keiser-contact-helpers/remove-white-glove-delivery-from-cart';
        if($checkbox.is(':checked')){
            action = '/keiser-contact-helpers/keiser-contact-helpers/add-white-glove-delivery-to-cart';
        }
        var data = {
            'action': action
        };
        data['lineItemId'] = $checkbox.attr('name');
        $.ajax({
            url: '/',
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            data: data,
            success: function(response){
                if(response.success){
                    window.location.reload();
                }
            },
            error: function(response){}
        });
    }
});
