# ArchivesSpace Finding Aid Migration

This module is designed to connect to ArchivesSpace to download Finding Aids via the Drupal Migrate framework.

## Installation and Configuration
1. Install module 'custom findingaid module'
    - Install via composer (`composer require drupal/custom_findingaid_module`)
2. Enable the module and its dependencies via drush or Drupal site
    -  via drush: First enable its dependencies module `drush en -y custom_findingaid_importer`, then enable `drush en -y aspace_findingaid`
    -  via Drupal site: Go to Extend/Install new module, locate custom module 'Custom Finding Aid Importer Module', and install it;       then locate 'Archivesspace Finding Aid module Migration' and install.
    -   Confirm modules status (`drush pml --type=module --status=enabled | grep migrate_plus`) 
3. Configurate Module Settings
   ArchivesSpace Finding Aid migration uses ArchivesSpace API endpoint, which must be configured with your ArchivesSpace URL, username, and password. Please visit  `/admin/configuration/Resource EAD settings` in your Drupal site to configure these settings before migration.

## Migration 
1. Use drush to execute migration. Add --limit or --update to limit data migration record or process a migration data updates
   -    `drush mim aspace_findingaid --limit=10`
