<?php

namespace keiser\contacthelpers\services;

use Craft;
use keiser\contacthelpers\fields\CampaignTrackingField;
use yii\base\Component;
use yii\web\Cookie;
use craft\elements\Entry;

use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Database\Reader;
use \MaxMind\Db\Reader\InvalidDatabaseException;

class Service extends Component
{
    public function getCountriesList(){
        $fileName = __DIR__ . '/../resources/all_countries.json';
        $countryList = json_decode(file_get_contents($fileName), true);
        if(!empty($countryList)){
            return $countryList;
        }
        return false;
    }

    private function mapRegion($country){
        switch($country['region']){
            case 'Polar':
                return 'Antarctica';
                break;
            case 'Americas':
                switch($country['subregion']){
                    case 'Northern America':
                        return 'North America';
                        break;
                    case 'Central America':
                        return 'South America';
                        break;
                    case 'Caribbean':
                        return 'North America';
                        break;
                    default:
                        return $country['subregion'];
                        break;
                }
                break;
            default:
                return $country['region'];
                break;
        }
    }

    public function findKeiserRep($countryISO, $zip = null, $institutionType = null, $interestedProducts = null){
        if(in_array($countryISO, ['US', 'CA']) &&
            $institutionType &&
            $interestedProducts &&
            $institutionType === 'government' &&
            in_array('strengthTrainingForceMachines', $interestedProducts) &&
            count($interestedProducts) === 1
        ){
            $representative = Craft::$app->globals->getSetByHandle('globalSiteConfig')->governmentCommercialSalesRep;
            if(isset($representative[0])){
                return $representative[0];
            }
        } else {
            $criteria = Entry::find();
            $criteria->section = 'territories';
            $criteria->countryISO = $countryISO;
            if($countryISO === 'US' && $zip){
                $zip = (int) $zip;
                $criteria->startZip = "<= {$zip}";
                $criteria->endZip = ">= {$zip}";
            }
            $territory = $criteria->one();
            if($territory && isset($territory->belongsToRep[0])){
                return $territory->belongsToRep[0];
            } else {
                $countryList = $this->getCountriesList();
                if($countryList){
                    $arrKey = null;
                    foreach($countryList as $key => $country){
                        if($country['alpha2Code'] === $countryISO){
                            $arrKey = $key;
                            break;
                        }
                    }
                    if($arrKey){
                        $criteria = Entry::find();
                        $criteria->section = 'regions';
                        $criteria->title = $countryList[$arrKey]['region'];
                        $region = $criteria->one();
                        if($region && isset($region->belongsToVP[0])){
                            return $region->belongsToVP[0];
                        }
                    }
                }
            }
        }
        return false;
    }

    public function getVisitorGeolocation(){
        $cookieDuration = 60 * 60 * 24; //1 Day
        try {
            $cookieCollection = Craft::$app->getRequest()->getCookies();
            $geolocationString = $this->getGeolocationFromCookie($cookieCollection, $cookieDuration);
            if ($geolocationString) {
                return $this->parseGeolocationString($geolocationString);
            } else {
                $reader = new Reader(getenv('MAXMIND_DB_FILE'));
                $record = $reader->city(Craft::$app->getRequest()->getUserIP());
                $countryISO = isset($record->country->isoCode) ? $record->country->isoCode : 'US';
                $countryName = isset($record->country->names['en']) ? $record->country->names['en'] : 'United States of America';
                $cityName = isset($record->city->names['en']) ? $record->city->names['en'] : '';
                $subdivisionName = isset($record->subdivisions[0]->names['en']) ? $record->subdivisions[0]->names['en'] : '';
                $subdivisionISO = isset($record->subdivisions[0]->isoCode) ? $record->subdivisions[0]->isoCode : 'ZZZ'; //ISO-3166 allowed reserved name for non existent codes
                $postalCode = isset($record->postal->code) ? $record->postal->code : '00000';
                if ($countryISO === 'US') {
                    $geolocationLabel = "{$cityName};{$subdivisionName};{$countryName};{$postalCode}";
                } else {
                    $geolocationLabel = "{$cityName};{$subdivisionName};{$countryName}";
                }
                $geolocationString = "{$countryISO}#{$postalCode}#{$geolocationLabel}#{$subdivisionISO}";
                $cookie = new Cookie([
                    'name' => 'visitorGeolocation',
                    'value' => $geolocationString . '#' . time() . $cookieDuration,
                    'expire' => time() + $cookieDuration,
                    'secure' => true,
                    'httpOnly' => true,
                ]);
                Craft::$app->response->getCookies()->add($cookie);
                return $this->parseGeolocationString($geolocationString);
            }
        } catch(InvalidDatabaseException $e){
            \keiser\contacthelpers\Plugin::log("MaxMind Exception: {$e->getMessage()}");
        } catch(AddressNotFoundException $e){
            \keiser\contacthelpers\Plugin::log("MaxMind Exception: {$e->getMessage()}");
        } catch(\Exception $e){
            \keiser\contacthelpers\Plugin::log("MaxMind Exception: {$e->getMessage()}");
        }
        //default location if all lookup fails. also useful when running on local as maxmind cannot resolve 127.0.0.1
        return $this->parseGeolocationString('US#93706#Fresno;California;United States of America;93706#CA');
    }

