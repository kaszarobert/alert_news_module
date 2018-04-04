<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2018.04.04.
 * Time: 21:36
 */

namespace Drupal\alert_news\Database;


interface IDatabaseCommunicator
{
    function getNewsTypesFromDatabase();

    function getSubscribedNewsTypeListForUser($id, array &$newsTypes);

    function getSelectedUserAndNewsCategoryFromDb($uid, $tid);

    function checkIfUserIsSubscribedForNewsType($key, $collection);

    function createNewSubscription($uid, $tid);

    function removeSubscriptionFromUser($uid, $tid);

    function getCurrentUserId();

    function getUserDataWithEmail();

}