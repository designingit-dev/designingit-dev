$(document).ready(function(){

    function collapseSupportHomeSubcategory(){
        $('.supportHomeSubcategory').filter(function(index){
            return parseInt(index / 4) > 0 ;
        }).hide();
        $('#supportHome__moreProducts').html('More Products <img class="inline--block faq__icon" src="/assets/images/plus-button-white.svg" alt="plus">').attr('data-state', 'collapsed');
    }

    function addSupportSubcategoriesClearfix(){
        var columnMappings = {
            'extraSmall': 2,
            'small': 4,
            'medium': 6,
            'large': 6
        };
        $.each(columnMappings, function(i, v){
            var counter = 1;
            $('.subcategoriesList').each(function(){
                if (counter % v == 0) {
                    $(this).after('<div class="supportSubcategoriesClearfix clearfix__' + i + '"></div>');
                }
                counter++;
            });
        });
    }

    $('#announcementsCarousel').slick({
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 8000
    });

    $('.supportLinksList__type').on('click', function(){
        if($('.supportLinksList__type.active').attr('data-type') != $(this).attr('data-type')){
            if($(this).attr('data-type') == 'all'){
                $('.supportLinksList__link').show();
            } else {
                $('.supportLinksList__link').hide();
                $('.supportLinksList__link[data-type=' + $(this).attr('data-type') +']').show();
            }
            $('.supportLinksList__type.active').removeClass('active');
            $(this).addClass('active');
        }
    });

    if($('.supportHomeSubcategory').length > 4){
        $('#supportHome__supportSubcategoriesContainer').append('' +
        '<div class="l--medium--12 textCenter">' +
            '<button class="button button--reverse" id="supportHome__moreProducts" data-state="collapsed"></button>' +
        '</div>');
        collapseSupportHomeSubcategory();
        $('#supportHome__moreProducts').on('click', function(){
            if($(this).attr('data-state') != 'collapsed'){
                collapseSupportHomeSubcategory()
            } else {
                $('.supportHomeSubcategory').show();
                $('#supportHome__moreProducts').html('Less Products <img class="inline--block faq__icon is--rotated" src="/assets/images/plus-button-white.svg" alt="plus">').attr('data-state', 'expanded');
            }
        });
    }

    addSupportSubcategoriesClearfix();

    $('.show-steps').on('click', function(e){
      e.preventDefault();
      $('.hidden-steps').addClass('active');
    });
});
