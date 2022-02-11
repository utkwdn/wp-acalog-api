<?php
   /*
   Plugin Name: WP Acalog API
   Plugin URI: https://github.com/utkwdn/wp-acalog-api
   description: A Wordpress plugin to pull Acalog catalog data into Wordpress.
   Version: 0.1.0
   Author: University of Tennessee, Office of Communications and Marketing
   Author URI: https://communications.utk.edu/
   License: GPL2+
   License URI: https://www.gnu.org/licenses/gpl-2.0.txt
   */

//Define the settings page for the Acalog API in Wordpress
function acalog_api_add_settings_page(){
	add_options_page( 'Acalog API Options', 'Acalog API', 'manage_options', 'haslam-acalog-api-options', 'acalog_api_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'acalog_api_add_settings_page' );

//Render the settings page for the Acalog API in Wordpres
function acalog_api_render_plugin_settings_page(){
	?>
    <h2>Haslam Acalog API Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields('acalog_api_options');
        do_settings_sections('acalog_api_plugin'); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
    </form>

	<form action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<input type="hidden" name="action" value="acalog_api_test">
		<?php submit_button( 'Manually Update' ); ?>
	</form>

    <?php
}

function acalog_api_register_settings(){
	register_setting('acalog_api_options', 'acalog_api_options');
    add_settings_section('acalog_api_settings', 'Acalog API Settings', 'acalog_api_section_text', 'acalog_api_plugin');

    add_settings_field('acalog_api_setting_catalog_url', 'Acalog Catalog URL', 'acalog_api_setting_catalog_url', 'acalog_api_plugin', 'acalog_api_settings');
    add_settings_field('acalog_api_setting_api_url', 'Acalog API URL', 'acalog_api_setting_api_url', 'acalog_api_plugin', 'acalog_api_settings');
    add_settings_field('acalog_api_setting_api_key', 'Acalog API key', 'acalog_api_setting_api_key', 'acalog_api_plugin', 'acalog_api_settings');
	add_settings_field('acalog_api_setting_college_name', 'College or Department Name', 'acalog_api_setting_college_name', 'acalog_api_plugin', 'acalog_api_settings');
	add_settings_field('acalog_api_setting_catalog_type', 'Catalog Type', 'acalog_api_setting_catalog_type', 'acalog_api_plugin', 'acalog_api_settings');
}
add_action('admin_init', 'acalog_api_register_settings');

function acalog_api_section_text() {
    echo '<p>Here you can set all the options for using the API</p>';
}

function acalog_api_setting_catalog_url() {
    $options = get_option('acalog_api_options');
    ?><input id='acalog_api_setting_catalog_url' name='acalog_api_options[catalog_url]' type='text' value='<?php print esc_attr($options['catalog_url']) ?>' /><?php
}

function acalog_api_setting_api_url() {
    $options = get_option('acalog_api_options');
    ?><input id='acalog_api_setting_api_url' name='acalog_api_options[api_url]' type='text' value='<?php print esc_attr($options['api_url']) ?>' /><?php
}

function acalog_api_setting_api_key() {
    $options = get_option('acalog_api_options');
    ?><input id='acalog_api_setting_api_key' name='acalog_api_options[api_key]' type='text' value='<?php print esc_attr($options['api_key']) ?>' /><?php
}

function acalog_api_setting_college_name() {
    $options = get_option('acalog_api_options');
    ?><input id='acalog_api_setting_college_name' name='acalog_api_options[college_name]' type='text' value='<?php print esc_attr($options['college_name']) ?>' /><?php
}

function acalog_api_setting_catalog_type() {
    $options = get_option('acalog_api_options');
    ?><input id='acalog_api_setting_catalog_type' name='acalog_api_options[catalog_type]' type='text' value='<?php print esc_attr($options['catalog_type']) ?>' /><?php
}

/*function acalog_api_manual_refresh_submit(){
	$interval = variable_get('acalog_api_interval', 60 * 60 * 24 *30);
	acalog_update_all();
	
	//Reset Acalog API in cron to keep from hitting the API call limit
	variable_set('acalog_api_next_execution', time() + $interval);
	drupal_set_message(t('All Departments, Programs, and Courses updated by the Acalog API.'), 'status');
}*/

add_action( 'admin_post_acalog_api_test', 'acalog_api_test' );

function acalog_api_test() {
    if ( isset ( $_GET['action'] ) && $_GET['action'] == 'acalog_api_test' ){
        print_r(acalog_update_all());
	}

    //die( __FUNCTION__ );
}

//deletes all nodes created by the Acalog API
function clear_acalog_data($node_type){
	$acalog_args = array('post_type' => $node_type, 'nopaging' => true);
	$acalog_posts = new WP_Query($acalog_args);
	
	if($acalog_posts->have_posts()){
		$i=0;
		while($acalog_posts->have_posts()){
			$acalog_posts->the_post();
			$acalog_ids[$i] = get_the_id();
			$i++;
		}
		wp_reset_postdata();
	}
			
	foreach($acalog_ids as $acalog_id){
		wp_trash_post($acalog_id);
	}

	//return "Delete finished.";
}

//determines the most recent active catalog, requires specifing Graduate or Undergraduate
function get_acalog_catalog($cattype){
	//build Acalog API URL and get output
	$path = "/v1/content";
	$params['key'] = get_option('acalog_api_options')['api_key'];
	$params['format'] = "xml";
	$params['method'] = "getCatalogs";
	$url = get_option('acalog_api_options')['api_url'].$path."?";
	
	foreach($params as $id => $param){
		$url .= "&".$id."=".$param;
	}
	
	$xml = file_get_contents($url);
	
	$dom = new DOMDocument;
	$dom2 = new DOMDocument;

	// clean up output
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom2->preserveWhiteSpace = false;
	$dom2->formatOutput = true;

	// substitute xincludes
	$dom->loadXML($xml);
	$dom->xinclude();
	$xml2 = $dom->saveXML();
	$dom2->loadXML($xml2);
	$dom2->xinclude();
	
	//run XSL to find the catalog ID
	$xsl = new DOMDocument;
	$xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_catalog.xsl'));
	
	$proc = new XSLTProcessor;
	$proc->registerPHPFunctions();
	$proc->setParameter('', 'cattype', $cattype);
	$proc->importStylesheet($xsl);
	
	//strip exccess text from catalog ID
	$catalog_id = $proc->transformToXML($dom2);
	$catalog_id = str_replace("acalog-catalog-", "", $catalog_id);
	$catalog_id = str_replace("\n", "", $catalog_id);
	
	//return catalog ID number
	return $catalog_id;
}

//Returns an array of the Acalog hierarchy owned by the specified college or department
function get_acalog_hierarchy($catid){
	if(get_option('acalog_api_options')['college_name'] != ""){
		//build Acalog API URL and get output
		$path = "/v1/search/hierarchy";
		$params['key'] = get_option('acalog_api_options')['api_key'];
		$params['format'] = "xml";
		$params['method'] = "search";
		$params['catalog'] = $catid;
		$params['query'] = 'parent:"'.get_option('acalog_api_options')['college_name'].'"';
		$params['options[limit]'] = "0";
		$url = get_option('acalog_api_options')['api_url'].$path."?";
		
		foreach($params as $id => $param){
			$url .= "&".$id."=".$param;
		}
	
		$xml = simplexml_load_file($url);
	
		$arts = $xml->xpath("//result");
	
		$hierarchy[0] = $options['college_name'];
		//$i = 1;
	
		foreach($arts as $art){
			$hierarchy[strval($art->id)] = strval($art->name);
			//$i++;
		}
	}else{
		//build Acalog API URL and get output
		$path = "/v1/content";
		$params['key'] = get_option('acalog_api_options')['api_key'];
		$params['format'] = "xml";
		$params['method'] = "getHierarchy";
		$params['catalog'] = $catid;
		$url = get_option('acalog_api_options')['api_url'].$path."?";
		
		foreach($params as $id => $param){
			$url .= "&".$id."=".$param;
		}
	
		$xml = simplexml_load_file($url);
		$xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
	
		$arts = $xml->xpath("//a:title");
		
		$i = 0;
		foreach($arts as $art){
			$hierarchy[$i] = strval($art);
			$i++;
		}
	}
	
	return $hierarchy;
}

function get_acalog_depts($catid){
	//build Acalog API URL and get output
	$path = "/v1/content";
	$params['key'] = get_option('acalog_api_options')['api_key'];
	$params['format'] = "xml";
	$params['method'] = "getItems";
	$params['catalog'] = $catid;
	$params['type'] = "hierarchy";
	
	$url = get_option('acalog_api_options')['api_url'].$path."?";
	
	foreach($params as $id => $param){
		$url .= "&".$id."=".$param;
	}
	
	$depts = get_acalog_hierarchy($catid);
	
	foreach($depts as $id=>$dept){
		if($id != 0){
			$this_url = $url;
			$this_url .= "&ids[]=".$id;
		
			$xml = simplexml_load_file($this_url);
			$xml->registerXPathNamespace('c', 'http://acalog.com/catalog/1.0');
			$xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
			$xml->registerXPathNamespace('h', 'http://www.w3.org/1999/xhtml');
		
			$dept_title = $xml->xpath('//c:entity/a:title');
			$dept_desc = $xml->xpath('//c:entity/a:content/*');
			
			foreach($dept_title as $this_title){
				$title = strval($this_title);
			}
			
			$desc = "";
			foreach($dept_desc as $this_desc){
				$elements = $this_desc->asXML();
				$desc .= str_replace("h:", "", $elements);
			}
		
			$departments[$id]['name'] = $title;
			$departments[$id]['desc'] = $desc;
		}
	}
	
	return $departments;
	
	//return $depts;
}

function save_acalog_depts($catid, $cat_type){
	$depts = get_acalog_depts($catid);
	
	foreach($depts as $id => $dept){
		$dept_post = array(
  			'post_title'    => $dept['name'],
			'post_content'  => '',
			'post_type'		=> 'acalog_department',
  			'post_status'   => 'publish',
  			'post_author'   => 1
		);
		
		$dept_id = wp_insert_post($dept_post, true);
	
		if(is_int($dept_id)){
			add_post_meta($dept_id, 'body', $dept['desc'], true);
			add_post_meta($dept_id, 'catalog_type', $cat_type, true);
			add_post_meta($dept_id, 'catoid', $catid, true);
			add_post_meta($dept_id, 'ent_oid', $id, true);
		}
	}
	//return "<pre>Departments saved.</pre>";
}

//Returns an array of all the course information of the courses owned by the specified college or department
function get_acalog_courses($catid){
	//build Acalog API URL and get output
	$path = "/v1/search/courses";
	$params['key'] = get_option('acalog_api_options')['api_key'];
	$params['format'] = "xml";
	$params['method'] = "search";
	$params['catalog'] = $catid;
	$params['query'] = '';
	
	$depts = get_acalog_hierarchy($catid);
	foreach($depts as $id=>$dept){
		if($id > 0){
			$params['query'] .= " ";
		}
		$params['query'] .= 'parent:"'.$dept.'"';
	}
	
	$params['options[limit]'] = "0";
	//$params['options[limit]'] = "1";
	$url = get_option('acalog_api_options')['api_url'].$path."?";
	
	foreach($params as $id => $param){
		$url .= "&".$id."=".$param;
	}
	
	$xml = simplexml_load_file($url);
	$xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
	$xml->registerXPathNamespace('c', 'http://acalog.com/catalog/1.0');

	$arts = $xml->xpath("//result");
	
	$i=0;

	foreach ($arts as $art) {

		$courseId = strval($art->id);
	
		$url2 = get_option('acalog_api_options')['api_url'] . "/v1/content?format=xml&key=".$params['key']."&method=getItems&type=courses&ids[]=".$courseId."&catalog=".$params['catalog'];
		
		$xml = file_get_contents($url2);
	
		$dom = new DOMDocument;
		$dom2 = new DOMDocument;

		// clean up output
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom2->preserveWhiteSpace = false;
		$dom2->formatOutput = true;

		// substitute xincludes
		$dom->loadXML($xml);
		$dom->xinclude();
		$xml2 = $dom->saveXML();
		$dom2->loadXML($xml2);
		$dom2->xinclude();
		
		//begin getting the course title
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_title.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($code_xsl);
		
		$courses[$i]['title'] = $proc->transformToXML($dom2);
		//end getting the course title
		
		//begin getting the course prefix
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_prefix.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($code_xsl);
		
		$courses[$i]['prefix'] = $proc->transformToXML($dom2);
		//end getting the course prefix

		//begin getting the course code
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_code.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($code_xsl);
		
		$courses[$i]['code'] = $proc->transformToXML($dom2);
		//end getting the course code
		
		//begin getting the course name
		$name_xsl = new DOMDocument;
		$name_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_name.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($name_xsl);
		
		$courses[$i]['name'] = $proc->transformToXML($dom2);
		//end getting the course name
		
		//begin getting the course credits
		$credits_xsl = new DOMDocument;
		$credits_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_credits.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($credits_xsl);
		
		$courses[$i]['credit_hours'] = $proc->transformToXML($dom2);
		//end getting the course credits
		
		//begin getting the course description
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_desc.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['desc'] = $proc->transformToXML($dom2);
		//end getting the course description
		
		//begin getting the course department
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_department.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['department'] = $proc->transformToXML($dom2);
		//end getting the course department
		
		//begin getting the course writing
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_writing.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['writing'] = $proc->transformToXML($dom2);
		//end getting the course writing
		
		//begin getting the course same as
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_same_as.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['same_as'] = $proc->transformToXML($dom2);
		//end getting the course same as
		
		//begin getting the course gen ed requirements
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_gen_ed.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['gen_ed'] = $proc->transformToXML($dom2);
		//end getting the course gen ed requirements
		
		//begin getting the course contact hour distribution
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_hour_dist.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['hour_dist'] = $proc->transformToXML($dom2);
		//end getting the course contact hour distribution
		
		//begin getting the course grading restriction
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_grade_rest.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['grade_rest'] = $proc->transformToXML($dom2);
		//end getting the course grading restriction
		
		//begin getting the course repeatability
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_repeat.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['repeat'] = $proc->transformToXML($dom2);
		//end getting the course repeatability
		
		//begin getting the course credit restriction
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_credit_rest.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['credit_rest'] = $proc->transformToXML($dom2);
		//end getting the course credit restriction
		
		//begin getting the course re prerequisites
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_re_pre.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['re_pre'] = $proc->transformToXML($dom2);
		//end getting the course re prerequisites
		
		//begin getting the course de prerequisites
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_de_pre.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['de_pre'] = $proc->transformToXML($dom2);
		//end getting the course de prerequisites
		
		//begin getting the course re corequisites
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_re_co.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['re_co'] = $proc->transformToXML($dom2);
		//end getting the course re corequisites
		
		//begin getting the course de corequisites
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_de_co.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['de_co'] = $proc->transformToXML($dom2);
		//end getting the course de corequisites
		
		//begin getting the course de pre and corequisites
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_pre_co.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['pre_co'] = $proc->transformToXML($dom2);
		//end getting the course de pre and corequisites
		
		//begin getting the course recommeneded background
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_rec_bg.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['rec_bg'] = $proc->transformToXML($dom2);
		//end getting the course recommeneded background
		
		//begin getting the course comments
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_comments.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['comments'] = $proc->transformToXML($dom2);
		//end getting the course comments
		
		//begin getting the course credit level restriction
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_credit_level_rest.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['credit_level_rest'] = $proc->transformToXML($dom2);
		//end getting the course credit level restriction
		
		//begin getting the course registration restriction
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_reg_rest.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['reg_rest'] = $proc->transformToXML($dom2);
		//end getting the course registration restriction
		
		//begin getting the course registration permission
		$desc_xsl = new DOMDocument;
		$desc_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_course_reg_perm.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'courseid', 'acalog-course-'.$courseId);
		$proc->importStylesheet($desc_xsl);
		
		$courses[$i]['reg_perm'] = $proc->transformToXML($dom2);
		//end getting the course registration permission
		
		$courses[$i]['catoid'] = $params['catalog'];
		$courses[$i]['coid'] = $courseId;	
		
		$i++;
		
	}
	
	return $courses;
	//return $arts;
}

//Saves all of the courses owned by the specified college or department as nodes of content type "course"
function save_acalog_courses($catid){
	$courses = get_acalog_courses($catid);
	
	foreach($courses as $course){
		$course_post = array(
  			'post_title'    => $course['title'],
			'post_content'  => '',
			'post_type'		=> 'acalog_course',
  			'post_status'   => 'publish',
  			'post_author'   => 1
		);
		
		$course_id = wp_insert_post($course_post, true);
	
		if(is_int($course_id)){
			add_post_meta($course_id, 'course_prefix', $course['prefix'], true);
			add_post_meta($course_id, 'course_code', $course['code'], true);
			add_post_meta($course_id, 'course_name', $course['name'], true);
			add_post_meta($course_id, 'course_credits', $course['credit_hours'], true);
			add_post_meta($course_id, 'course_description', $course['desc'], true);
			add_post_meta($course_id, 'course_department', $course['department'], true);
			add_post_meta($course_id, 'writing_emphasis', $course['writing'], true);
			add_post_meta($course_id, 'same_as', $course['same_as'], true);
			add_post_meta($course_id, 'general_education_requirement', $course['gen_ed'], true);
			add_post_meta($course_id, 'contact_hour_distribution', $course['hour_dist'], true);
			add_post_meta($course_id, 'grading_restriction', $course['grade_rest'], true);
			add_post_meta($course_id, 'repeatability', $course['repeat'], true);
			add_post_meta($course_id, 'credit_restriction', $course['credit_rest'], true);
			add_post_meta($course_id, 're_prerequisites', $course['re_pre'], true);
			add_post_meta($course_id, 'de_prerequisites', $course['de_pre'], true);
			add_post_meta($course_id, 're_corequisites', $course['re_co'], true);
			add_post_meta($course_id, 'de_corequisites', $course['de_co'], true);
			add_post_meta($course_id, 'de_pre_and_corequisites', $course['pre_co'], true);
			add_post_meta($course_id, 'recommended_background', $course['rec_bg'], true);
			add_post_meta($course_id, 'comments', $course['comments'], true);
			add_post_meta($course_id, 'credit_level_restriction', $course['credit_level_rest'], true);
			add_post_meta($course_id, 'registration_restriction', $course['reg_rest'], true);
			add_post_meta($course_id, 'registration_permission', $course['reg_perm'], true);
			add_post_meta($course_id, 'catoid', $course['catoid'], true);
			add_post_meta($course_id, 'coid', $course['coid'], true);
	
			//return($course_id);
		}
	}
	
	//return "Courses saved.";
}

function get_acalog_programs($catid){
	//build Acalog API URL and get output
	$path = "/v1/search/programs";
	$params['key'] = get_option('acalog_api_options')['api_key'];
	$params['format'] = "xml";
	$params['method'] = "search";
	$params['catalog'] = $catid;
	$params['query'] = '';
	
	$depts = get_acalog_hierarchy($catid);
	foreach($depts as $id=>$dept){
		if($id > 0){
			$params['query'] .= " ";
		}
		$params['query'] .= 'parent:"'.$dept.'"';
	}
	
	$params['options[limit]'] = "0";
	//$params['options[limit]'] = "1";
	$url = get_option('acalog_api_options')['api_url'] . $path."?";
	
	foreach($params as $id => $param){
		$url .= "&".$id."=".$param;
	}
	
	$xml = simplexml_load_file($url);
	$xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
	$xml->registerXPathNamespace('c', 'http://acalog.com/catalog/1.0');

	$arts = $xml->xpath("//result");
	
	$i=0;
	
	foreach ($arts as $art) {

		$progId = strval($art->id);
		//$progId = "12086";
	
		$url2 = get_option('acalog_api_options')['api_url'] . "/v1/content?format=xml&key=".$params['key']."&method=getItems&type=programs&ids[]=".$progId."&catalog=".$params['catalog'];
		
		$xml = file_get_contents($url2);
	
		$dom = new DOMDocument;
		$dom2 = new DOMDocument;

		// clean up output
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom2->preserveWhiteSpace = false;
		$dom2->formatOutput = true;

		// substitute xincludes
		$dom->loadXML($xml);
		$dom->xinclude();
		$xml2 = $dom->saveXML();
		$dom2->loadXML($xml2);
		$dom2->xinclude();
		
		//begin getting the program title
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_title.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['title'] = $proc->transformToXML($dom2);
		//end getting the program title
		
		//begin getting the program department
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_department.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['department'] = $proc->transformToXML($dom2);
		//end getting the program department
		
		//begin getting the program type
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_type.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['prog_type'] = $proc->transformToXML($dom2);
		//end getting the program type
		
		//begin getting the degree type
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_degree_type.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['degree_type'] = $proc->transformToXML($dom2);
		//end getting the degree type
		
		//begin getting the program content
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_content.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->setParameter('', 'catoid', $params['catalog']);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['content'] = $proc->transformToXML($dom2);
		//end getting the program content
		
		//begin getting the program core
		$code_xsl = new DOMDocument;
		$code_xsl->loadXML(file_get_contents(plugin_dir_path(__FILE__).'/xsl/acalog_program_core_all.xsl'));
		
		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->setParameter('', 'progid', 'acalog-program-'.$progId);
		$proc->setParameter('', 'catoid', $params['catalog']);
		$proc->importStylesheet($code_xsl);
		
		$programs[$i]['core'] = $proc->transformToXML($dom2);
		//end getting the program core
		
		$programs[$i]['catoid'] = $params['catalog'];
		$programs[$i]['poid'] = $progId;	
		
		$i++;
	}
	
	return $programs;
	//return $arts;

}

//transforms perma-links into Acalog URLs
function acalog_get_url($id, $to, $catid){
	$url = "";
	if(strpos($to, "acalog-media") !== false){
		$id = str_replace("acalog-permalink-", "", $id);
		$url = get_option('acalog_api_options')['catalog_url']."/mime/media/".$catid."/".$id."/";
	}elseif(strpos($to, "acalog-entity") !== false){
		$to = str_replace("acalog-entity-", "", $to);
		$url = get_option('acalog_api_options')['catalog_url']."/preview_entity.php?catoid=".$catid."&ent_oid=".$to;
	}elseif(strpos($to, "acalog-page") !== false){
		$to = str_replace("acalog-page-", "", $to);
		$url = get_option('acalog_api_options')['catalog_url']."/content.php?catoid=".$catid."&navoid=".$to;
	}elseif(strpos($to, "acalog-program") !== false){
		$to = str_replace("acalog-program-", "", $to);
		$url = get_option('acalog_api_options')['catalog_url']."/preview_program.php?catoid=".$catid."&poid=".$to;
	}elseif(strpos($to, "acalog-course") !== false){
		$to = str_replace("acalog-course-", "", $to);
		$url = get_option('acalog_api_options')['catalog_url']."/preview_course_nopop.php?catoid=".$catid."&coid=".$to;
	}
	return $url;
}

//Saves all of the programs owned by the specified college or department as nodes of content type "acalog_program"
function save_acalog_programs($catid){
	$programs = get_acalog_programs($catid);
	
	foreach($programs as $program){
		$prog_post = array(
  			'post_title'    => $program['title'],
			'post_content'  => '',
			'post_type'		=> 'acalog_program',
  			'post_status'   => 'publish',
  			'post_author'   => 1
		);
		
		$prog_id = wp_insert_post($prog_post, true);
	
		if(is_int($prog_id)){
			add_post_meta($prog_id, 'course_department', $program['department'], true);
			add_post_meta($prog_id, 'program_type', $program['prog_type'], true);
			add_post_meta($prog_id, 'degree_type', $program['degree_type'], true);
			add_post_meta($prog_id, 'program_content', $program['content'], true);
			add_post_meta($prog_id, 'core', $program['core'], true);
			add_post_meta($prog_id, 'catoid', $program['catoid'], true);
			add_post_meta($prog_id, 'poid', $program['poid'], true);
		}
	}
	
	//return "Programs saved.";
}

function acalog_update_all(){
	
	if(get_option('acalog_api_options')['api_key'] != "" && get_option('acalog_api_options')['api_url'] != "" && get_option('acalog_api_options')['catalog_url'] != ""){
		if(get_option('acalog_api_options')['catalog_type'] != ""){
			$cat_types[0] = get_option('acalog_api_options')['catalog_type'];
		}else{
			$cat_types = array("Undergraduate", "Graduate");
		}
	
		//clear_acalog_data("acalog_course");
		clear_acalog_data("acalog_program");
		//clear_acalog_data("acalog_department");
		
		foreach($cat_types as $cat_type){
			$catid = strval(get_acalog_catalog($cat_type));
		
			//save_acalog_courses($catid);
			save_acalog_programs($catid);
			//save_acalog_depts($catid, $cat_type);
		}
		return $cat_types;
	}
}

add_filter( 'cron_schedules', 'acalog_monthly_cron_interval' );
function acalog_monthly_cron_interval( $schedules ) { 
    $schedules['thirty_days'] = array(
        'interval' => 60 * 60 * 24 * 30,
        'display'  => esc_html__( 'Once every thirty days' ), );
    return $schedules;
}

function acalog_api_activate(){
	if(!wp_next_scheduled('acalog_api_cron_hook')){
		wp_schedule_event(time(), 'thirty_days', 'acalog_api_cron_hook');
	}
}

function acalog_api_deactivate(){
    wp_clear_scheduled_hook('acalog_api_cron_hook');
}

register_activation_hook(__FILE__, 'acalog_api_activate');
add_action('acalog_api_cron_hook', 'acalog_update_all');

register_deactivation_hook( __FILE__, 'acalog_api_deactivate' ); 

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function acalog_api_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'acalog-api-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', __FILE__ ), // Block style CSS.
		is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'acalog-api-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', __FILE__ ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'acalog-api-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', __FILE__ ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
	wp_localize_script(
		'acalog-api-block-js',
		'cgbGlobal', // Array containing dynamic data for a JS Global.
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
			// Add more data here that you want to access from `cgbGlobal` object.
		]
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'utk/acalog-api', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'acalog-api-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'acalog-api-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'acalog-api-block-editor-css',
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'acalog_api_assets' );