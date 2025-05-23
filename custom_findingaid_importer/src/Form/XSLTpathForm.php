<?php

namespace Drupal\custom_findingaid_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure custom finding aid xlst filepath settings
 */
class XSLTpathForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_findingaid_importer_filepath';
  }

/**
   * {@inheritdoc}
 */   
  protected function getEditableConfigNames() {
    return ['custom_findingaid_importer.settings'];
  } 

/**                                                                                            
   * Helper function to get the configuration object                                                                       
   */                                                                                          
  protected function getConfig() {                                     
    return \Drupal::configFactory()->getEditable('custom_findingaid_importer.settings');                                          
  } 

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
     $config = $this->getConfig(); 

    $form['Uploaded File Path'] = [
      '#type' => 'details',
      '#title' => $this->t('File System directory to host upload XSLT'),
      '#open' => TRUE,
    ];
    $form['Uploaded File Path']['xslt_file_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('XSLT File Upload Path'),
      '#config_target' => 'custom_findingaid_importer.settings:xslt_file_path',
      '#default_value' => $config->get('xslt_file_path') ?? 'public://xslt_uploads',
      '#description' => $this->t('Define XSLT file upload directory path (e.g., public://xslt_uploads)'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }
}
