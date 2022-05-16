<?php

namespace keiser\redirectmanager\controllers;

use Craft;
use craft\web\Controller;
use keiser\redirectmanager\models\RedirectManagerModel;

class RedirectManagerController extends Controller
{
    public function actionSaveRedirect()
    {
        $this->requirePostRequest();
        $redirectManager = \keiser\redirectmanager\Plugin::getInstance()->redirectManager;
        if ($id = Craft::$app->getRequest()->getParam('redirectId')) {
            $model = $redirectManager->getRedirectById($id);
        } else {
            $model = new RedirectManagerModel();
        }

        $attributes = Craft::$app->getRequest()->getParam('redirectRecord');
        $model->uri = $attributes['uri'];
        $model->location = $attributes['location'];
        $model->type = $attributes['type'];

        if ($redirectManager->saveRedirect($model)) {
            Craft::$app->getSession()->setNotice('Redirect saved.');
            return $this->redirectToPostedUrl([
                'redirectId' => $model->id
            ]);
        } else {
            Craft::$app->getSession()->setError("Couldn't save redirect.");
            Craft::$app->getUrlManager()->setRouteParams([
                'redirectId' => $model->id
            ]);
        }
    }

    public function actionDeleteRedirect()
    {

        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredParam('id');
        \keiser\redirectmanager\Plugin::getInstance()->redirectManager->deleteRedirectById($id);

        return $this->asJson([
            'success' => true
        ]);
    }
}
