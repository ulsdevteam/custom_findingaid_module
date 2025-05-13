# Custom Finding Aid Migration module

This module contains two submodules. Aspace findingaid module is to migrate finding aid data from ArchivesSpace data resource to modern islandora site; custom findingaid importer module is to transform findindaid data imported from the endusers using xml format, and then generate findingaid content type entity in modern islandora site. 
## Usage
1. Install the module
    - Install via composer (`composer require drupal/custom_findingaid_module`)
2. Enable the module and its dependencies
    -   `drush en -y migrate_tools, migrate_plus`
    -   `drush en -y custom_findingaid_module`
    -   Confirm modules status (`drush pml --type=module --status=enabled | grep migrate_plus`)
3. Configurate Module Settings
   Archivesspace Finding Aid module configuratin path  `/admin/configuration/Resource EAD settings`
   Custom Finding Aid import module configuration path `/admin/configuration/Finding Aid file settings`

## Migration
   Please refer to submodule README
