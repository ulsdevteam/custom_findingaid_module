<?php

namespace Drupal\aspace_findingaid\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\FileStorage;

/**
 * Configure ArchivesSpace finding aid migrate repository settings
 */
class RepositoryXSLTForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aspace_findingaid_repo_config';
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

    $form['repository'] = [
      '#type' => 'details',
      '#title' => $this->t('ArchivesSpace repository and XSLT Settings'),
      '#open' => TRUE,
    ];

      $form['repository']['archivesspace_repository'] = [                                     
      '#type' => 'number',                                                 
      '#title' => $this->t('Repository ID'),
      '#config_target' => 'aspace_findingaid.settings:archivesspace_repository',
      '#description' => $this->t('The ID of the ArchivesSpace repository to process'),
      '#required' => TRUE,
    ];             

      $form['repository']['archivesspace_xslt_file'] = [
	'#type' => 'managed_file',
	'#title' => $this->t('ArchivesSpace XSLT transformation file'),
	'#description' => $this->t('The XSLT used to process ArchivesSpace Finding aids'),
	'#upload_location' => 'public://as_xslt_uploads',
	'#upload_validators' => [
		'file_validate_extensions' => ['xslt xsl'],
		],
	'#config_target' => 'aspace_findingaid.settings:archivesspace_xslt_file',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //handle archivesSpace xslt
    $fid = $form_state->getValue('archivesspace_xslt_file')[0] ?? NULL;
    $this->config('aspace_findingaid.settings') 
	->set('archivesspace_xslt_file', $fid)
	->save();
    //make file permanent
    if($fid) {
	$as_xslt_file = \Drupal\file\Entity\File::load($fid);
	$as_xslt_file->setPermanent();
	$as_xslt_file->save();
	}

    //locate the template file from module samples configuration directory
    $conf_filepath = \Drupal::service('extension.list.module')->getPath('aspace_findingaid').'/config/samples';
    $file_storage = new FileStorage($conf_filepath);
  
    $file_template = $file_storage->read('migrate_plus.migration.aspace_findingaid');
    if (!$file_template) {
	\Drupal::logger('aspace_findingaid')->error('Failed to find ArchivesSpace Finding Aid Migration Template YAML in configuration samples directory');
        return;
	}
    //update repository value from configuration form
    $file_template['source']['repository'] = '/repositories/'.$form_state->getValue('archivesspace_repository');
    $file_template['label'] = $file_template['label']. $form_state->getValue('archivesspace_repository');
    $file_template['id'] = $file_template['id'].'_'.$form_state->getValue('archivesspace_repository');
  
    //save to active configuration
    $config = \Drupal::configFactory()->getEditable('migrate_plus.migration.aspace_findingaid_'.$form_state->getValue('archivesspace_repository'));   
    $config->setData($file_template)->save();
    \Drupal::logger('aspace_findingaid')->info('A new migration configuration for repository: @repo has been created', 
			['@repo'=>$form_state->getValue('archivesspace_repository') ]);

    //clear migration cache
    \Drupal::service('plugin.manager.migration')->clearCachedDefinitions();

    parent::submitForm($form, $form_state);
  }

}
