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
class FindingaidXSLTForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_findingaid_importer_xslt';
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

    //get number of the xslt-aeon pairs from form or initialize to 1
    $num_xslt_aeon = $form_state->get('num_xslt_aeon') ?? 1;
    $form_state->set('num_xslt_aeon', $num_xslt_aeon);

    //retrieve xslt upload path from configruation
    $upload_path = $config->get('xslt_file_path') ?? 'public://xslt_uploads';

    //new xslt-aeon file uploaded
    for ($i = 1; $i < $num_xslt_aeon; $i++) {
        $form["xslt_aeon_files"] = [
            '#type' => 'fieldset',
            '#title' => t('Upload a new Finding aid XSLT-AEON configuration'),
            '#tree' => TRUE, // set a hierarchy on xslt files to easily get value	
          ];
        $form["xslt_aeon_files"][$i]['file_desc'] =[
            '#type' => 'textfield',
            '#title' => $this->t('XSLT-AEON configuration description'),
            '#description' => $this->t('The new Finding aid XSLT-AEON configuration description'),
            '#required' => FALSE,
          ];
        $form["xslt_aeon_files"][$i]['xslt_file'] =[
            '#type' => 'managed_file',
	          '#title' => $this->t('Upload a new XSLT to process Finding aids'),
		        '#description' => $this->t('The XSLT used to process Finding aids'),
		        '#upload_validators' => [
				'file_validate_extensions' => ['xslt xsl'],
			],
			'#upload_location' => $upload_path,
			'#required' => FALSE,
            ];
        $form["xslt_aeon_files"][$i]['aeon_baseurl'] =[
            '#type' => 'textfield',
            '#title' => $this->t('AEON baseUrl Setting'),
            '#description' => $this->t('The AEON baseUrl used to link Finding aids'),
            ];
        }    
	  
    //add additional configurations 
    $form['add_xslt_aeon'] = [
	      '#type' => 'submit',
	      '#name' => 'add_xslt_aeon',
	      '#value' => $this->t('Add another XSLT_AEON file configuration'),
	      '#submit' => ['::add_XSLT_AEON']
	    ];

    //List the available configurations:
     if ( !empty($existing_xslt_aeon_files) ) {
	      $form['existing_xslt_aeon_pairs'] = [
		    '#type' => 'details',
		    '#title' => t('Existing XSLT-AEON configurations to process Finding aids'),
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
   * Submit handler for adding additional XSLT Aeon Pair                                                                                                                                   
   */  
  public function add_XSLT_AEON(array &$form, FormStateInterface $form_state) {
   $num_xslt_aeon = $form_state->get('num_xslt_aeon') ?? 1;
   $form_state->set('num_xslt_aeon', $num_xslt_aeon+1);
   $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //identify the button
    $submit_trigger = $form_state->getTriggeringElement();
    if ($submit_trigger['#name'] === 'add_xslt_aeon'
	          || strpos($submit_trigger['#name'], 'delete_') !== false) { return;}
    $config =  $this->getConfig();
    $existing_xslt_aeon_files = $config->get('xslt_aeon_files') ?: [];
    $new_xslt_aeon_pairs = $config->get("num_xslt_aeon");
    $new_upload_files = [];

    //new pair uploaded
    foreach ($form_state->getValue('xslt_aeon_files', []) as $key=>$file_data) {  
	      if ( !empty($file_data['xslt_file'][0])  ) {
        	  $file_desc = $file_data['file_desc'];
		  $aeon_baseurl = $file_data['aeon_baseurl'];
		  $file = File::load($file_data['xslt_file'][0]);
		  if ($file) {
			  $filename = $file->getFilename();
			  $file_obj = \Drupal::entityTypeManager()->getStorage('file')->load($file->id());
            		  //remove all the traillig pattern added by drupal, eg use 'testfile'as key for file testfile_100.xsl
			  $fname = preg_replace('/^(.*?)(?:_\d+)?\.[^.]*$/', '$1', $filename); 
			  $curr_template = $config->get('xslt_aeon_files.'.$file_desc);
			  if ( $curr_template)  {
		 //delete previous xslt if endUser set a same configuration desc and upload xslt with the same filename
		 		$inner_key = 'xslt_file';
                		$fid = $curr_template[$inner_key];
				$prev_file = File::load($fid);
				if ($prev_file) {
					$prev_file->delete();
					}
			  }
			  //save file
			  $file->setPermanent();
			  $file->save();
                \Drupal::logger('custom_findingaid_importer')->info('File @fname uploaded successfully to @fpath',
                    ['@fname'=>$filename, '@fpath'=>$file_obj->getFileUri(),]);

			  //store configurations
			  $new_upload_files[$file_desc] = [
				  'xslt_file' => $file->id(),
				  'aeon_baseurl' => $aeon_baseurl,
			  ];
                //now save all xslt files to configuration
                	  $updated_files = array_merge($existing_xslt_aeon_files, $new_upload_files);
			  $config->set('xslt_aeon_files', $updated_files)->save();
		  }
	      }
         }
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
