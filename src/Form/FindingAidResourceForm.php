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
      '#title' => t('ArchivesSpace API Connection'),
      '#open' => TRUE,
    ];

    $form['connection']['archivesspace_base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ArchivesSpace API Prefix'),
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
      '#config_target' => 'aspace_findingaid.settings:archivesspace_viewonlineuri',
    ];

    $form['resource_link_prefix']['as_resources_readingroom_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resource Readingroom Prefix'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_readingroomuri',
    ];

    return parent::buildForm($form, $form_state);
  }
}
