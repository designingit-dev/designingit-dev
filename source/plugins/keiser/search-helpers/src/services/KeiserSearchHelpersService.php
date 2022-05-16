<?php

namespace keiser\searchhelpers\services;

use Craft;
use craft\elements\Entry;
use craft\base\Component;


class KeiserSearchHelpersService extends Component {

    public static $indexSettings = [
        'products' =>  [
            'attributes' => [
                'partNumber' => '',
                'body' => 'richText',
                'features' => 'productFeature',
                'image' => 'asset',
                'orderDescending' => 'number',
                'productCategories' => 'categories',
                'productLine' => 'tags'
            ],
            'searchableAttributes' => [
                'title',
                'partNumber',
                'features',
                'body'
            ],
            'attributesForFaceting' => [
                'features',
                'productCategories'
            ],
            'ranking' => [
                'desc(orderDescending)',
                'typo',
                'geo',
                'words',
                'filters',
                'proximity',
                'attribute',
                'exact',
                'custom'
            ]
        ],
        'jobs' => [
            'attributes' => [
                'body' => 'richText'
            ],
            'searchableAttributes' => [
                'title',
                'body'
            ]
        ],
        'supportArticles' => [
            'attributes' => [
                'richTextBody' => 'longRichText',
                'supportTags' => 'tags'
            ],
            'searchableAttributes' => [
                'title',
                'richTextBody',
                'supportTags'
            ]
        ],
        'supportCategories' => [
            'attributes' => [
                'arbitraryContentBlocks' => 'arbitraryContentBlocks'
            ],
            'searchableAttributes' => [
                'arbitraryContentBlocks'
            ]
        ],
        'supportHomepage' => [
            'attributes' => [
                'arbitraryContentBlocks' => 'arbitraryContentBlocks'
            ],
            'searchableAttributes' => [
                'arbitraryContentBlocks'
            ]
        ],
        'supportAnnouncements' => [
            'attributes' => [
                'announcementBody' => 'richText'
            ],
            'searchableAttributes' => [
                'announcementBody'
            ]
        ],
        'supportFAQ' => [
            'attributes' => [
                'supportFAQAnswer' => 'richText',
                'supportTags' => 'tags'
            ],
            'searchableAttributes' => [
                'supportFAQAnswer',
                'supportTags'
            ]
        ],
        'supportLinks' => [
            'attributes' => [
                'linkURL' => '',
                'supportPartsTags' => 'tags'
            ],
            'searchableAttributes' => [
                'title',
                'supportPartsTags'
            ]
        ],
        'supportCopyBlockWithLink' => [
            'attributes' => [
                'supportBlockCopy' => '',
            ],
        ],
        'supportLinkBlockWithIcon' => [
            'attributes' => [
                'supportLinkText' => ''
            ]
        ],
        'about' => [
            'attributes' => [
                'body' => 'richText',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders',
                'industryPartners' => 'industryPartners'
            ]
        ],
        'aboutTrainers' => [
            'attributes' => [
                'body' => 'richText',
                'copyBlockWithImage' => 'copyBlockWithImage',
                'trainer' => 'trainer'
            ]
        ],
        'aboutEnvironment' => [
            'attributes' => [
                'body' => 'richText',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders'
            ]
        ],
        'aboutCareers' => [
            'attributes' => [
                'body' => 'richText'
            ]
        ],
        'aboutEducation' => [
            'attributes' => [
                'body' => 'richText',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders'
            ]
        ],
        'aboutFinancing' => [
            'attributes' => [
                'body' => 'richText',
                'blockWithLink' => 'blockWithLink'
            ]
        ],
        'educationTrainers' => [
            'attributes' => [
                'body' => 'richText',
                'copyBlockWithImage' => 'copyBlockWithImage',
                'trainer' => 'trainer'
            ]
        ],
        'educationTraining' => [
            'attributes' => [
                'body' => 'richText',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders',
                'calendarHeader' => '',
            ]
        ],
        'fitnessEquipmentOverview' => [
            'attributes' => [
                'body' => 'richText',
                'categoryBlockWithImage' => 'categoryBlockWithImage',
            ]
        ],
        'generalPages' => [
            'attributes' => [
                'body' => 'richText',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders',
                'copyBlockWithImage' => 'copyBlockWithImage',
                'arbitraryContentBlocks' => 'arbitraryContentBlocks',
                'copyBlockWithLink' => 'copyBlockWithLink'
            ]
        ],
        'homepage' => [
            'attributes' => [
                'body' => 'richText'
            ]
        ],
        'privacyPolicy' => [
            'attributes' => [
                'body' => 'longRichText'
            ]
        ],
        'science' => [
            'attributes' => [
                'teaserBlocks' => 'teaserBlocks',
                'expandedBlocks' => 'expandedBlocks'
            ]
        ],
        'solutionsEntries' => [
            'attributes' => [
                'copyBlockWithImage' => 'copyBlockWithImage',
                'twoColumnBlockWithHeaders' => 'twoColumnBlockWithHeaders',
                'copyBlockWithIcon' => 'copyBlockWithIcon'
            ]
        ],
        'solutionsLandingPage' => [
            'attributes' => [
                'body' => 'richText',
                'entryBlockWithImage' => 'entryBlockWithImage'
            ]
        ],
        'termsAndConditions' => [
            'attributes' => [
                'body' => 'longRichText'
            ]
        ],
        'demo' => [
            'attributes' => [
                'heroVideoHeading' => 'heroVideoHeading',
                'copyBlockWithImage' => 'copyBlockWithImage',
                'socialBlockSectionHeading' => 'socialBlockSectionHeading',
                'teaserBlock' => 'teaserBlock'
            ]
        ],
        'keiserRepresentative' => [
            'attributes' => [
                'email' => '',
                'contactBlock' => 'unfilteredRichText'
            ]
        ],
        'options' => [
            'attributes' => [
                'body' => 'richText'
            ]
        ],
        'testimonials' => [
            'attributes' => [
                'quote' => 'richText',
                'citation' => ''
            ]
        ],
        'keiserInteractiveCycling' => [
            'attributes' => [
                'appLandingHeaderWithScreenshot' => 'appLandingHeaderWithScreenshot',
                'appLandingAppLinkBlock' => 'appLandingAppLinkBlock',
                'appLandingIconReel' => 'appLandingIconReel'
            ],
        ],
        'appLandingPages' => [
            'attributes' => [
                'appLandingHeader' => 'appLandingHeader',
                'appLandingDescription' => 'richText',
                'appLandingScreenshots' => 'appLandingScreenshots'
            ]
        ],
        'mSeriesGroupAppLandingPage' => [
            'attributes' => [
                'appLandingHeaderWithImage' => 'appLandingHeaderWithImage',
                'appLandingTwoColumnBlockWithImage' => 'appLandingTwoColumnBlockWithImage',
                'appLandingTwoColumnBlockWithInfographic' => 'appLandingTwoColumnBlockWithInfographic',
                'appLandingDescription' => 'richText',
                'appLandingScreenshots' => 'appLandingScreenshots',
                'appLandingRichText' => 'richText',
                'appLandingInstructions' => 'appLandingInstructions',
                'appLandingInstructionStep' => 'appLandingInstructionStep',
                'appLandingRichText' => 'richText',
                'appLandingBanner' => 'appLandingBanner',
                'appLandingIconReel' => 'appLandingIconReel',
                'appLandingTwoColumnBlockWithImageAlt' => 'appLandingTwoColumnBlockWithImageAlt'
            ]
        ],
        'mSeriesConverter' => [
            'attributes' => [
                'mSeriesConverterHero' => 'mSeriesConverterHero',
                'mSeriesConverterCompatibility' => 'mSeriesConverterCompatibility',
                'arbitraryContentBlocks' => 'arbitraryContentBlocks'
            ]
        ],
        'powerEdBlog' => [
            'attributes' => [
                'body' => 'richText'
            ],
            'searchableAttributes' => [
                'title',
                'body'
            ]
        ],
    ];