    private function getGeolocationFromCookie(\yii\web\CookieCollection $cookieCollection, $cookieDuration){
        if($cookieCollection->has('visitorGeolocation')){
            $cookie = $cookieCollection['visitorGeolocation']->value;
            $cookieComponents = explode('#', $cookie);
            if(isset($cookieComponents[4])){
                if((time() - $cookieComponents[4]) > $cookieDuration){
                    Craft::$app->response->getCookies()->remove('visitorGeolocation');
                    return false;
                }
                unset($cookieComponents[4]);
                return implode('#', $cookieComponents);
            }
        }
        return false;
    }

    private function parseGeolocationString($geolocationString){
        $components = explode('#', $geolocationString);
        if(!isset($components[0]) || !$components[0]){
            $countryISO = 'US';
        } else {
            $countryISO = $components[0];
        }
        if(!isset($components[1]) || !$components[1]){
            $zip = '00000';
        } else {
            $zip = $components[1];
        }
        if(!isset($components[2]) || !$components[2]){
            $label = 'Fresno;California;United States of America;93706';
        } else {
            $label = $components[2];
        }
        if(!isset($components[3]) || !$components[3]){
            $subdivision = 'ZZZ';
        } else {
            $subdivision = $components[3];
        }
        return [
            'country' => $countryISO,
            'zip' => $zip,
            'label' => $label,
            'subdivision' => $subdivision
        ];
    }

    public function isForeignVisitor(){
        $geolocation = $this->getVisitorGeolocation();
        $config = Craft::$app->globals->getSetByHandle('globalSiteConfig');
        if(isset($config->homeCountryISOList) && $config->homeCountryISOList){
            $countryList = explode(',', $config->homeCountryISOList);
        } else {
            $countryList = ['US', 'CA'];
        }
        if($geolocation && in_array($geolocation['country'], $countryList)){
            return false;
        }
        return true;
    }

    public function getKeiserRep($repHandle){
        $criteria = Entry::find();
        $criteria->section = "keiserRepresentatives";
        $criteria->slug = $repHandle;
        $rep = $criteria->one();
        if($rep){
            return $rep;
        }
        return false;
    }

    public function getEnvironmentVariable($handle){
        return getenv($handle);
    }

    public function getCampaignParameters($url){
        $requestParams = parse_url($url, PHP_URL_QUERY);
        parse_str($requestParams, $requestParams);
        $campaignParameters = [
            'campaign',
            'content',
            'keyword',
            'lsrc',
            'placement',
            'site',
            'offer'
        ];
        $value = '';
        $newCampaign = false;
        foreach($campaignParameters as $parameter){
            if(isset($requestParams[$parameter])){
                $value .= $requestParams[$parameter];
                $newCampaign = true;
            }
            $value .= ';';
        }
        if($newCampaign){
            $value .= Craft::$app->getRequest()->getAbsoluteUrl();
            $cookieDuration = 60 * 60 * 24 * 365;
            $cookie = new Cookie([
                'name' => CampaignTrackingField::$cookieName,
                'value' =>  $value,
                'expire' => time() + $cookieDuration,
                'secure' => true,
                'httpOnly' => true
            ]);
            Craft::$app->response->getCookies()->add($cookie);
        } else {
            $cookieCollection = Craft::$app->getRequest()->getCookies();
            if($cookieCollection->has(CampaignTrackingField::$cookieName)){
                $value = $cookieCollection[CampaignTrackingField::$cookieName]->value;
            }
        }
        return $value;
    }

