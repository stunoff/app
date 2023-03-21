<?php

namespace App\Helper;

class UserPermissions
{
    /**
     * @param array $userRights
     * @param array $groupRights
     *
     * @return bool
     */
    public static function isCurrentUserHaveRights($userRights = array(), $groupRights = array())
    {
        $userId      = (int)$_SESSION['user'];
        $userGroupId = (int)$_SESSION['tip'];
        
        if (is_array($userRights) && in_array($userId, $userRights)) {
            return true;
        }
        
        if (empty($groupRights) && empty($userRights)) {
            return true;
        }
        
        if (is_array($groupRights) && in_array($userGroupId, $groupRights)) {
            return true;
        }
        
        return false;
    }
}
