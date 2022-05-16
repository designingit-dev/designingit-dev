<?php

namespace keiser\commercehelpers\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\web\Controller;
use craft\web\View;

use yii\web\Response;

class OrderStatusController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['find'];

    // Public Methods
    // =========================================================================

    /**
     * Search for an order by shortnumber and email.
     *
     * @return Response
     */
    public function actionFind(): Response
    {
        $this->requirePostRequest();

        $orderNumber = Craft::$app->request->getRequiredParam('orderNumber');
        $email = Craft::$app->request->getRequiredParam('email');

        if (
            !Order::find()
                ->shortNumber($orderNumber)
                ->email($email)
                ->exists()
        ) {
            Craft::$app->getSession()->setFlash('orderNotFound', $orderNumber);
            Craft::$app->getSession()->setFlash('orderEmail', $email);
            return $this->redirect('order-status');
        }

        return $this->redirect('order-status/' . $orderNumber);
    }
}
