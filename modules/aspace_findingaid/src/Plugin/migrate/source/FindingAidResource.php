<?php

namespace Drupal\aspace_findingaid\Plugin\migrate\source;

use Drupal\aspace_findingaid\ArchivesSpaceIterator;
use Drupal\aspace_findingaid\ArchivesSpaceSession;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Provide Archivesspace finding aid migration Source plugin
 *
 * @MigrateSource(
 *   id = "aspace_findingaid"
 * )
 */
class FindingAidResource extends SourcePluginBase {

  /**
   * ArchivesSpace Session object.
   */
  protected $session;

  /**
   * Object type to migrate.
   *
   * @var string
   */
  protected $objectType;

  /**
   * Last updated timestamp (ISO 8601).
   *
   * @var string
   */
  protected $lastUpdate;

  /**
   * object types to migrate
   *
   * @var array
   */
  protected $objectTypes = [
    'repositories',
    'resources',
  ];

  /**
   * The fields for this source.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * The ArchivesSpace repository we are migrating.
   *
   * @var string
   */
  protected $repository = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $this->objectType = $configuration['object_type'];

    switch ($this->objectType) {

       case 'resources':
         $this->fields = [
          'uri' => $this->t('URI'),
          'title' => $this->t('Title'),
          'repository' => $this->t('Repository'),
          'dates' => $this->t('Dates'),
          'ead_id' => $this->t('EAD ID'),
          'ead_location' => $this->t('EAD Location'),
          'extents' => $this->t('Extents'),
          'external_documents' => $this->t('External Documents'),
          'finding_aid_author' => $this->t('Finding Aid Author'),
          'finding_aid_date' => $this->t('Finding Aid Date'),
          'finding_aid_description_rules' => $this->t('Description Rules'),
          'finding_aid_filing_title' => $this->t('Filing Title'),
          'finding_aid_language' => $this->t('Finding Aid Language'),
          'finding_aid_status' => $this->t('Finding Aid Status'),
          'finding_aid_title' => $this->t('Finding Aid Title'),
          'id_0' => $this->t('ID Position 0'),
          'id_1' => $this->t('ID Position 1'),
          'id_2' => $this->t('ID Position 2'),
          'id_3' => $this->t('ID Position 3'),
          'language' => $this->t('Language Code'),
          'level' => $this->t('Level'),
          'linked_agents' => $this->t('Linked Agents'),
          'notes' => $this->t('Notes'),
          'publish' => $this->t('Publish'),
          'resource_type' => $this->t('Resource Type'),
          'restrictions' => $this->t('Restrictions'),
          'subjects' => $this->t('Subjects'),
          'suppressed' => $this->t('Suppressed'),
          'user_mtime' => $this->t('User Modified Time'),
        ];
        break;

      case 'repositories':
        $this->fields = [
          'uri' => $this->t('URI'),
          'name' => $this->t('Name'),
          'repo_code' => $this->t('Repository Code'),
          'publish' => $this->t('Publish?'),
          'agent_representation' => $this->t('Agent Representation'),
        ];
        break;

      default:
        break;
    }

    if (isset($configuration['last_updated'])) {
      $this->lastUpdate = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $configuration['last_updated']);
    }
    else {
      $this->lastUpdate = new \DateTime();
      $this->lastUpdate->setTimestamp(0);
    }

    //get repository with fallback logic: retrieve it from migration YML source configuration, otherwise from module config settings
    if (isset($configuration['repository'])) {
      if (is_int($configuration['repository'])) {
        $this->repository = '/repositories/' . $configuration['repository'];
      }
      elseif (preg_match('#^/repositories/[0-9]+$#', $configuration['repository'])) {
        $this->repository = $configuration['repository'];
      }
    } else {
	$this->repository = \Drupla::config('aspace_findingaid.settings')->get('archivesspace_repository');
    }

    // Create the session
    // Send migration config auth options to the Session object.
    if (isset($configuration['base_uri']) ||
        isset($configuration['username']) ||
        isset($configuration['password'])) {
      // Get Config Settings.
      $base_uri = ($configuration['base_uri'] ?? '');
      $username = ($configuration['username'] ?? '');
      $password = ($configuration['password'] ?? '');

      $this->session = ArchivesSpaceSession::withConnectionInfo(
          $base_uri, $username, $password
        );

      // No login info provided by the migration config.
    }
    else {
      $this->session = new ArchivesSpaceSession();
    }

  }

  /**
   * Initializes the iterator with the source data.
   *
   * @return \Iterator
   *   An iterator containing the data for this source.
   */
  protected function initializeIterator() {
    return new ArchivesSpaceIterator($this->objectType, $this->session, $this->repository);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
   //define ids used for identification
    $ids = [
      'uri' => [
        'type' => 'string',
      ],
    ];
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return $this->fields;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "ArchivesSpace Finding Aid";
  }

}
