<?php

namespace Drupal\alert_news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

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
        $result = \Drupal::database()->select('taxonomy_term_field_data', 't')
            ->fields('t', ['tid'])
            ->fields('t', ['name'])
            ->condition('vid', 'hirtipusok', '=')
            ->execute()
            ->fetchAll();

        return $result;
    }

    private function getSubscribedNewsTypeListForUser($id, array &$newsTypes) {
        //SELECT * FROM `module_alert_news` WHERE uid = 1
        $result = \Drupal::database()->select('module_alert_news', 'an')
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


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $hirtipusok = $this->getHirTipusokFromDatabase();

        $userid = \Drupal::currentUser()->id();

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
        $this->displayReturnedForm($form_state);
    }
}