    public static $masterIndexSettings = [
        'searchableAttributes' => [
            'title',
            'partNumber',
            'features',
            'body',
            'richTextBody',
            'supportTags',
            'arbitraryContentBlocks',
            'announcementBody',
            'supportFAQAnswer',
            'twoColumnBlockWithHeaders',
            'industryPartners',
            'copyBlockWithImage',
            'trainer',
            'supportBlockCopy',
            'trainingListOneTitle',
            'trainingListOneItem',
            'trainingListTwoTitle',
            'trainingListTwoItem',
            'calendarHeader',
            'categoryBlockWithImage',
            'copyBlockWithLink',
            'supportLinkText',
            'teaserBlocks',
            'expandedBlocks',
            'copyBlockWithIcon',
            'entryBlockWithImage',
            'heroVideoHeading',
            'socialBlockSectionHeading',
            'blockWithLink',
            'teaserBlock',
            'supportPartsTags',
            'email',
            'contactBlock',
            'quote',
            'citation',
            'appLandingHeaderWithScreenshot',
            'appLandingAppLinkBlock',
            'appLandingIconReel',
            'appLandingHeader',
            'appLandingScreenshots',
            'appLandingHeaderWithImage',
            'appLandingTwoColumnBlockWithImage',
            'appLandingTwoColumnBlockWithInfographic',
            'appLandingDescription',
            'appLandingRichText',
            'appLandingInstructions',
            'appLandingInstructionStep',
            'appLandingRichText',
            'appLandingBanner',
            'appLandingTwoColumnBlockWithImageAlt',
            'mSeriesConverterHero',
            'mSeriesConverterCompatibility'
        ],
        'attributesForFaceting' => [
            'type',
            'section'
        ],
        'customRanking' => [
            'desc(orderDescending)'
        ]
    ];

