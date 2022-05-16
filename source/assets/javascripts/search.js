$(document).on('ready', function(){

    if(typeof algoliaApplicationId !== 'undefined' && algoliaSearchApiKey && algoliaIndexPrefix ){

        function renderGlobalSearchResults(content) {
            $('.globalSearchResultsContainer').parent().removeClass('is--hidden').addClass('is--visible');
            $('.globalSearchResultsContainer').html(function () {
                return $.map(content.hits, function (hit) {
                    var linkType = '';
                    if (typeof hit.linkType !== 'undefined' && hit.linkType !== 'link') {
                        linkType = '<span class="searchResult__title__linkType">' + hit.linkType + '</span>'
                    }
                    var highlight = trim(getHighlight(hit), 30);
                    var searchResultTitleClass = '';
                    hit = processReferenceTag(hit);
                    hit = replaceS3Url(hit);
                    if (!hit.url) {
                        searchResultTitleClass = 'searchResult__title__noLink';
                        hit.url = '';
                    }
                    return '' +
                        '<div class="row searchResult" id="algolia'+ hit.objectID +'">' +
                        '<div class="l--medium--12 columns margin--bottom--half">' +
                        '<p class="searchResult__breadcrumb margin--bottom--none">' + hit.breadcrumb + '</p>' +
                        '<a class="text--red searchResult__title ' + searchResultTitleClass + '" href="' + hit.url + '"><h3 class="searchResult__title__heading">' + hit.title + '</h3>' + linkType + '</a>' +
                        '<p class="searchResult__body">' + highlight + '</p>' +
                        '</div>' +
                        '</div>';
                });
            });
            var query = $('.algoliaSearchBox:focus').val();
            $('.globalSearchResultsContainer').append('' +
                '<div class="row">' +
                '<div class="l--medium--8">' +
                '<a href="/search?q=' + query + '" class="button" id="globalSearchRedirectButton"><svg class="dropdown--icon"><use xlink:href="#cta-link-chevron-12"></use></svg>SEE ALL RESULTS</a>' +
                '</div>' +
                '<div class="l--medium--4 algoliaLogoContainer">' +
                '<img src="/assets/images/search/search-by-algolia.svg" />' +
                '</div>' +
                '</div>'
            );
        }

        function clearGlobalSearch() {
            $('.algoliaSearchBox').val('');
            $('.globalSearchResultsContainer').parent().removeClass('is--visible').addClass('is--hidden');
            $('.algoliaClearSearch').removeClass('display--inline--block').addClass('is--hidden');
            $('.algoliaSearchAgain').addClass('display--inline--block').removeClass('is--hidden');
            $('.globalSearchResultsContainer').html('');
        }

        function renderResults(content) {
            $('#algoliaResultsContainer').html(function () {
                return $.map(content.hits, function (hit) {
                    var linkType = '';
                    if (typeof hit.linkType !== 'undefined' && hit.linkType !== 'link') {
                        linkType = '<span class="searchResult__title__linkType text--black">' + hit.linkType + '</span>'
                    }
                    var highlight = getHighlight(hit);
                    var searchResultTitleClass = '';
                    hit = processReferenceTag(hit);
                    hit = replaceS3Url(hit);
                    if (!hit.url) {
                        searchResultTitleClass = 'searchResult__title__noLink';
                        hit.url = '';
                    }
                    return '' +
                        '<div class="row searchResult" id="algolia'+ hit.objectID +'">' +
                        '<div class="l--extraSmall--2 l--small--1 l--medium--1 columns">' +
                        '<img class="searchResult__recordTypeImage" src="/assets/images/search/record-type-' + hit.type + '.png" />' +
                        '</div>' +
                        '<div class="l--extraSmall--10 l--small--11 l--medium--11 columns">' +
                        '<p class="searchResult__breadcrumb margin--bottom--none">' + hit.breadcrumb + '</p>' +
                        '<a class="text--red searchResult__title ' + searchResultTitleClass + '" href="' + hit.url + '"><h3 class="searchResult__title__heading">' + hit.title + '</h3>' + linkType + '</a>' +
                        '<p class="searchResult__body margin--bottom--half">' + highlight + '</p>' +
                        '</div>' +
                        '</div>';
                });
            });
            $('#totalResultsCount').text(content.nbHits + ' results');
        }

        function renderFacets(content) {
            $('.searchResultsFilters').html(function () {
                return $.map(content.getFacetValues('type'), function (facet) {
                    var filterImage = '';
                    var filterActive = '';
                    var searchResultsFilterClass = '';
                    if (facet.isRefined) {
                        filterImage = '<img class="searchResultsFilter__image" src="/assets/images/search/record-type-' + facet.name + '.png" />';
                        filterActive = 'true';
                        searchResultsFilterClass = 'searchResultsFilter__active';
                    } else {
                        filterImage = '<img class="searchResultsFilter__image" src="/assets/images/search/filter-' + facet.name + '.png" />';
                        filterActive = 'false'
                    }
                    return '' +
                        '<div class="row">' +
                        '<a class="searchResultsFilter ' + searchResultsFilterClass + '" data-facet="' + facet.name + '" data-active="' + filterActive + '">' +
                        '<div class="l--extraSmall--2 l--small--1 l--medium--2 columns margin--bottom--half">' +
                        filterImage +
                        '</div>' +
                        '<div class="l--extraSmall--10 l--small--11 l--medium--10 columns margin--bottom--half searchResultsFilter__name">' +
                        '<h3>' + facet.name + '</h3>' +
                        '<span class="text--black">' + facet.count + '</span>' +
                        '<img class="searchResultsFilter__remove" src="/assets/images/search/remove-filter.png" />' +
                        '</div>' +
                        '</a>';
                    '</div>';
                });
            });
        }

        function renderPagination(content) {
            var i = 0;
            var html = '';
            for (i = 0; i < content.nbPages; i++) {
                html += '<a class="text--large searchResultsPagination__link" data-page="' + i + '"><span>' + (i + 1) + '</span></a>';
            }
            $('#searchResultsPagination').html(html);
            $('#searchResultsPagination').slick({
                slidesToShow: 5,
                slidesToScroll: 5,
                arrows: true,
                infinite: false,
                speed: 600,
                responsive: [
                    {
                        breakpoint: 500,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4
                        }
                    },
                ]
            });
            $('#searchResultsPagination').slick('slickGoTo', content.page, true);
            $('a.searchResultsPagination__link[data-page="' + content.page + '"]').addClass('searchResultsPagination__link__active');
        }

        function search(query) {
            refreshPagination = true;
            refreshFacets = true;
            if (query) {
                helper.setQuery(query).search();
                $('#searchPageTitle').text('RESULTS FOR "' + query + '"');
            } else {
                helper.search();
            }
        }

        function trim(text, limit) {
            var textHash = text.split(" ");
            if (textHash.length > limit) {
                textHash = textHash.splice(0, limit).join(" ") + "...";
                return textHash;
            }
            return text;
        }

        function getHighlight(hit) {
            var highlight = '';
            var highlights = [];
            $.each(hit._highlightResult, function (i, result) {
                if (i !== 'title') {
                    if (Array.isArray(result)) {
                        $.each(result, function (j, subResult) {
                            if (subResult.matchLevel !== 'none') {
                                highlights.push({'matchLevel': subResult.matchLevel, 'highlight': subResult.value});
                            }
                        });
                    } else {
                        if (result.matchLevel !== 'none') {
                            highlights.push({'matchLevel': result.matchLevel, 'highlight': result.value});
                        }
                    }
                }
            });
            if (hit.section == 'supportFAQ') {
                highlight = hit.supportFAQAnswer;
            } else if (hit.section == 'keiserRepresentatives') {
                highlight = hit.email + '<br>' + hit.contactBlock;
            } else if (hit.section == 'options') {
                highlight = hit.body;
            } else if (hit.section == 'testimonials') {
                highlight = hit.quote + '<br>' + hit.citation;
            } else if (highlights.length > 1) {
                highlights.sort(function (a, b) {
                    if (a.matchLevel == 'partial' && b.matchLevel == 'partial') {
                        return b.highlight.length - a.highlight.length;
                    } else if (a.matchLevel == 'partial' && b.matchLevel == 'full') {
                        return 1;
                    } else if (a.matchLevel == 'full' && b.matchLevel == 'partial') {
                        return -1;
                    } else if (a.matchLevel == 'full' && b.matchLevel == 'full') {
                        return b.highlight.length - a.highlight.length;
                    }
                });
                highlight = trimHighlight(highlights[0].highlight);
            } else if (highlights.length == 1) {
                highlight = trimHighlight(highlights[0].highlight);
            } else {
                var firstHighlightKey = Object.keys(hit._highlightResult)[0];
                if (firstHighlightKey == 'title' && hit.section == 'products') {
                    highlight = hit.body;
                } else {
                    var firstHighlight = hit._highlightResult[firstHighlightKey];
                    if (Array.isArray(firstHighlight)) {
                        highlight = trimHighlight(firstHighlight[0].value);
                    } else {
                        highlight = trimHighlight(firstHighlight.value);
                    }
                }
            }
            return highlight;
        }

        var indexPrefix = algoliaIndexPrefix;
        var client = algoliasearch(algoliaApplicationId, algoliaSearchApiKey);
        var helper = algoliasearchHelper(client, indexPrefix + 'master', {
            'disjunctiveFacets': ['type'],
            'hitsPerPage': 8,
            'filters': 'postDate <=' + serverTimestamp + ' AND expiryDate > ' + serverTimestamp
        });

        var globalSearchHelper = algoliasearchHelper(client, indexPrefix + 'master', {
            'hitsPerPage': 2,
            'filters': 'postDate <=' + serverTimestamp + ' AND expiryDate > ' + serverTimestamp
        });

        var refreshPagination = false;
        var refreshFacets = false;

        globalSearchHelper.on('result', function (content) {
            renderGlobalSearchResults(content);
        });

        helper.on('result', function (content) {
            renderResults(content);
            if (refreshFacets) {
                renderFacets(content);
                refreshFacets = false;
            }
            if (refreshPagination) {
                renderPagination(content);
                refreshPagination = false;
            }
        });

        $('.algoliaSearchBox').on('keyup', function () {
            var query = $(this).val();
            if (query.length > 0) {
                globalSearchHelper.setQuery($(this).val()).search();
                $('.algoliaClearSearch').removeClass('is--hidden').addClass('display--inline--block');
                $('.algoliaSearchAgain').addClass('is--hidden').removeClass('display--inline--block');
            } else {
                clearGlobalSearch();
            }
        });

        $('.searchResultsFilters').on('click', '.searchResultsFilter', function (e) {
            e.stopPropagation();
            var target = $(e.target);
            if (!target.is('.searchResultsFilter__remove') && $(this).attr('data-active') != 'true') {
                refreshPagination = true;
                $('#searchResultsPagination').slick('unslick');
                $(this).attr('data-active', 'true');
                $(this).addClass('searchResultsFilter__active');
                $('.searchResultsFilter__image', $(this)).attr('src', '/assets/images/search/record-type-' + $(this).attr('data-facet') + '.png');
                uri.addQuery('typeFilter', $(this).attr('data-facet'));
                uri.removeQuery('pageNum');
                history.replaceState({}, '', uri.resource());
                helper.addDisjunctiveFacetRefinement('type', $(this).attr('data-facet')).search();
            }
        });

        $('.searchResultsFilters').on('click', '.searchResultsFilter__remove', function (e) {
            e.stopPropagation();
            var $searchResultsFilter = $(this).parents('.searchResultsFilter').first();
            $searchResultsFilter.attr('data-active', 'false');
            $searchResultsFilter.removeClass('searchResultsFilter__active');
            $('.searchResultsFilter__image', $searchResultsFilter).attr('src', '/assets/images/search/filter-' + $searchResultsFilter.attr('data-facet') + '.png');
            refreshPagination = true;
            $('#searchResultsPagination').slick('unslick');
            uri.removeQuery('typeFilter', $searchResultsFilter.attr('data-facet'));
            uri.removeQuery('pageNum');
            history.replaceState({}, '', uri.resource());
            helper.removeDisjunctiveFacetRefinement('type', $searchResultsFilter.attr('data-facet')).search();
        });

        $('#searchResultsPagination').on('click', 'a.searchResultsPagination__link', function () {
            var pageNumber = $(this).attr('data-page');
            $('a.searchResultsPagination__link.searchResultsPagination__link__active').removeClass('searchResultsPagination__link__active');
            $('a.searchResultsPagination__link[data-page="' + pageNumber + '"]').addClass('searchResultsPagination__link__active');
            uri.setQuery('pageNum', parseInt(pageNumber) + 1);
            history.replaceState({}, '', uri.resource());
            helper.setPage(pageNumber).search();
            $('html, body').animate({
                scrollTop: ($('#algoliaResultsContainer').offset().top - parseInt($('#js--stickyHeaderSpacer').css('height').replace('px', '')) - 4)
            }, 500);
        });

        $('#searchAgainInput').on('keyup', function () {
            var query = $(this).val();
            if (query.length > 1) {
                $('#searchResultsPagination').slick('unslick');
                helper.removeDisjunctiveFacetRefinement('type');
                uri.removeQuery('typeFilter');
                uri.setQuery({'q': query, 'pageNum': 1});
                history.replaceState({}, '', uri.resource());
                search(query);
            }
        });

        $('.algoliaClearSearch').on('click', function (e) {
            clearGlobalSearch();
            e.stopPropagation();
        });

        $('#subMenu-search').on('shown.bs.dropdown', function () {
            $('#subMenu-search .algoliaSearchBox').focus();
            return true;
        });

        $('.algoliaSearchBoxContainer').on('click', function (e) {
            e.stopPropagation();
        });

        var uri = new URI(window.location.href);
        var queryParams = URI.parseQuery(uri.query());
        if (typeof queryParams['q'] !== 'undefined') {
            $('#searchAgainInput').attr('placeholder', queryParams['q']);
            $('#searchPageTitle').text('RESULTS FOR "' + queryParams['q'] + '"');
            helper.setQuery(queryParams['q']);
            if (typeof queryParams['typeFilter'] !== 'undefined') {
                if (typeof queryParams['typeFilter'] == 'object') {
                    $.each(queryParams['typeFilter'], function (i, type) {
                        helper.addDisjunctiveFacetRefinement('type', type);
                    });
                } else {
                    helper.addDisjunctiveFacetRefinement('type', queryParams['typeFilter']);
                }
            }
            if (typeof queryParams['pageNum'] !== 'undefined') {
                helper.setPage(parseInt(queryParams['pageNum']) - 1);
            } else {
                uri.addQuery('pageNum', 1);
                history.replaceState({}, '', uri.resource());
            }
            search('');
        }

        function trimHighlight(text){
            var regex = /(.*<em>.*<\/em>.*$)/gm;
            var matches;
            if( (matches = text.match(regex)) !== null ){
                return matches[0];
            }
            return text;
        }

        function processReferenceTag(hit){
            if( hit.url && hit.linkType == 'link' &&
                hit.url.substr(0,1) == '{' ){
                var referenceTagParts = hit.url.match(/\{asset\:([0-9]*)\}/);
                if(typeof referenceTagParts[1] !== 'undefined'){
                    var assetId = referenceTagParts[1];
                    var objectID = hit.objectID;
                    hit.url = '';
                    $.ajax({
                        url: '/',
                        method: 'POST',
                        // async: false, //Need to block rendering of this search result until the link is resolved from server
                        headers: {
                            'Accept': 'application/json'
                        },
                        data: {
                            'action': '/keiser-contact-helpers/keiser-contact-helpers/get-asset-link',
                            'assetId': assetId
                        },
                        success: function(response){
                            if(response.success){
                                $('#algolia' + objectID + ' a.searchResult__title:first').prop('href', response.url).removeClass('searchResult__title__noLink');
                            }
                        }
                    });
                }
            }
            return hit;
        }

        function replaceS3Url(hit){
            if(hit.url){
                hit.url = hit.url.replace(window.s3Bucket, window.s3BaseUrl);
            }
            return hit;
        }
    }
});
