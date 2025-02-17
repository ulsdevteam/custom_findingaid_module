<?php

namespace Drupal\uls_resource;
if (!defined('ULS_XSLT')) {
	define('ULS_XSLT', implode(DIRECTORY_SEPARATOR, array(__DIR__, 'uls_ead.xslt')));
	}
if (!defined('FINDINGAID_PREFIX')) {
	define('FINDINGAID_PREFIX', "Guide to the");
}

/**
 * Manages iteration of ArchivesSpace API search result sets.
 */
class ArchivesSpaceIterator implements \Countable, \Iterator {

  /**
   * ArchivesSpace Session object.
   *
   * @var Drupal\uls_resource\ArchivesSpaceSession
   */
  protected $session;

  /**
   * ArchivesSpace object type we are currently iterating.
   *
   * @var string
   */
  protected $type;

  /**
   * ArchivesSpace object types available.
   *
   * @var array
   */
  protected $types = [
    'repositories',
    'resources',
  ];

  /**
   * Repository URI we are iterating over.
   *
   * @var string
   */
  protected $repository;

  /**
   * Count of items to iterate over.
   *
   * @var int
   */
  protected $count = -1;

  /**
   * Current set of loaded items we are iterating over.
   *
   * @var array
   */
  protected $loaded = [];
  /**
   * Current position of the iterator.
   *
   * @var int
   */
  protected $position = 0;

  /**
   * Current page number.
   *
   * @var int
   */
  protected $currentPage = 0;

  /**
   * Last page this iterator will reach.
   *
   * @var int
   */
  protected $lastPage;

  /**
   * Offset First.
   *
   * @var int
   */
  protected $offsetFirst = 0;

  /**
   * Offset last.
   *
   * @var int
   */
  protected $offsetLast = 0;

  /**
   * Default max set by ArchivesSpace is 250.
   *
   * @var int
   */
  protected $pageSize = 250;