    public static $entryTypeMapping = [
        'products' => 'products',
        'jobs' => 'jobs',
        'supportArticles' => 'support',
        'supportCategories' => 'support',
        'supportHomepage' => 'support',
        'supportAnnouncements' => 'support',
        'supportFAQ' => 'support',
        'supportLinks' => 'support',
        'supportCopyBlockWithLink' => 'support',
        'supportLinkBlockWithIcon' => 'support',
        'about' => 'general',
        'aboutTrainers' => 'general',
        'aboutEnvironment' => 'general',
        'aboutCareers' => 'general',
        'aboutEducation' => 'general',
        'aboutFinancing' => 'general',
        'fitnessEquipmentOverview' => 'general',
        'generalPages' => 'general',
        'homepage' => 'general',
        'privacyPolicy' => 'general',
        'science' => 'general',
        'solutionsEntries' => 'general',
        'solutionsLandingPage' => 'general',
        'termsAndConditions' => 'general',
        'demo' => 'general',
        'keiserRepresentative' => 'general',
        'options' => 'general',
        'testimonials' => 'general',
        'keiserInteractiveCycling' => 'general',
        'appLandingPages' => 'general',
        'mSeriesGroupAppLandingPage' => 'general',
        'mSeriesConverter' => 'general',
        'powerEdBlog' => 'blog',
    ];

    public static $urlMapping = [
        'supportAnnouncements' => 'announcementLink',
        'supportLinks' => 'linkURL',
        'supportCopyBlockWithLink' => 'linkURL',
        'supportLinkBlockWithIcon' => 'linkURL'
    ];

    public static $breadcrumbMapping = [
        'supportArticles' => 'SUPPORT / ARTICLES /',
        'supportCategories' => 'SUPPORT / CATEGORIES /',
        'supportHomepage' => 'SUPPORT / ',
        'supportAnnouncements' => 'SUPPORT / ANNOUNCEMENTS /',
        'supportFAQ' => 'SUPPORT / FAQ /',
        'supportLinks' => 'SUPPORT / DOWNLOADS AND LINKS /',
        'supportCopyBlockWithLink' => 'SUPPORT / DOWNLOADS AND LINKS /',
        'supportLinkBlockWithIcon' => 'SUPPORT / DOWNLOADS AND LINKS /',
    ];

