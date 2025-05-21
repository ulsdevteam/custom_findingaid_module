<?php

namespace Drupal\custom_findingaid_importer\Utility;

use Drupal\file\Entity\File;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
* Utility function for xml tranformation via xslt
**/
class xmlTransformerHelper {
	use MessengerTrait;
	use StringTranslationTrait;

/**
 * Transforms an xml using XSLT
 * @param data_parameters: 
 *  array containing xml data and xml data_src (aspace | other) indicating xml from archivesSpace or 
 *  uploading from other resource
 * @param int $xslt_file_id
 *
 * @return: the transformed HTML as string or FALSE on failure
 */
 public static function xmlTransformer(array $data_parameters = [], $xslt_file_id) {
	if ( array_key_exists("data_src", $data_parameters ) && $data_parameters['data_src']=="aspace" ) {
	}
	else if ( array_key_exists("data_src", $data_parameters) && $data_parameters['data_src']=="others" ) {
		//get xslt fileID from xslt_aeon_files array
		$xslt_id = $xslt_file_id['xslt_file'];
		$xslt_file = File::load($xslt_id); 
		
		//file entity exists in drupal db
		if ( !$xslt_file ) { 
			\Drupal::logger('custom_findingaid_importer')->error(t('Failed to find XSLT file.'));
			return FALSE;
		}
		$xslt_path = \Drupal::service('file_system')->realpath($xslt_file->getFileUri());
       //load xml document from the uploaded file path
        $xml_doc = new  \DOMDocument();
        if ( !$xml_doc->load($data_parameters['xml_path']) ) {
			\Drupal::logger('custom_findingaid_importer')->error('Failed to load the xml file.');
			return FALSE;
		}
        $xsl= new \DOMDocument();
        if ( !$xsl->load($xslt_path) ) {
			\Drupal::logger('custom_findingaid_importer')->error('Failed to load the xslt file.');
            return FALSE;   	
		}
        //transform
        $proc = new \XSLTProcessor();
		$proc->registerPHPFunctions();
		$proc->importStyleSheet($xsl); 
        $html = $proc->transformToXML($xml_doc);

		if ( $html === FALSE) {
			\Drupal::logger('custom_findingaid_importer')->error(t('Failed to tranform the xml with the xslt file.')); 
			}
		return $html;
		}
	}
}
