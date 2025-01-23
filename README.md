## ArchivesSpace Resource content migration
This module is a lite version of drupal archivesspace module to enable users to migrate uls archivesspace resource and finding aid data to drupal site
## Usage
1. Install the module
    - Install via composer (`composer require drupal/archivesspace`) OR
    - Install manually
        1. Ensure all the dependencies are installed.
        2. Save the module to your Drupal site's modules directory.

### Migration content types
- Repositories
- Resources


### Configuration
ULS resource content migration uses ArchivesSpace API point. The Resource prefix uris are used to link its associated objects contained in the resource. Please visit  `/admin/configuration/ULS resource settings` in your Drupal site to configure these settings before migration.

## Drupal Title Field
- Use drupal title_length module to increase node title length. In the settings.php under the drupal installation direction (e.g. ..drupal/web/sites/default/settings.local.php),  add $settings['node_title_length_chars'] = 16000 before enabling the title_length module.

