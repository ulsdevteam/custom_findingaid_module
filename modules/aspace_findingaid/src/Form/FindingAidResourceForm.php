<?php

namespace Drupal\aspace_findingaid\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\FileStorage;

/**
 * Configure ArchivesSpace finding aid migration settings
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
      '#type' => 'number',                                                 
      '#title' => $this->t('Repository ID'),
      '#default_value' => '',
      '#config_target' => 'aspace_findingaid.settings:archivesspace_repository',
      '#description' => t('The ID of the ArchivesSpace repository to process'),
      '#required' => TRUE,
    ];             

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //locate the template file from module samples configuration directory
    $conf_filepath = \Drupal::service('extension.list.module')->getPath('aspace_findingaid').'/config/samples';
    $file_storage = new FileStorage($conf_filepath);
  
    $file_template = $file_storage->read('migrate_plus.migration.aspace_findingaid');
    if (!$file_template) {
	\Drupal::logger('aspace_findingaid')->error('Failed to find ArchivesSpace Finding Aid Migration Template YAML in configuration samples directory');
        return;
	}
    //update repository value from configuration form
    $file_template['source']['repository'] = 'repository/'.$form_state->getValue('archivesspace_repository');
    $file_template['label'] = $file_template['label']. $form_state->getValue('archivesspace_repository');
    $file_template['id'] = $file_template['id'].'_'.$form_state->getValue('archivesspace_repository');
  
    //save to active configuration
    $config = \Drupal::configFactory()->getEditable('migrate_plus.migration.aspace_findingaid_'.$form_state->getValue('archivesspace_repository');   
    $config->setData($file_template)->save();
    \Drupal::logger('aspace_findingaid')->info('A new migration configuration for repository: @repo has been created', 
			['@repo'=>$form_state->getValue('archivesspace_repository') ]);

    //clear migration cache
    \Drupal::service('plugin.manager.migration')->clearCachedDefinitions();

    parent::submitForm($form, $form_state);
  }

}
