<?php

namespace keiser\redirectmanager;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use craft\web\ErrorHandler;
use craft\events\ExceptionEvent;
use yii\base\ExitException;
use yii\web\HttpException;

class Plugin extends \craft\base\Plugin
{
    public $hasCpSection = true;

    public function init()
    {
        parent::init();

        $this->setComponents([
            'redirectManager' => \keiser\redirectmanager\services\RedirectManagerService::class
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event){
            $variable = $event->sender;
            $variable->set('redirectManager', \keiser\redirectmanager\services\RedirectManagerService::class);
        });

        //redirect only 404
        Event::on(
            ErrorHandler::class,
            ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
            function (ExceptionEvent $event) {
                Craft::debug(
                    'ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION',
                    __METHOD__
                );
                $exception = $event->exception;
                // If this is a Twig Runtime exception, use the previous one instead
                if ($exception instanceof \Twig_Error_Runtime &&
                    ($previousException = $exception->getPrevious()) !== null) {
                    $exception = $previousException;
                }
                // If this is a 404 error, see if we can handle it
                if ($exception instanceof HttpException && $exception->statusCode === 404) {
                    $path = Craft::$app->getRequest()->getPathInfo();
                    if($path){
                        if( $location = $this->redirectManager->processRedirect($path) )
                        {
                            $event->handled = true;
                            Craft::$app->getResponse()->redirect($location['url'], $location['type'])->send();
                            try {
                                Craft::$app->end();
                            } catch (ExitException $e) {
                                Craft::error($e->getMessage(), __METHOD__);
                            }
                        }
                    }
                }
            }
        );

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['keiser-redirect-manager'] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates';
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $e) {
            $e->rules['keiser-redirect-manager\/new'] = ['template' => 'keiser-redirect-manager/_edit'];
            $e->rules['keiser-redirect-manager\/<redirectId:\d+>'] = ['template' => 'keiser-redirect-manager/_edit'];
        });
    }
}
