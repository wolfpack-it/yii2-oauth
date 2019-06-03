<?php

namespace oauth\controllers;

use common\controllers\SessionController as CommonSessionController;
use common\components\User as UserComponent;
use yii\web\Request;

/**
 * Class SessionController
 * @package oauth\controllers
 */
class SessionController extends CommonSessionController
{
    public function actionDelete(
        UserComponent $user,
        Request $request
    ) {
        $returnUrl = $user->getReturnUrl();
        if ($request->isDelete) {
            //In this case we do not want to destroy the session so a user can logout during the authorization and continue with another user
            $user->logout(false);
        }
        return $returnUrl ? $this->redirect($returnUrl) : $this->goHome();
    }
}