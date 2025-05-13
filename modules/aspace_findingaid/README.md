# Archivesspace Finding Aid Migration

This module is designed to utilize an exsiting drupal archivesspace module to facilitate archivesspace finding aid data migration to drupal site
## Usage
1. Install the module from its parent module 'custom_findingaid_module'
    - Install via composer (`composer require drupal/custom_findingaid_module`)
2. Enable the module and its dependencies
    -   `drush en -y migrate_tools, migrate_plus`
    -   `drush en -y aspace_findingaid`
    -   Confirm modules status (`drush pml --type=module --status=enabled | grep migrate_plus`) 
3. Configurate Module Settings
   Archivesspace Finding Aid migration uses ArchivesSpace API endpoint. The Resource prefix uris are used to link its associated objects contained in the resource. Please visit  `/admin/configuration/Resource EAD settings` in your Drupal site to configure these settings before migration.

## Migration 
1. Use drush to execute migration. Add --limit or --update to limit data migration record or process a migration data updates
   -    `drush mim aspace_findingaid --limit=10`
