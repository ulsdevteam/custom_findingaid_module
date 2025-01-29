<?php

namespace Drupal\uls_resource\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure resource migration settings
 */
class ulsResourceForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uls_resource_config';
  }

/**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['uls_resource.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = \Drupal::state();

    $form['connection'] = [
      '#type' => 'details',
      '#title' => t('ArchivesSpace API Connection'),
      '#open' => TRUE,
    ];

    $form['connection']['archivesspace_base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ArchivesSpace API Prefix'),
      '#default_value' => $state->get('archivesspace.base_uri'),
    ];

    $form['connection']['archivesspace_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ArchivesSpace Username'),
      '#default_value' => $state->get('archivesspace.username'),
    ];

    $form['connection']['archivesspace_password'] = [
      '#type' => 'password',
      '#title' => $this->t('ArchivesSpace Password'),
      '#default_value' => '',
      '#description'   => t('Leave blank to make no changes, use an invalid string to disable if need be.'),
    ];

    $form['resource_link_prefix'] = [
      '#type' => 'details',
      '#title' => t('Resource Link Prefixs'),
      '#open' => TRUE,
    ];

    $form['resource_link_prefix']['as_resources_viewonline_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource Viewonline Prefix'),
      '#default_value' => $state->get('archivesspace.viewonlineuri'),
    ];

    $form['resource_link_prefix']['as_resources_readingroom_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource Readingroom Prefix'),
      '#default_value' => $state->get('archivesspace.readingroomuri'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the provided values in Drupal state.
    $state = \Drupal::state();
    $state->set('archivesspace.base_uri', $form_state->getValue('archivesspace_base_uri'));
    $state->set('archivesspace.username', $form_state->getValue('archivesspace_username'));
    if (!empty($form_state->getValue('archivesspace_password'))) {
      $state->set('archivesspace.password', $form_state->getValue('archivesspace_password'));
    }
   $state->set('archivesspace.viewonlineuri', $form_state->getValue('as_resources_viewonline_uri'));
   $state->set('archivesspace.readingroomuri', $form_state->getValue('as_resources_readingroom_uri'));
  
    parent::submitForm($form, $form_state);
  }

}
