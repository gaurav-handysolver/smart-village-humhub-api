<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\rest\controllers\noAuth\user;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\rest\components\NoAuthBaseController;
use humhub\modules\rest\definitions\UserDefinitions;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use Yii;


/**
 * Class GroupController
 */
class GroupController extends NoAuthBaseController
{

    /**
     * @inheritdoc
     */
//    noAuth function getAccessRules()
//    {
//        return [
//            ['permissions' => [ManageGroups::class]],
//        ];
//    }

    public function actionMemberAddWithoutAuth($id,$userId)
    {
        $group = Group::findOne(['id' => $id]);
        if ($group === null) {
            return $this->returnError(404, 'Group not found!');
        }

        $user = User::findOne(['id' => $userId]);
        if ($user === null) {
            return $this->returnError(404, 'User not found!');
        }

        if ($group->isMember($userId)) {
            return $this->returnError(400, 'User is already a member of the group!');
        }

        if ($group->addUser($userId, !(empty(Yii::$app->request->get('isManager'))))) {
            return $this->returnSuccess('Member added!');
        }

        return $this->returnError(400, 'Could not add member!');
    }

}