    public function whiteGloveDeliveryAvailableForRegion(){
        $geolocation = $this->getVisitorGeolocation();
        if(
            $geolocation && $geolocation['country'] == 'US' &&
            !in_array($geolocation['subdivision'], ['AK', 'HI'])){
            return true;
        }
        return false;
    }

    public function getBanners($path){
        $bannerGroupQuery = Entry::find();
        $bannerGroupQuery->section = 'bannerGroups';
        $bannerGroupQuery->orderBy('bannerGroupsPriority');
        $bannerGroups = $bannerGroupQuery->all();
        $banners = [
            'absoluteTopBanners' => [],
            'belowTopNavigationBanners' => []
        ];
        foreach($bannerGroups as $bannerGroup){
            $bannerGroupEnabled = false;
            foreach($bannerGroup->bannerGroupMatchURIPattern as $patternBlock){
                $pattern = $patternBlock->pattern;
                $pattern = '/' . $pattern . '/';
                if(preg_match($pattern,$path) === 1){
                    $bannerGroupEnabled = true;
                }
            }
            foreach($bannerGroup->bannerGroupBlockURIPattern as $patternBlock){
                $pattern = $patternBlock->pattern;
                $pattern = '/' . $pattern . '/';
                if(preg_match($pattern,$path) === 1){
                    $bannerGroupEnabled = false;
                }
            }
            if($bannerGroupEnabled){
                $bannersInGroup = $bannerGroup->bannerGroupBanners->all();
                if($closedBanners = $this->getClosedBanners()){
                    foreach($bannersInGroup as $key => $banner){
                        if(in_array($banner->id, $closedBanners)){
                            unset($bannersInGroup[$key]);
                        }
                    }
                    $bannersInGroup = array_merge([],$bannersInGroup); //Rekeys the array, starting from 0, in case some values were unset
                }
                if(!empty($bannersInGroup)){
                    $banner = $bannersInGroup[rand(0, (count($bannersInGroup) - 1))];
                    switch ($bannerGroup->bannerGroupBannerPosition){
                        case 'absoluteTop':
                            $banners['absoluteTopBanners'][] = $banner;
                            break;
                        case 'belowTopNavigation':
                            $banners['belowTopNavigationBanners'][] = $banner;
                            break;
                    }
                }
            }
        }
        return [
            'absoluteTopBanners' => Craft::$app->view->renderTemplate('banners/_displayBanners', [
                'banners' => $banners['absoluteTopBanners'],
                'isAbsoluteTopBanner' => true
            ]),
            'belowTopNavigationBanners' => Craft::$app->view->renderTemplate('banners/_displayBanners', [
                'banners' => $banners['belowTopNavigationBanners'],
                'isAbsoluteTopBanner' => false
            ]),
        ];
    }

    private function getClosedBanners(){
        $cookieCollection = Craft::$app->getRequest()->getCookies();
        $cookieName = 'closedBanners';
        if($cookieCollection->has($cookieName)){
            return $cookieCollection[$cookieName]->value;
        }
        return false;
    }

    public function stripEnclosingParagraphTags($html){
        return preg_replace(['/^<p>/','/<\/p>$/'],'',trim($html),1);
    }

    public function shortenFullName($fullName){
        $matches = [];
        preg_match('/(\\w+\s+\w{1})/', $fullName, $matches);
        if(!empty($matches)){
            return $matches[0];
        }
        return $fullName;
    }

    public function getCountryISOFromCountryName($countryName){
        $countryList = $this->getCountriesList();
        if($countryList) {
            $arrKey = null;
            foreach ($countryList as $key => $country) {
                if ($country['name'] === $countryName) {
                    return $country['alpha2Code'];
                }
            }
        }
        return null;
    }

}
