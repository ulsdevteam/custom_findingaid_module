<?php

namespace Drupal\custom_findingaid_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileExists;
use Drupal\file\Entity\File;

/**
 * Configure custom finding aid xlst settings
 */
class FindingaidXSLTList extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_findingaid_importer_xslt_list';
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
    //retrieve existing xslt if configured
    $config = $this->getConfig(); 
    $existing_xslt_aeon_files = $config->get('xslt_aeon_files') ?: [];

    //List the available configurations:
     if ( !empty($existing_xslt_aeon_files) ) {
	      $form['existing_xslt_aeon_pairs'] = [
		    '#type' => 'details',
		    '#title' => t('Existing XSLT configurations to process Finding aids'),
		    '#open' => TRUE,
	      ];
	      //iterate available xslt-aeon pairs for display:
	      foreach ($existing_xslt_aeon_files as $fid_key => $file_arr_data) {
		        $xslt_file_id = $file_arr_data['xslt_file'];
		            $form['existing_xslt_aeon_pairs'][$fid_key] = [
			          '#type' => 'item',
			          '#markup' => $this->t('XSLT: @fid', ['@fid'=> $fid_key]),
			          'delete' => [
				        '#type' => 'submit',
				        '#value' => $this->t('Delete'),
				        '#name' => 'delete_'.$xslt_file_id,
				        '#submit' => ['::delete_XSLT_AEON'],
					      ],
			      ];
		      }
      }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //identify the button
    $submit_trigger = $form_state->getTriggeringElement();
    if ($submit_trigger['#name'] === 'add_xslt_aeon'
	          || strpos($submit_trigger['#name'], 'delete_') !== false) { return;}
    parent::submitForm($form, $form_state);
}

/**                                                                                   
 * {@inheritdoc}                                                                      
*/
   public function delete_XSLT_AEON(array &$form, FormStateInterface $form_state) {
	$config =  $this->getConfig();
	$config_param = $config->get('xslt_aeon_files'); 

	//retrieve XSLT-AEON configuration item to be deleted 
	$config_del = $form_state->getTriggeringElement();
	$config_fid = str_replace('delete_', '', $config_del['#name']);	
	$del_item ="";
	foreach($config_param as $key => $val) {
		if ( isset($val['xslt_file']) && $val['xslt_file'] == $config_fid ) {
			$del_item = $key;
			$config_item = $config_param[$key];
			$del_file = File::load($config_item['xslt_file']);
			if ( $del_file ) {
				$del_file->delete();
			}
			unset($config_param[$key]);
			$config->set('xslt_aeon_files', $config_param)->save();
			\Drupal::logger('custom_findingaid_importer')->info('Configuration @item deleted.',['@item' =>$del_item]);
			break;
		}
	}
   }	
}
