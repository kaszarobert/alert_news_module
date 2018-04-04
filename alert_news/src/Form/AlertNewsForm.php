<?php

namespace Drupal\alert_news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AlertNewsForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'alert_news_form';
    }

    /**
     * @return mixed
     */
    private function getHirTipusokFromDatabase() {
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

    private function getSubscribedNewsTypeListForUser($id, array &$newsTypes) {
        //SELECT * FROM `module_alert_news` WHERE uid = 1
        $result = \Drupal::database()
            ->select('module_alert_news', 'an')
            ->fields('an', ['uid'])
            ->fields('an', ['tid'])
            ->condition('uid', $id, '=')
            ->execute()
            ->fetchAll();

        return $result;
    }

    private function checkIfUserIsSubscribedForNewsType($key, $collection) {
        //return array_key_exists($key, $collection);
        foreach ($collection as $item) {
            if ($item->tid == $key) {
                return true;
            }
        }

        return false;
    }

    private function getCurrentUserId() {
        return \Drupal::currentUser()->id();
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $hirtipusok = $this->getHirTipusokFromDatabase();

        $userid = $this->getCurrentUserId();

        $subscribednews = $this->getSubscribedNewsTypeListForUser($userid, $hirtipusok);

        $rows = array();
        foreach ($hirtipusok as $row) {

            $isSubscribed = $this->checkIfUserIsSubscribedForNewsType($row->tid, $subscribednews);

            $form['alert_news_' . $row->tid] = array(
                '#type' => 'checkbox',
                '#title' => $this->t($row->name),
                '#default_value' => $isSubscribed,
            );
        }

        //$form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    private function getSelectedUserAndNewsCategoryFromDb($uid, $tid) {
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

    /**
     * @param $uid
     * @param $tid
     * @return \Drupal\Core\Database\StatementInterface|int|null
     * @throws \Exception
     */
    private function createNewSubscription($uid, $tid) {
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

    private function removeSubscriptionFromUser($uid, $tid) {
        // DELETE FROM `module_alert_news` WHERE uid = 1 AND tid = 4
        $result = \Drupal::database()
            ->delete('module_alert_news')
            ->condition('uid', $uid, "=")
            ->condition('tid', $tid, "=")
            ->execute();

        return $result;
    }

    /**
     * @param $uid
     * @param $tid
     * @param $value
     * @throws \Exception
     */
    private function setValueInDatabase($uid, $tid, $value) {
        if ($value == "1") {
            // check if record exists about that
            if (empty($this->getSelectedUserAndNewsCategoryFromDb($uid, $tid))) {
                // if there's no record about that subscription, add one to database
                $result = $this->createNewSubscription($uid, $tid);
            }
            // otherwise we don't need to store anything new in db

        } else {
            // remove the record from database
            $result = $this->removeSubscriptionFromUser($uid, $tid);

        }
    }

    /**
     * For debugging purposes
     * @param FormStateInterface $form_state
     */
    private function displayReturnedForm(FormStateInterface $form_state) {
        // drupal_set_message($this->t('@can_name ,Your application is being submitted!',
        //  array('@can_name' => $form_state->getValue('candidate_name'))));
        foreach ($form_state->getValues() as $key => $value) {
            drupal_set_message($key . ': ' . $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // display form data for debugging
        $this->displayReturnedForm($form_state);

        // get userid
        $userid = $this->getCurrentUserId();

        foreach ($form_state->getValues() as $key => $value) {
            // get tid
            if (strpos($key, 'alert_news') !== false) {
                $tid = substr($key, 11);
                // save in db if necessary
                $this->setValueInDatabase($userid, $tid, $value);
            }
        }
    }
}