  /**
   * {@inheritdoc}
   */
  public function __construct(string $type, ArchivesSpaceSession $session, string $repository) {
    if (!in_array($type, $this->types)) {
      throw new \InvalidArgumentException('Can\'t iterate over type: ' . $type);
    }
    $this->position = 0;
    $this->type = $type;
    $this->session = $session;
    $this->repository = $repository;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->position = 0;
    $this->loadPage(1);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $this->rewind();
    return $this->count;
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->loaded[$this->position];
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    ++$this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {

    if ($this->position < count($this->loaded)) {
      return TRUE;
    }

    if ($this->currentPage < $this->lastPage) {
      $this->loadPage($this->currentPage + 1);
      return $this->valid();
    }

    return FALSE;
  }

  /**
   * process ead xml from resource and generate html using loaded stylesheet
   * @param str $xml 
   *     resource description 
   * @param str $eadName
   *     ead unique identifier
   * @param array $file_params
   *     -$file_xslt stylesheet tranformation template 
   * @return str $f_content
   */
   public function ead_to_html($xml, $eadName, $file_params) {
	//load xml and xslt
	$d_xml= new \DOMDocument();
	$d_xsl= new \DOMDocument();
        try {
		if ($d_xml->loadXML($xml) and $d_xsl->load($file_params["file_xslt"])) {
		$xsl_proc = new \XSLTProcessor();
		$xsl_proc->registerPHPFunctions();
		
    		$viewonlineUri =\Drupal::config('uls_resource.settings')->get('archivesspace_viewonlineuri');                                                 
    		$readingroomUri =\Drupal::config('uls_resource.settings')->get('archivesspace_readingroomuri');                                                
    		if ( !empty($viewonlineUri) &&  preg_match("@^https?://@", $viewonlineUri)  ) {                                                                                                           
        		$xsl_proc->setParameter('', 'viewonlineuri', $viewonlineUri);                                                           
        		} else  {
				print 'Error: Invalid resource viewonline URI: ' . $viewonlineUri. PHP_EOL;
			}
		 if ( !empty($readingroomUri) &&  preg_match("@^https?://@", $readingroomUri)  ) {                 
                        $xsl_proc->setParameter('', 'readingroomuri', $readingroomUri);                            
                        } else  {                                                                     
                                print 'Error: Invalid resource viewonline URI: ' . $readingroomUri. PHP_EOL;
                        }       
        	libxml_use_internal_errors(true);
        	$result = $xsl_proc->importStyleSheet($d_xsl);
        	if(!$result) 
			{
                        	print_r(libxml_get_errors());
				echo PHP_EOL;
				libxml_clear_errors();
				return "";
			}
		else {
       			libxml_use_internal_errors(false);
         		//transform
         		$xml_result = $xsl_proc->transformToDoc($d_xml);
			//save tranformed xml doc as local htmlfile
         		$f_content = $xml_result->saveHTML();
			return $f_content;
 		     }	
		}
	}
	catch (Exception $e) {
		echo "Error: failed to process ead to xml: " . $e->getMessage(). PHP_EOL;
		}
        return "";
	}

  /**
   * Loads a page of ArchivesSpace results.
   *
   * @param int $page
   *   An integer representing the page to load.
   */
  protected function loadPage($page) {
    if (isset($this->lastPage) && $page > $this->lastPage) {
      return;
    }

    $parameters = [
      'page' => $page,
      'page_size' => $this->pageSize,
    ];

   //retrieve resource data needed and its ead information 
   if($this->type == "resources")
	{
	//echo "STEP4). Start GET request to resources Type: " . __FILE__ .PHP_EOL;
           $ead_parameters = [
      		'include_unpublished' => "False",
      		'include_daos' => "True",
                'include_uris' => "True",
                'ead3'=> "False",
                ];		

	  $file_params = [
		'file_xslt' => ULS_XSLT,
		];
          $ead_results=[];

          //Iterate resources results to extract ead information
	  $results = $this->session->request('GET', $this->repository . '/' . $this->type, $parameters);
          foreach ($results['results'] as $item) {
		$item_ead = [];
		//filter out resources with the published finding_aid
          	if (($item['publish']) and ($item['is_finding_aid_status_published'])) {
			//keep primary resource information
			$item_ead['uri'] = $item['uri']; 
			if (array_key_exists('finding_aid_title', $item)) {
				$item_ead['title'] = $item['finding_aid_title'];
			} else {
				$item_ead['title'] = FINDINGAID_PREFIX . $item['title'];
				}
			array_key_exists('ead_id', $item) ? $item_ead['ead_id'] = $item['ead_id'] : NULL;
		        	
			//construct resource identifier with the concatnated ids 
			array_key_exists('id_0', $item) ? $item_ead['id_0'] = $item['id_0'] : NULL;
                        array_key_exists('id_1', $item) ? $item_ead['id_1'] = $item['id_1'] : Null;
                        array_key_exists('id_2', $item) ? $item_ead['id_2'] = $item['id_2'] : NULL;
                        array_key_exists('id_3', $item) ? $item_ead['id_3'] = $item['id_3'] : NULL;

		   	//retrieve resource ead xml
		    	$resourceId =  substr(strrchr($item['uri'], "/"), 1);
	            	$ead_xml = $this->session->request('GET', 
						$this->repository . '/resource_descriptions/'.$resourceId .'.xml', $ead_parameters, FALSE, TRUE);	
		    	//Replaced special char & used that  not part of html entity like &abc123;, &#abc;
			$ead_xml_format = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $ead_xml);
			libxml_use_internal_errors(true); //store error in memory
		    	$tmp_xml =simplexml_load_string($ead_xml_format);
			if (!$tmp_xml) { 
				echo "Failed to load ead raw data ".$item_ead['title'] .PHP_EOL;
				foreach(libxml_get_errors() as $e) {
					echo "Error: " . $e->message . PHP_EOL; 
				}
				libxml_clear_errors();
				$ead_location  = null;
			} else {
                    		$ead_fname = str_replace('/', '_', $item['uri']); //construct a unique ead fileName  
		    		$ead_location = $this->ead_to_html($tmp_xml->asXML(), $ead_fname, $file_params);
			}	
			if (empty($ead_location)) { 
				echo "Failed transforming ead to html: " .$item_ead['title'] .PHP_EOL;
				} 
		    	$item_ead['ead_loc'] = $ead_location; 
                	array_push($ead_results, $item_ead);
			} else {
				echo "Skip processing resource :" . $item['title'] ." .Check resource data's publish status. " .PHP_EOL;
			}	
		}
                //echo "size of ead result: " . sizeof($ead_results). PHP_EOL;
		$results['results'] = $ead_results;
	} else {
    		$results = $this->session->request('GET', $this->repository . '/' . $this->type, $parameters);
	}
    // Repositories aren't paginated like everything else.
    if ($this->type == 'repositories') {
      $this->count    = count($results);
      $this->position = 0;
      $this->loaded   = $results;
    }
    else {
      $this->count       = $results['total'];
      $this->currentPage = $results['this_page'];
      $this->lastPage    = $results['last_page'];
      $this->position    = 0;
      $this->loaded      = $results['results'];
    }

  }

}
