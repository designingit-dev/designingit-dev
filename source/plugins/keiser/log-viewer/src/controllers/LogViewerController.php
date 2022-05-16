<?php

namespace keiser\logviewer\controllers;

use craft\web\Controller;

class LogViewerController extends Controller
{

    public function actionGetLog()
    {
        $this->requireLogin();
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $post = craft()->request->getPost();

        $log = \keiser\logviewer\Plugin::getInstance()->logViewer->readLogFile($post);

        return $this->asJson($log);
    }
}