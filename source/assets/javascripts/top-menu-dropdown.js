var throttle = require('lodash.throttle')

var dropDownMenu = {
    $menuItem: $('.topMenu--item--dropDown .topMenu__item__link'),

    hideAllDropdowns: function(evt) {
        if(!evt.target.classList.contains('topMenu__item__link')) 
        {
            dropDownMenu.$menuItem.removeClass('active')
            $('.topMenu__item ul').removeClass('active')
        }
    },

    openDropdown: function(evt) {
        evt.preventDefault()
        $('.topMenu__item ul').removeClass('active')
        dropDownMenu.$menuItem.not($(this)).removeClass('active')
        $(this).toggleClass('active')

        if($(this).hasClass('active')) {
            $(this).parent().find('ul').addClass('active')
        }else {
            $(this).parent().find('ul').removeClass('active')
        }

    }

}

$(document).ready(function(){

    $(document).on('click', dropDownMenu.hideAllDropdowns)

    dropDownMenu.$menuItem.click(dropDownMenu.openDropdown)

})