    public function updateAlgoliaIndex($operation, Entry $entry, $settings = null){
        $algoliaIndicesList = array_keys(self::$entryTypeMapping);
        if(in_array($entry->getType()->handle, $algoliaIndicesList)){
            $indexHandle = $entry->getType()->handle;
            if(!$settings){
                $settings = \keiser\searchhelpers\Plugin::getInstance()->getSettings();
            }
            if($settings->algoliaApplicationId && $settings->algoliaAdminApiKey && $settings->algoliaIndexPrefix){
                $client = new \AlgoliaSearch\Client($settings->algoliaApplicationId, $settings->algoliaAdminApiKey);
                $masterIndex = $client->initIndex($settings->algoliaIndexPrefix . 'master');
                $masterIndex->setSettings(self::$masterIndexSettings);
                switch($operation){
                    case 'add':
                        $record = $this->prepareRecord($entry, $indexHandle);
                        $masterIndex->addObject($record);
                        break;
                    case 'remove':
                        $masterIndex->deleteObject($entry->getSourceId());
                        break;
                }
            }
        }
    }

    private function extractFieldValue($fieldType, $field){
        if(!$field){
            return '';
        }
        switch($fieldType){
            case 'richText':
                return strip_tags($field->getParsedContent());
                break;
            case 'unfilteredRichText':
                return $field->getParsedContent();
                break;
            case 'productFeature':
                $features = [];
                foreach($field as $feature){
                    $features[] = strip_tags($feature['nameOfFeature']);
                }
                return $features;
                break;
            case 'asset':
                return $field->one()->getUrl('xsmall');
                break;
            case 'categories':
                $categories = [];
                foreach($field as $category){
                    $categories[] = $category->title;
                }
                return $categories;
                break;
            case 'tags':
                $tags = [];
                foreach($field as $tag){
                    $tags[] = $tag->title;
                }
                return $tags;
                break;
            case 'arbitraryContentBlocks':
                $blocks = [];
                foreach($field as $contentBlock){
                    $blocks[] = strip_tags($contentBlock->copy->getParsedContent());
                }
                return $blocks;
                break;
            case 'twoColumnBlockWithHeaders':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->smallHeader . "\n";
                    $content .= $contentBlock->largeHeader . "\n";
                    $content .= strip_tags($contentBlock->copyBlockLeft->getParsedContent());
                    $content .= strip_tags($contentBlock->copyBlockRight->getParsedContent());
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'twoColumnBlock':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->copyBlockLeft->getParsedContent());
                    $content .= strip_tags($contentBlock->copyBlockRight->getParsedContent());
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'copyBlockWithImage':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->smallTitle) . "\n";
                    $content .= strip_tags($contentBlock->largeTitle) . "\n";
                    $content .= strip_tags($contentBlock->bodyCopy->getParsedContent());
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'trainer':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->trainerName . "\n";
                    $content .= $contentBlock->hometown . "\n";
                    $content .= $contentBlock->trainerTitle . "\n";
                    $content .= $contentBlock->trainerBio . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'industryPartners':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->partnerName . "\n";
                    $content .= $contentBlock->partnerCopy . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'trainingListOneItem':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->itemTitle . "\n";
                    $content .= $contentBlock->itemDescription . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'trainingListTwoItem':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->itemTitle . "\n";
                    $content .= $contentBlock->itemDescription . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'categoryBlockWithImage':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->blockTitle . "\n";
                    $content .= $contentBlock->blockDescription . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'copyBlockWithLink':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->blockTitle . "\n";
                    $content .= $contentBlock->blockDescription . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'teaserBlocks':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->smallHeader . "\n";
                    $content .= $contentBlock->largeHeader . "\n";
                    $content .= $contentBlock->copyBlock . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'expandedBlocks':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = '';
                    switch($contentBlock->type){
                        case 'mSeriesExpanded':
                            $content = $contentBlock->smallHeader . "\n";
                            $content .= $contentBlock->largeHeader . "\n";
                            $content .= strip_tags($contentBlock->copyBlock->getParsedContent()) . "\n";
                            $content .= $contentBlock->magnetSmallHeader . "\n";
                            $content .= $contentBlock->magnetLargeHeader . "\n";
                            $content .= strip_tags($contentBlock->magnetCopyBlock->getParsedContent()) . "\n";
                            break;
                        case 'pedalExpanded':
                        case 'pneumaticExpanded':
                            $content = $contentBlock->smallHeader . "\n";
                            $content .= $contentBlock->largeHeader . "\n";
                            $content .= strip_tags($contentBlock->copyBlock->getParsedContent()) . "\n";
                            break;
                        case 'pneumaticExpandedIconListItem':
                            $content = $contentBlock->iconListTitle . "\n";
                            $content .= strip_tags($contentBlock->iconListCopy->getParsedContent()) . "\n";
                            break;
                    }
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'copyBlockWithIcon':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->titleBlock . "\n";
                    $content .= $contentBlock->copyBlock . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'entryBlockWithImage':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->blockTitle . "\n";
                    $content .= $contentBlock->blockDescription . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'heroVideoHeading':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->largeHeader . "\n";
                    $content .= $contentBlock->buttonText . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'socialBlockSectionHeading':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->sectionTitle) . "\n";
                    $content .= strip_tags($contentBlock->sectionSubtitle) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'teaserBlock':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = '';
                    switch($contentBlock->type){
                        case 'teaser':
                            $content = strip_tags($contentBlock->largeHeader) . "\n";
                            $content .= strip_tags($contentBlock->copyBlock->getParsedContent()) . "\n";
                            break;
                        case 'teaserExpandedBlock':
                            $content = $contentBlock->smallHeader . "\n";
                            $content .= strip_tags($contentBlock->largeHeader) . "\n";
                            $content .= strip_tags($contentBlock->copyBlock->getParsedContent()) . "\n";
                            break;
                        case 'teaserExpandedIconListItem':
                            $content = $contentBlock->iconListTitle . "\n";
                            $content .= strip_tags($contentBlock->iconListCopy->getParsedContent()) . "\n";
                            break;
                    }
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'longRichText':
                $textChunks = wordwrap(strip_tags($field->getParsedContent()), 1000,  "<custombreak>");
                $textChunks = explode("<custombreak>", $textChunks);
                $textChunks = array_splice($textChunks, 0, 5);
                return $textChunks;
                break;
            case 'blockWithLink':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = '';
                    switch($contentBlock->type){
                        case 'linkBlockWithCopy':
                            $content = $contentBlock->blockTitle . "\n";
                            $content .= $contentBlock->linkText . "\n";
                            $content .= $contentBlock->blockBody . "\n";
                            $content .= $contentBlock->blockStrong . "\n";
                            break;
                        case 'linkBlock':
                            $content = $contentBlock->blockTitle . "\n";
                            $content .= $contentBlock->linkText . "\n";
                            break;
                    }
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'number':
                return (int) $field;
                break;
            case 'appLandingHeaderWithScreenshot':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->screenshotTitle->getParsedContent()) . "\n";
                    $content .= strip_tags($contentBlock->description->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingAppLinkBlock':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->blockContent->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingIconReel':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->blockContent->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingHeader':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->smallHeading . "\n";
                    $content .= $contentBlock->largeHeading . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingScreenshots':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->screenshotTitle . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingHeaderWithImage':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->smallHeading . "\n";
                    $content .= $contentBlock->largeHeading . "\n";
                    $content .= $contentBlock->description . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingTwoColumnBlockWithImage':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->largeHeading . "\n";
                    $content .= $contentBlock->smallHeading . "\n";
                    $content .= strip_tags($contentBlock->description->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingTwoColumnBlockWithInfographic':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->largeHeadingWhite . "\n";
                    $content .= $contentBlock->largeHeadingRed . "\n";
                    $content .= strip_tags($contentBlock->description->getParsedContent()) . "\n";
                    $content .= strip_tags($contentBlock->footnotes->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingInstructions':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->appLandingInstructionsTitle . "\n";
                    $content .= strip_tags($contentBlock->description->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingInstructionStep':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->description . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingBanner':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = $contentBlock->largeHeadingWhite . "\n";
                    $content .= $contentBlock->largeHeadingRed . "\n";
                    $content .= strip_tags($contentBlock->description->getParsedContent()) . "\n";
                    $content .= $contentBlock->smallHeadingRed . "\n";
                    $content .= $contentBlock->smallHeadingWhite . "\n";
                    $content .= strip_tags($contentBlock->smallDescription->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'appLandingTwoColumnBlockWithImageAlt':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->blockContent->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'mSeriesConverterHero':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->copy->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
            case 'mSeriesConverterCompatibility':
                $blocks = [];
                foreach($field as $contentBlock){
                    $content = strip_tags($contentBlock->copy->getParsedContent()) . "\n";
                    $blocks[] = $content;
                }
                return $blocks;
                break;
        }
    }

    private function getIndexSettings($indexHandle){
        $indexSettings = [];
        if(isset(self::$indexSettings[$indexHandle]['attributesForFaceting'])){
            $indexSettings ['searchableAttributes'] = self::$indexSettings[$indexHandle]['searchableAttributes'];
        }
        if(isset(self::$indexSettings[$indexHandle]['attributesForFaceting'])){
            $indexSettings['attributesForFaceting'] = self::$indexSettings[$indexHandle]['attributesForFaceting'];
        }
        if(isset(self::$indexSettings[$indexHandle]['ranking'])){
            $indexSettings['ranking'] = self::$indexSettings[$indexHandle]['ranking'];
        }
        return $indexSettings;
    }

    private function prepareRecord(Entry $entry, $indexHandle){
        $expiryDate = 2145876372; //31st December 2037
        if($entry->expiryDate){
            $expiryDate = $entry->expiryDate->getTimestamp();
        }
        $record = [
            'section' => $entry->section->handle,
            'type' => self::$entryTypeMapping[$indexHandle],
            'title' => $entry->title,
            'url' => (isset(self::$urlMapping[$indexHandle]) ? $entry->getFieldValue(self::$urlMapping[$indexHandle]) : $entry->url),
            'breadcrumb' => (isset(self::$breadcrumbMapping[$indexHandle]) ? self::$breadcrumbMapping[$indexHandle]: $entry->section->name . ' /'),
            'linkType' => 'link',
            'objectID' => $entry->getSourceId(),
            'postDate' => $entry->postDate->getTimestamp(),
            'expiryDate' => $expiryDate
        ];
        $attributes = self::$indexSettings[$indexHandle]['attributes'];
        foreach($attributes as $attribute => $fieldType){
            if($fieldType == ''){
                $record[$attribute] = $entry->getFieldValue($attribute);
            } else {
                $record[$attribute] = $this->extractFieldValue($fieldType, $entry->getFieldValue($attribute));
            }
        }
        return $record;
    }

    public function buildAlgoliaIndex($settings){
        if($settings->algoliaApplicationId && $settings->algoliaAdminApiKey && $settings->algoliaIndexPrefix){
            $entryTypeList = array_keys(self::$entryTypeMapping);
            $masterIndexName = $settings->algoliaIndexPrefix . 'master';
            $operations = [];
            foreach($entryTypeList as $entryType){
                $queryModel = \craft\elements\Entry::find();
                $queryModel->type = $entryType;
                $queryModel->enabledForSite = true;
                $entries = $queryModel->all();
                foreach($entries as $entry){
                    if(!$entry->getIsDraft() && !$entry->getIsUnpublishedDraft() && !$entry->getIsRevision()
                        && $entry->enabled && (isset($entry->metaNoIndex) ? !$entry->metaNoIndex : true)){
                        $operations[] = [
                            'action' => 'addObject',
                            'indexName' => $masterIndexName,
                            'body' => $this->prepareRecord($entry, $entryType)
                        ];
                    }
                }
            }
            $client = new \AlgoliaSearch\Client($settings->algoliaApplicationId, $settings->algoliaAdminApiKey);
            $masterIndex = $client->initIndex($settings->algoliaIndexPrefix . 'master');
            $masterIndex->setSettings(self::$masterIndexSettings);
            $client->batch($operations);
        }
    }

    public function getSettings(){
        return \keiser\searchhelpers\Plugin::getInstance()->getSettings();
    }

}
