<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\rest\controllers\noAuth\user;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\rest\components\BaseController;
use humhub\modules\rest\components\NoAuthBaseController;
use humhub\modules\rest\controllers\user\GroupController;
use humhub\modules\rest\controllers\noAuth\user\UserDefinitions;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;


/**
 * Class AccountController
 */
class UserController extends NoAuthBaseController
{

    /**
     * @inheritdoc
     */
//    noAuth function getAccessRules()
//    {
//        return [
//            ['permissions' => [ManageUsers::class]],
//        ];
//    }


    /**
     * Get User by username
     * 
     * @param string $username the username searched
     * @return UserDefinitions
     * @throws HttpException
     */
    public function actionGetByUsername($username)
    {
        $user = User::findOne(['username' => $username]);

        if ($user === null) {
            return $this->returnError(404, 'User not found!');
        }
        
        return $this->actionView($user->id);
    }


    /**
     * Get User by email
     * 
     * @param string $email the email searched
     * @return UserDefinitions
     * @throws HttpException
     */

    public function actionView($id)
    {
        $user = User::findOne(['id' => $id]);
        if ($user === null) {
            return $this->returnError(404, 'User not found!');
        }

        return UserDefinitions::getUser($user);
    }

    /**
     *
     * @return array
     * @throws HttpException
     */
    public function actionCreate()
    {
        $user = new User();
        $user->scenario = 'editAdmin';
        $user->load(Yii::$app->request->getBodyParam("account", []), '');
        $user->validate();

        $profile = new Profile();
        $profile->scenario = 'editAdmin';
        $profile->load(Yii::$app->request->getBodyParam("profile", []), '');
        $profile->validate();

        $password = new Password();
        $password->scenario = 'registration';
        $password->load(Yii::$app->request->getBodyParam("password", []), '');
        $password->newPasswordConfirm = $password->newPassword;
        $password->validate();

        if ($user->hasErrors() || $password->hasErrors() || $profile->hasErrors()) {
            return $this->returnError(400, 'Validation failed', [
                'password' => $password->getErrors(),
                'profile' => $profile->getErrors(),
                'account' => $user->getErrors(),
            ]);
        }

        if ($user->save()) {
            $profile->user_id = $user->id;
            $password->user_id = $user->id;
            $password->setPassword($password->newPassword);
            if ($profile->save() && $password->save()) {
                if($password->mustChangePassword) {
                    $user->setMustChangePassword(true);

                }
                return $this->actionView($user->id);
            }
        }

        Yii::error('Could not create validated user.', 'api');
        return $this->returnError(500, 'Internal error while save user!');
    }


}