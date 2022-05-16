<?php

namespace keiser\ajaxlogging\controllers;

use craft\web\Controller;
use keiser\ajaxlogging\Plugin;
use Craft;

class KeiserAjaxLoggingController extends Controller
{

  protected $allowAnonymous = [
      'actionAjaxCraftLogging'
  ];

  public function actionAjaxCraftLogging()
  {
    $this->requireAcceptsJson();
    $this->requirePostRequest();

    $attrs = Craft::$app->getRequest()->getBodyParams();

    if (isset($attrs['log'])) {
      foreach ($attrs['log'] as $log) {

        switch($log['type']) {
          case 'error':
            $level = \yii\log\Logger::LEVEL_ERROR;
            break;
          case 'warning':
          default:
            $level = \yii\log\Logger::LEVEL_INFO;
            break;
        }
        Plugin::log($log['message'], $level, $log['plugin']);
      }
    }

    return $this->asJson([]);
  }
}
