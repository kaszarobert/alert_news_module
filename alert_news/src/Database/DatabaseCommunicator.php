<?php

namespace Drupal\alert_news\Database;

use Drupal\Core\Database\Query\Condition;

class DatabaseCommunicator implements IDatabaseCommunicator
{
    public function getNewsTypesFromDatabase() {
        //SELECT name FROM `taxonomy_term_field_data` WHERE vid = 'hirtipusok'
        $result = \Drupal::database()
            ->select('taxonomy_term_field_data', 't')
            ->fields('t', ['tid'])
            ->fields('t', ['name'])
            ->condition('vid', 'hirtipusok', '=')
            ->execute()
            ->fetchAll();

        return $result;
    }

    public function getSubscribedNewsTypeListForUser($id, array &$newsTypes) {
        // filter for existing news types from db
        $db_or = new Condition("OR");

        foreach($newsTypes as $item){
            $db_or->condition('tid', $item->tid,'=');
        }

        //SELECT * FROM `module_alert_news` WHERE uid = 1
        $result = \Drupal::database()
            ->select('module_alert_news', 'an')
            ->fields('an', ['uid'])
            ->fields('an', ['tid'])
            ->condition('uid', $id, '=')
            ->condition($db_or)
            ->execute()
            ->fetchAll();

        return $result;
    }

    public function getSelectedUserAndNewsCategoryFromDb($uid, $tid) {
        $result = \Drupal::database()
            ->select('module_alert_news', 'an')
            ->fields('an', ['uid'])
            ->fields('an', ['tid'])
            ->condition('uid', $uid, '=')
            ->condition('tid', $tid, '=')
            ->execute()
            ->fetchAll();

        return $result;
    }

    public function checkIfUserIsSubscribedForNewsType($key, $collection) {
        //return array_key_exists($key, $collection);
        foreach ($collection as $item) {
            if ($item->tid == $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $uid
     * @param $tid
     * @return \Drupal\Core\Database\StatementInterface|int|null
     * @throws \Exception
     */
    public function createNewSubscription($uid, $tid) {
        // INSERT INTO `module_alert_news` (uid, tid) VALUES (1, 4)
        $result = \Drupal::database()
            ->insert('module_alert_news')
            ->fields(
                array(
                    'uid' => $uid,
                    'tid' => $tid,
                )
            )
            ->execute();

        return $result;
    }

    public function removeSubscriptionFromUser($uid, $tid) {
        // DELETE FROM `module_alert_news` WHERE uid = 1 AND tid = 4
        $result = \Drupal::database()
            ->delete('module_alert_news')
            ->condition('uid', $uid, "=")
            ->condition('tid', $tid, "=")
            ->execute();

        return $result;
    }

    public function getCurrentUserId() {
        return \Drupal::currentUser()->id();
    }

    public function getUserDataWithEmail(){
        // SELECT mail FROM users_field_data WHERE mail IS NOT NULL
        return \Drupal::database()
            ->select('users_field_data', 'u')
            ->fields('u', ['uid'])
            ->fields('u', ['mail'])
            ->fields('u', ['langcode'])
            ->isNotNull('u.mail')
            ->execute()
            ->fetchAll();
    }
}