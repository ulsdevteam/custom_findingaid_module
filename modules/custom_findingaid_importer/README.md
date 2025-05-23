# Custom Finding Aid Importer

This module is designed to allow a user to upload a Finding Aid EAD file and the XSLT file used to process the XML into HTML, then transform finding aid data to body of a Drupal node.  The module will create a new Drupal Content Type of "Finding Aid" for these nodes.

## Installation and Configuration
1. Install module 'custom findingaid module'
    - Install via composer (`composer require drupal/custom_findingaid_module`)
2. Enable the module and its dependencies via drush or Drupal site
    -  via drush:  `drush en -y custom_findingaid_importer`
    -  via Drupal site: Go to Extend/Install new module, locate Custom module 'Custom Finding Aid Importer Module' and install.
    -  Confirm modules status (`drush pml --type=module --status=enabled | grep migrate_plus`) 
3. Configurate Module Settings
   Custom Finding Aid Importer Module configuration include 'XSLT upload path setting' and 'New XSLT uploader'. The first variable defines the file location to store the uploaded XSLT files. The 'New XSLT uploader' allows enduser to upload XSLT and baseUrl to link to Aeon portal site. Please locate path:  `/admin/configuration/Finding Aid file settings` in your Drupal site to configure these settings before migration.

## Usage 
1. Go to Drupal site, locate the Finding Aid creation form via path: Content -> Add content -> Finding Aid
2. Upload a Finding Aid EAD file under field label 'Upload Finding Aid XML file to process'
3. Select an XSLT configuration from drop down box 'Select XSLT used to process Finding Aid data'
4. Click 'Transform'
5. New Content entity should be created and displayed under Content.
