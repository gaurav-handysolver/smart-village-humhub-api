<?php
namespace humhub\modules\rest\models\noAuthModels;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\user\components\User;
use humhub\modules\user\models\GroupUser;
use Yii;

/**
 * Created by PhpStorm.
 * User: rahulsinghmatharu
 * Date: 20/04/22
 * Time: 4:10 PM
 */
class NoAuthGroup extends \humhub\modules\user\models\Group
{

    /**
     * Adds a user to the group. This function will skip if the user is already a member of the group.
     *
     * @param User $user user id or user model
     * @param bool $isManager mark as group manager
     * @return bool true - on success adding user, false - if already member or cannot be added by some reason
     * @throws \yii\base\InvalidConfigException
     */
    public function addUser($user, $isManager = false)
    {
        if ($this->isMember($user)) {
            return false;
        }

        $userId = ($user instanceof User) ? $user->id : $user;

        $newGroupUser = new GroupUser();
        $newGroupUser->user_id = $userId;
        $newGroupUser->group_id = $this->id;
        $newGroupUser->created_at = date('Y-m-d G:i:s');
        $newGroupUser->created_by = Yii::$app->user->id;
        $newGroupUser->is_group_manager = $isManager;
        if ($newGroupUser->save()) {
            if ($this->notify_users) {
                if (!($user instanceof User)) {
                    $user = User::findOne(['id' => $user]);
                }
                IncludeGroupNotification::instance()
                    ->about($this)
                    ->from(Yii::$app->user->identity)
                    ->send($user);
            }
            return true;
        }

        return false;
    }

}