<?php

namespace Drupal\alert_news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alert_news\Database\IDatabaseCommunicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AlertNewsForm extends FormBase
{
    private $db;

    function __construct(IDatabaseCommunicator $dbc) {
        $this->db = $dbc;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('service_alert_news_db_communicator')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'alert_news_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $newsTypes = $this->db->getNewsTypesFromDatabase();
        $userId = $this->db->getCurrentUserId();
        $subscribedNews = $this->db->getSubscribedNewsTypeListForUser($userId, $newsTypes);

        foreach ($newsTypes as $row) {
            $isSubscribed = $this->db->checkIfUserIsSubscribedForNewsType($row->tid, $subscribedNews);

            $form['alert_news_' . $row->tid] = array(
                '#type' => 'checkbox',
                '#title' => $this->t($row->name),
                '#default_value' => $isSubscribed,
            );
        }

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

    /**
     * @param $uid
     * @param $tid
     * @param $value
     * @throws \Exception
     */
    private function setValueInDatabase($uid, $tid, $value) {
        if ($value == "1") {
            // check if record exists about that
            if (empty($this->db->getSelectedUserAndNewsCategoryFromDb($uid, $tid))) {
                // if there's no record about that subscription, add one to database
                $this->db->createNewSubscription($uid, $tid);
            }
            // otherwise we don't need to store anything new in db

        } else {
            // remove the record from database
            $this->db->removeSubscriptionFromUser($uid, $tid);

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
        //$this->displayReturnedForm($form_state);

        // get userId
        $userId = $this->db->getCurrentUserId();

        foreach ($form_state->getValues() as $key => $value) {
            // get tid
            if (strpos($key, 'alert_news') !== false) {
                $tid = substr($key, 11);
                // save in db if necessary
                $this->setValueInDatabase($userId, $tid, $value);
            }
        }
    }
}