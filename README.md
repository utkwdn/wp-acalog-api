# WP-Acalog-API
 
This is a Wordpress plug in to pull information from an Acalog catalog and store it in Wordpress. Before installing this plugin, you will need to create a custom post type with a number of associated fields. These are:

Custom post type slug name: acalog_program

Custom fields:

* course_department
* program_type
* degree_type
* program_content
* core
* catoid
* poid

Once the plugin is installed and activated, there will be a new item under Settings called Acalog API. You'll need the Acalog Catalog URL (the URL where you normally access the catalog), the Acalog API URL (found in Acalog Admin's web services API section), and your API Key (generated in Acalog Admin's web services API section).

There are two optional fields. One is College or Department Name. Specify one if you want to limit the results to a particular college or department. Leave this blank to pull from all colleges and deparments in the institution. The other is Catalog Type. This limits the results to a particular catalog type. This may be either Graduate or Undergraduate. Leave this field blank to pull from both.