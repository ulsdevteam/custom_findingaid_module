<?php

namespace Drupal\aspace_findingaid\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Core\Cache\Cache;

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

   $source_config = \Drupal::config('migrate_plus.migration.aspace_findingaid')->get('source');
   
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
      '#title' => $this->t('Repository to process migration'),                          
      '#default_value' => $source_config['repository'],
      '#config_target' => 'aspace_findingaid.settings:archivesspace_repository',
      '#description' => t('The Archivesspace repository to process migration'), 
      '#required' => TRUE,
    ];             

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // generate the migration YML file

    //locate the orginal file
    $conf_filepath = \Drupal::service('extension.list.module')->getPath('aspace_findingaid').'/config/install';
    $file_storage = new FileStorage($conf_filepath);
  
    $file_template = $file_storage->read('migrate_plus.migration.aspace_findingaid');
    if (!$file_template) {
	\Drupal::logger('aspace_findingaid')->error('Failed to find Archivesspace Finding Aid Migration YAM in configuration install directory');
        return;
	}
    //update repository value from configuration form
    $config = \Drupal::config('migrate_plus.migration.aspace_findingaid');
    $file_template['source']['repository'] = (int)$form_state->getValue('archivesspace_repository');
    $file_template['label'] = $config->get('label'). $file_template['source']['repository'];
    $file_template['id'] = $config->get('id').'_'.$file_template['source']['repository'];
    $config_sync_directory = Settings::get('config_sync_directory');
   
    $new_repo_file = "$config_sync_directory/migrate_plus.migration.aspace_findingaid_" . $file_template['source']['repository'] .".yml";
    $encode_content = \Drupal::service('serialization.yaml')->encode($file_template);
    file_put_contents($new_repo_file, $encode_content);

    //clearcache and sync
    drupal_flush_all_caches();
    parent::submitForm($form, $form_state);
  }

}
