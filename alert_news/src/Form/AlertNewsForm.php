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
        $result = \Drupal::database()->select('taxonomy_term_field_data', 't')
            ->fields('t', ['tid'])
            ->fields('t', ['name'])
            ->condition('vid', 'hirtipusok', '=')
            ->execute()
            ->fetchAll();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $hirtipusok = $this->getHirTipusokFromDatabase();

        $rows = array();
        foreach ($hirtipusok as $row) {
            $form['alert_news_' . $row->tid] = array(
                '#type' => 'checkbox',
                '#title' => $this->t($row->name),
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