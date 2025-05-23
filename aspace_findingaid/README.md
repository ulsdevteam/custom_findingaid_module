# ArchivesSpace Finding Aid Migration

This module is designed to utilize an exsiting drupal archivesspace module to facilitate archivesspace finding aid data migration to drupal site
## Usage
1. Install module 'Archivesspace Finding Aid migration Module'
    - Install via composer (`composer require drupal/aspace_findingaid`)
2. Enable the module and its dependencies via drush or Drupal site
    -  via drush: First enable its dependencies module `drush en -y custom_findingaid_importer`, then enable `drush en -y aspace_findingaid`
    -  via Drupal site: Go to Extend/Install new module, locate custom module 'Custom Finding Aid Importer Module', and install it;       then locate 'Archivesspace Finding Aid module Migration' and install.
Then locate module 'Archivesspace Finding Aid migration Module', and install it.
    -   Confirm modules status (`drush pml --type=module --status=enabled | grep migrate_plus`) 
3. Configurate Module Settings
   Archivesspace Finding Aid migration uses ArchivesSpace API endpoint. The Resource prefix uris are used to link its associated objects contained in the resource. Please visit  `/admin/configuration/Resource EAD settings` in your Drupal site to configure these settings before migration.

## Migration 
1. Use drush to execute migration. Add --limit or --update to limit data migration record or process a migration data updates
   -    `drush mim aspace_findingaid --limit=10`
