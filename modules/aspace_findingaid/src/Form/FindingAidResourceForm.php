<?php

namespace Drupal\aspace_findingaid\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Archivesspace finding aid migration settings
 */
class FindingAidResourceForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aspace_findingaid_config';
  }

/**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aspace_findingaid.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['connection'] = [
      '#type' => 'details',
      '#title' => t('ArchivesSpace API Connection Settings'),
      '#open' => TRUE,
    ];

    $form['connection']['archivesspace_base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ArchivesSpace API URL'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_base_uri',
    ];

    $form['connection']['archivesspace_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ArchivesSpace Username'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_username', 
    ];

    $form['connection']['archivesspace_password'] = [
      '#type' => 'password',
      '#title' => $this->t('ArchivesSpace Password'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_password',
      '#description'   => t('Leave blank to leave unchanged.'),
    ];

    $form['archivesspace_repository'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository to process'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_repository',
      '#description' => t('The Archivesspace repository to process migration'),
    ];

    return parent::buildForm($form, $form_state);
  }
}
