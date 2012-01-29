<?php
/**
 * Plugin Name: Diy Plugin Framework
 * Plugin URI: http://github.com/onemanonelaptop/diy
 * Description: A Diy Plugin Framework for creating plugins
 * Version: 0.0.1
 * Author: Rob Holmes
 * Author URI: http://github.com/onemanonelaptop
 */

/* Copyright 2011 Rob Holmes ( email: rob@onemanonelaptop.com )

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Ensure the class is available to all dependant plugins by using plugins_loaded
add_action( 'plugins_loaded', 'diy_init' );
/**
* Runs on plugins_loaded action and defines the Diy Class 
*   
* @since    0.1
* @access   public
* @return   void
*/
function diy_init() {

    // If the class has already been defined then dont define it again
    if (!class_exists('Diy')) {
            /**
            * The Abstract Diy Class
            *   
            * @since	0.1
            * @access	public
            */
            class Diy {

                    /**
                    * @var array Store the defined post meta boxes e.g. $meta[post-type][metabox-id][field-id][group-instance][group-index]
                    */
                    protected $meta = array();

                    /**
                    * @var string The Title that is displayed on the options page
                    */
                    protected $settings_page_title = "Options Page";

                    /**
                    * @var string The menu anchor link text for the options page
                    */
                    protected $settings_page_link = "Options";

                    /**
                    * @var string The slug of the plugin
                    */
                    protected  $slug = "";

                    /**
                    * @var string The option group name
                    */
                    protected $options_group = '';

                    /**
                    * @var array The options page defaults
                    */
                    protected $defaults = array ();
                   
                    /**
                    * @var string The name of the file e.g. my-plugin/my-plugin.php
                    */
                    protected $filename = '';

                    /**
                    * @var array Define some default key/value pairs to be used by the 'select' field widget
                    */
                    protected $yesno = array ('1'=>'Yes','0'=>'No');
 
                    /**
                    * @var array Define the image extension for attachment field widget previews
                    */
                    protected $image_extensions = array('png','gif','jpg','ico');

                    /**
                    * @var string How is the diy being used i.e. 'plugin','theme','postbox'
                    */
                    protected $usage = 'plugin';

                    /**
                    * @var array Set to true to remove sidebar metaboxes on the options page
                    */
                    protected $is_generic = false;
                    
                    /**
                    * This starts the process of defining the plugin
                    * @return void
                    */
                    public function start() {

                        // If we havent got a user defined slug then exit
                        if ($this->slug == '') {
                                return;
                        }

                        // generate the options_group var name
                        if ($this->options_group == '') {
                                $this->options_group = $this->slug;
                        }

                        // full file and path to the plugin file
                        $this->plugin_file =  WP_PLUGIN_DIR .'/'.$this->filename ;

                        // store the path to the child plugin
                        $this->plugin_path = WP_PLUGIN_DIR.'/'.str_replace(basename( $this->filename),"",plugin_basename($this->filename));

                        // store the url to the child plugin
                        $this->plugin_url = plugin_dir_url( $this->plugin_file );

                        // paths to the diy plugin
                        $this->diy_file = __FILE__;
                        $this->diy_path = str_replace(basename( $this->diy_file),"",$this->diy_file);
                        $this->diy_url = str_replace(ABSPATH,trailingslashit(get_option( 'siteurl' )),$this->diy_path);

                        // Register the child plugins fields
                        add_action('admin_init', array($this,'diy_fields')); 
                        
                        // Register the child plugins metaboxes
                        add_action('admin_init', array($this,'diy_metaboxes'));

                        // Save the custom post fields with the post data
                        add_action('save_post', array(&$this,'diy_save_post')); 

                        // Register the scripts and styles needed for metaboxes and fields
                        add_action('admin_init', array(&$this,'diy_scripts_and_styles') );	

                        // Add the plugins options page	unless the Diy Class is being used just for metaboxes		
                        if ($this->usage != 'meta') {
                                // Add the plugins options page
                                add_action( 'admin_menu', array($this,'diy_add_options_page') );
                        }
                        
                        // Force the plugin options page to have two columns
                        add_filter('screen_layout_columns', array(&$this, 'diy_options_page_columns'), 10, 2);

                        // Add the predefined metaboxes to the plugin options page as long as is_generic isnt true
                        if ($this->is_generic == false) {
                                add_action('admin_init', array(&$this,'diy_add_predefined_metaboxes') ); 
                        }

                        // Setup the ajax callback for autocomplete widget
                        add_action('wp_ajax_suggest_action', array(&$this,'diy_suggest_posts_callback'));	
                        // add_action('wp_ajax_suggest_action', array(&$this,'diy_suggest_users_callback'));	

                        // Setup some query vars to serve javascript and css via a url
                        add_action( 'template_redirect', array( &$this, 'diy_script_server' ));
                        add_filter( 'query_vars', array( &$this, 'diy_query_vars' ));
                                         
                    } // end function 
                    
                    
                     /**
                    * Return a link to the admin icon
                    * @param string $hook Current page hook
                    * @return string
                    */
                    function diy_settings_page_icon( $hook ) {
			if ($hook == $this->hook) 
				return plugin_dir_url( __FILE__ ).$this->icon;
			return $hook;
                    }
		

                    /**
                    * For each filed defined by the child plugin add it to the appropriate options page/post metabox
                    * 
                    * @return void
                    */
                    function diy_fields() {
                        // If there are no fields defined then just leave
                        if (!is_array($this->fields)) {
                            return;
                        }
                        
                        // Go through all the defined fields
                        foreach($this->fields as $field) {
                            
                            // get the metabox post_types the field is attached to
                            $metabox_post_types = $this->diy_get_metabox_post_types($field['metabox']);

                            // If a post type is set then add the new field to the appropriate metabox.
                            if ($metabox_post_types!="") {
                                // If its not an array, then make it one
                                if (!is_array($metabox_post_types)) {
                                    $metabox_post_types = array($metabox_post_types);
                                }
                                // Add the metabox to each of the post types
                                foreach ($metabox_post_types as $type) {
                                    $this->meta[ $type ][ $field['metabox'] ][ $field['group'] ] = $field;
                                }
                            } else {  
                                add_settings_field(
                                    $field['group'],  // option identifier
                                    $field['title'], // field title
                                    array(&$this, 'settings_field_callback'), // field widget callback
                                    $this->page , // page hook
                                    $field['metabox'], // assigned metabox
                                    $field	// arguments to pass in
                                );

                                register_setting( $this->options_group, $field['group'], array(&$this,'diy_validate_settings'));

                                $this->options_meta[ $field['metabox'] ][ $field['group'] ] = $args;

                                // check if this option has previously been created if not apply the defaults
                                if (! get_option( $field['group'] ) ) {
                                    foreach ($field['fields'] as $key => $values) {
                                            // as long as we are not dealing with an effing checbox
                                            if (!(($values['type'] == 'checkbox') && ($values['default'] != 0))) {
                                                    $build[0][$key] = $values['default'];
                                            }
                                    }
                                    // Update the options table with the defaults
                                    update_option($field['group'],$build);
                                } // end if
                            } // end if
                        } // end foreach
                    } // end function

                    /**
                    * Validate callback when saving a plugins options
                    * 
                    * @param    array   $data   The form post data
                    * @return   array   The validated data  
                    */
                    function diy_validate_settings($data) {
                        // Convert the suggest data [# ] to a post ID
                        $data = $this->suggest_to_id($data); 
                        return $data;
                    }

                    /**
                    * For a given metabox return the post_types is is registered to
                    * 
                    * @param    string   $id   The id of the metabox
                    * @return   array   An array of post_types  
                    */
                    function diy_get_metabox_post_types($id) {
                        if (!is_array($this->metaboxes)) {
                            return '';
                        }
                        foreach ($this->metaboxes as $metabox) {
                            if ($metabox['id'] == $id) {
                                return $metabox['post_type'];
                            }
                        }
                        return '';
                    }

                    /**
                    * Loop throught the defined metaboxes and create them as necessary
                    * 
                    * @return   void   
                    */
                    function diy_metaboxes() {
                        if (!is_array($this->metaboxes)) {
                            return;
                        }
                        foreach ($this->metaboxes as $metabox) {

                            if ($metabox['post_type'] != '') {
                                // If a post type is set then add the metabox to the post type
                                if (!is_array($metabox['post_type'])) {
                                    $metabox['post_type'] = array($metabox['post_type']);
                                }

                                foreach ($metabox['post_type'] as $metabox_post_type) {

                                    add_meta_box( 
                                                $metabox['id'], 
                                                $metabox['title'],  
                                                array(&$this,'post_metabox_builder'), 
                                                $metabox_post_type, 
                                                'normal', 
                                                'high', 
                                                $this->meta[$metabox_post_type][$metabox['id']] 
                                    );
                                }     
                            } else { 
                                    // otherwise add this metabox to an options page.

                                    add_settings_section(
                                            $metabox['id'], 
                                            '', 
                                            array(&$this, 'section_null'), 
                                            $this->page 
                                    );
                                    add_meta_box(
                                            $metabox['id'],
                                            $metabox['title'], 
                                            array(&$this, 'diy_option_field_builder'), 
                                            $this->page, 
                                            'normal', 
                                            'core',
                                            array('section' => $metabox['id'],'description'=>$metabox['description'])
                                    );

                            } // end if
                        } // end foreach
                    } // end function

                    /**
                    * Vogon constructor
                    *
                    * @since	0.1
                    * @param 	string  $file 	Contains __FILE__ for the file extending this class
                    * @access	public
                    * @return   void
                    */
                    function __construct($file = __FILE__) {
                        // Save the filename of the child plugin
                        $this->filename = plugin_basename($file);
                        // Initialise the plugin if the method has been defined in the extended class
			if ( is_callable( array($this, 'setup') ) ) {
                            $this->setup();
			}    
                    } // function


                    /**
                    *  Serve the CSS or JS when requested via a URL
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    public function diy_script_server() {
                            // Check that the query var is set and is the correct value.
                            if (get_query_var( 'diy' ) == 'css') {
                                    // Send the headers for a CSS file
                                    header("Content-type: text/css");
                                    // output css 
                                    print $this->diy_css();
                                    exit;
                            }

                            // Check that the query var is set and is the correct value.
                            if (get_query_var( 'diy' ) == 'js') {
                                    // Send the headers for a javascript file
                                    header("Content-type: application/x-javascript");
                                    // output js 
                                    print $this->diy_js();
                                    exit;
                            }
                    } // function 


                    /**
                    *  Setup the query variable used to serve js and css data
                    *
                    * @since	0.1
                    * @param	array   $public_query_vars	An array of the currently registered query var names
                    * @return	array   Query var names array
                    * @access	public
                    */
                    public function diy_query_vars($public_query_vars) {
                            $public_query_vars[] = 'diy';
                            return $public_query_vars;
                    } // function

                    /**
                    * When the plugin is activated update the settings
                    *
                    * @since	0.1
                    * @access	public
                    * @todo		not currently called anywhere
                    * @return   void
                    */
                    public function diy_activate() {
                            update_option( $this->options_name, $this->options);
                    } // function

                    /**
                    * When the plugin is deactivated update the settings
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    public function plugin_deactivate() {

                    } // function

                    /**
                    * Create the Options page for the plugin
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    public function diy_add_options_page() {
                            // Add a theme page or an option page depending on the diy usage
                            if ($this->usage == 'theme') {
                                    $this->page = add_theme_page( __($this->settings_page_title), __($this->settings_page_link), 'edit_theme_options', $this->slug, array(&$this,'diy_render_options_page' ));
                                    add_action('load-'.$this->page,  array(&$this, 'diy_loading_options_page'));	
                            } else if ($this->usage == 'plugin') {
                                    $this->page = add_options_page(__($this->settings_page_title), __($this->settings_page_link), 'manage_options', $this->slug, array($this, 'diy_render_options_page'));
                                    add_filter( 'plugin_action_links', array(&$this, 'diy_add_settings_link'), 10, 2 );

                                    // Run stuff as and when this options page loads
                                    add_action('load-'.$this->page,  array(&$this, 'diy_loading_options_page'));
                            }
                    } // function

                    /**
                    * Runs only on the plugin page load hook, enables the scripts needed for metaboxes
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_loading_options_page() {
                        wp_enqueue_script('common');
                        wp_enqueue_script('wp-lists');
                        wp_enqueue_script('postbox');
                    } // function

                    /**
                    * Add a settings link to the plugin list page
                    *
                    * @since	0.1
                    * @param	string  $file       the filename of the plugin currently being rendered on the installed plugins page
                    * @param	array   $links      an array of the current registered links in html format
                    * @return	array
                    * @access	public
                    */
                    function diy_add_settings_link($links, $file) {
                        // if the current row being rendered matches our plugin then add a settings link
                        if ( $file == $this->filename  ){
                            // Build the html for the link
                            $settings_link = '<a href="options-general.php?page=' .$this->slug . '">' . __('Settings', $this->slug) . '</a>';
                            // Prepend our link to the beginning of the links array
                            array_unshift( $links, $settings_link );
                        }
                        return $links;
                    } // function

                    /**
                    * On the plugin page make sure there are two columns
                    *
                    * @since	0.1
                    * @access	public
                    * @param   int $columns
                    * @param   string  $screen
                    * @return  int number of columns
                    */
                    function diy_options_page_columns($columns, $screen) {
                        if ($screen == $this->page) {
                            $columns[$this->page] = 2;
                            update_user_option(true, "screen_layout_$this->page", "2" );
                        }
                        return $columns;
                    } // function

                    /**
                    * Create the options page form
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    public function diy_render_options_page() {
                        global $screen_layout_columns;
                        $data = array();
                        ?>
                        <div class="wrap">
                            <?php screen_icon('options-general'); ?>
                            <h2><?php print $this->settings_page_title; ?></h2>
                            <form id="settings" action="options.php" method="post" enctype="multipart/form-data">

                                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
                                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
                                <?php settings_fields($this->options_group); ?>
                                <div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
                                    <div id="side-info-column" class="inner-sidebar">
                                            <?php do_meta_boxes($this->page, 'side', $data); ?>
                                    </div>
                                    <div id="post-body" class="has-sidebar">
                                        <div id="post-body-content" class="has-sidebar-content">
                                            <?php do_meta_boxes($this->page, 'normal', $data); ?>
                                            <br/>
                                            <p>
                                                <input type="submit" value="Save Changes" class="button-primary" name="Submit"/>	
                                            </p>
                                        </div>
                                    </div>
                                    <br class="clear"/>				
                                </div>	
                            </form>
                        </div>
                        <script type="text/javascript">
                                //<![CDATA[
                                jQuery(document).ready( function($) {
                                        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');

                                        postboxes.add_postbox_toggles('<?php echo $this->page; ?>');
                                });
                                //]]>
                        </script>
                        <?php
                    } // function



                    /**
                    * Register some default metaboxes on the plugins options page
                    *
                    * @since	0.1
                    * @access	public
                    * @todo 	This function should use optbox() to define its metaboxes
                    * @return   void
                    */	
                    function diy_add_predefined_metaboxes() {
                        // Support metabox
                        add_settings_section('admin-section-support', '', array(&$this, 'section_null'), $this->page );
                        // Bug report metabox
                        add_settings_section('admin-section-bugs', '', array(&$this, 'section_null'), $this->page );
                        //  Define the sidebar meta boxes
                        if ($this->usage != 'theme') {
                            add_meta_box('admin-section-support','Support', array(&$this, 'diy_render_support_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-support'));
                        }

                        add_meta_box('admin-section-bugs','Found a bug?', array(&$this, 'diy_render_bugs_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-bugs'));
                        add_meta_box('admin-section-connect','Get Connected', array(&$this, 'diy_render_connect_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-connect'));

                        if ($this->usage != 'theme') {
                            add_meta_box('admin-section-like','Did you like this plugin?', array(&$this, 'diy_render_rating_metabox'), $this->page, 'side', 'core',array('section' => 'admin-section-like'));
                        }
                    } // function


                    /**
                    * Meta box for the documention link and the debugging popup 
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_render_support_metabox() {
                        print "<ul id='admin-section-support-wrap'>";
                        print "<li><a id='diy-support' href='https://github.com/OneManOneLaptop/" . $this->slug . "/wiki/" . $this->slug . "' target='_blank' style=''>Plugin Documentation</a></li>";
                        print '<li><a title="Plugin Debug Information" href="#TB_inline?width=640&inlineId=debuginfo" class="thickbox">Debug Information</a></li>';
                        print "</ul>"; 
                        print '<div id="debuginfo" style="display:none;"><p><b>diy Version:</b><br/>' . $this->version. '</p><p><b>Options Array:</b><br/>' . var_export($this->options,true) . '</p></div>';
                    } // function


                    /**
                    * Meta box for the bug reporting info
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_render_bugs_metabox() {
                        print "<ul id='admin-section-bug-wrap'>";
                        print "<li><p>If you have found a bug in this " . ($this->usage=='theme' ? 'theme' : 'plugin' ) . ", please open a new <a id='diy-bug' href='https://github.com/OneManOneLaptop/" . $this->slug . "/issues/' target='_blank' style=''>Github Issue</a>.</p><p>Please describe the problem clearly and where possible include a reduced test case.</p></li>";
                        print "</ul>"; 
                    } // function

                    /**
                    * Meta box for displaying social media links
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_render_connect_metabox() {
                        print "<ul id='admin-section-bug-wrap'>";
                        print "<li class='icon-twitter'><a href='http://twitter.com/onemanonelaptop'>Follow me on Twitter</a></li>";
                        print "<li class='icon-linkedin'><a href='http://www.linkedin.com/pub/rob-holmes/26/3a/594'>Connect Via LinkedIn</a></li>";
                        print "<li  class='icon-wordpress'><a href='http://profiles.wordpress.org/users/OneManOneLaptop/'>View My Wordpress Profile</a></li>";
                        print "</ul>"; 
                    } // function

                    /**
                    * Meta box for displaying plugin rating links
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_render_rating_metabox() {
                        print "<ul id='admin-section-like-wrap'>";
                        print "<li><a href='http://onemanonelaptop.com/docs/" . $this->slug . "/'>Link to it so others can find out about it.</a></li>";
                        print "<li><a href='http://wordpress.org/extend/plugins/" . $this->slug . "/'>Give it a 5 star rating on WordPress.org.</a></li>";
                        print "<li><a href='http://www.facebook.com/sharer.php?u=" . urlencode("http://wordpress.org/extend/plugins/" . $this->slug . "/") . "&t=" . urlencode($this->settings_page_link) . "'>Share it on Facebook</a></li>";
                        print "</ul>"; 
                    } // function

                    /**
                    * Register the admin scripts
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_scripts_and_styles() {
                            wp_enqueue_script( 'jquery' );
                            wp_enqueue_script( 'jquery-ui-core' );
                            wp_enqueue_script( 'jquery-ui-datepicker' );

                            // Register our dynamic css and js files
                            wp_register_style('diy', home_url() .'?diy=css');
                            wp_register_script('diy',  home_url() .'?diy=js', array('jquery','media-upload','thickbox','editor'));

                            wp_register_script('gmap','http://maps.google.com/maps/api/js?sensor=false');

                            // Add custom scripts and styles to the plugin/theme page only
                            add_action('admin_print_scripts-' . $this->page, array(&$this, 'diy_admin_scripts'));
                            add_action('admin_print_styles-' . $this->page, array(&$this,  'diy_admin_styles'));

                            // Add custom scripts and styles to the post editor pages
                            add_action('admin_print_scripts-post.php', array(&$this, 'diy_admin_scripts'));
                            add_action('admin_print_scripts-post-new.php',array(&$this,  'diy_admin_scripts'));
                            add_action('admin_print_styles-post.php', array(&$this, 'diy_admin_styles'));
                            add_action('admin_print_styles-post-new.php',array(&$this,  'diy_admin_styles'));	

                    } // function

                    /**
                    * Add custom styles to this plugins options page only
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_admin_styles() {

                            // used by media upload
                            wp_enqueue_style('thickbox');
                            // Enqueue our diy specific css
                            wp_enqueue_style('diy');
                            // color picker
                            wp_enqueue_style( 'farbtastic' );
                    } // function


                    /**
                    * Add scripts globally to all post.php and post-new.php admin screens
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */
                    function diy_admin_scripts() {
                            // Enqueue our diy specific javascript
                            wp_enqueue_script('diy');

                            // Color picker
                            wp_enqueue_script('farbtastic');  

                            // Allow Jquery Chosen
                            wp_enqueue_script('suggest');

                            // Allow usage of the google map api
                            wp_enqueue_script('gmap');
                    }

                    /**
                    * Define a metabox field and add it to a metabox 
                    *
                    * @param	mixed $args	array that contains the metabox field definition
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */	
                    function field($args) {
                        $this->fields[] = $args;
                    } // end function


                    /**
                    * Add a meta box to a post type or an options page.
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $args
                    * @return   void
                    */	
                    function metabox( $args) {
                        $this->metaboxes[] = $args;
                    } // end function

                    /**
                    *  If a height is specified return the inline style to set it
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $height  the height in pixels
                    * @return   string  
                    */
                    function height($height) {
                            return ((!empty($height)) ? ' height:'. $height . 'px;' : '');
                    } // function

                    /**
                    * If a width is specified return the inline style to set it
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $width  The width in pixels
                    * @return   string 
                    */
                    function width($width) {
                            return  ((!empty($width)) ? ' width:'. $width . 'px;' : '');
                    } // function

                    /**
                    * If a description is given then return the html to display it
                    *
                    * @since	0.1
                    * @param	string $d	The text to show for the description
                    * @access	public
                    * @return   void
                    */
                    function description($d) {
                            return ( (!empty($d)) ? '<br />' . '<span class="description">'.$d. '</span>' : '');
                    } // function

                    /**
                    * If any placeholder text is specified then add the html attribute to the element
                    *
                    * @since	0.1
                    * @param	string 	$p 	The text to use for the placeholder
                    * @access	public
                    * @return   void
                    */
                    function placeholder($p) {
                            return ( (!empty($p)) ? 'placeholder="' . $p . '"' : '');
                    } // function

                    /**
                    * If any suffix text is specified then add the html right after the field
                    *
                    * @since	0.1
                    * @param	string 	$s 	The text to use for the suffix
                    * @access	public
                    * @return   void
                    */
                    function suffix($s) {
                            return ( (!empty($s)) ? '<span class="field-suffix">' . $s . '</span>' : '');
                    } // function

                    /**
                    * Build a text input field widget
                    *
                    * @since	0.1
                    * @param	array 	$args 	The width, name, value, placeholder and description of the text field
                    * @access	public
                    * @return   void
                    */
                    function text($args) {
                            // $args = $this->apply_name_fix($this->apply_default_args($args)) ;
                            echo "<input class='field' type='text' size='57'  style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['name'] . "' value='" . $args['value']  . "'/>" . $this->suffix($args['suffix']);					
                            echo $this->description($args['description']);
                    } // function

                    /**
                    * Build a datepicker field widget
                    *
                    * @since	0.1
                    * @param	array 	$args 	The width, name, value, placeholder and description of the date field
                    * @access	public
                    * @return   void
                    */
                    function date($args) {
                            // $args = $this->apply_name_fix($this->apply_default_args($args)) ;
                            echo "<input class='field datepicker' type='text' size='57'  style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['name'] . "' value='" . $args['value']  . "'/>";					
                            echo $this->description($args['description']);
                    } // function


                    /**
                    * Build a textarea field widget
                    *
                    * @since	0.1
                    * @param	array 	$args 	The width, name, value, placeholder and description of the textarea field
                    * @access	public
                    * @return   void
                    */
                    function textarea($args) {
                            echo "<textarea class='field' data-tooltip='" .$args['tooltip']  . "' name='" . $args['name']  . "' style='" . $this->width($args['width']) . " " .  $this->height($args['height']) . "' rows='7' cols='50' type='textarea'>" . $args['value'] . "</textarea>";			
                            echo $this->description($args['description']);
                    } // function

                    /**
                    * Build a checkbox field widget
                    *
                    * @since	0.1
                    * @param	array 	$args 	The width, name, value, placeholder and description of the checkbox field
                    * @access	public
                    * @return   void
                    */ 
                    function checkbox($args) {
                            echo "<input  class='field' name='" . $args['name'] . "' type='checkbox' value='1' ";
                            checked('1', $args['value']); 
                            echo " /> <span  class='description'>" . $args['description'] . "</span>" ;

                    } // function


                    /**
                    * Build a selectbox field widget
                    *
                    * @since	0.1
                    * @param	array 	$args 	The width, name, value, placeholder and description of the text field
                    * @return   void
                    * @access	public
                    */ 
                    function select($args)  {

                            if ($args['multiple']) {
                                    echo "<select class='optselect field'  multiple='true' style='" .$this->width($args['width'])  . "' name='" . $args['name'] . "" . "[]'>";
                                    foreach ($args['selections'] as $key => $value) {
                                            echo "<option " . (array_search($value , $args['value']) === false ? '' : 'selected' ). " value='" . $key . "'>" . $value . "</option>";	
                                    }	
                                    echo "</select>";
                            } else {
                                    echo "<select  class='optselect field'  style='" .$this->width($args['width'])  . "' name='" . $args['name'] . "'>";
                                    foreach ($args['selections'] as $key => $value) {
                                            echo "<option " . ($args['value'] == $key ? 'selected' : '' ). " value='" . $key . "'>" . $value . "</option>";	
                                    }	
                                    echo "</select>";
                            }
                            echo $this->description($args['description']);
                    } // function

                    /**
                    * Render a google map
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $args
                    * @return   void
                    */ 
                    function map ($args) {
                            global $post;
                            // build the html map element
                            echo '<div id="map-' . $args['name'] . '" class="gmap field" data-zoom="5" data-lat="" data-long="" data-latfield="' . $args['latfield'] . '" data-longfield="' . $args['longfield'] . '" style="' .$this->height($args['height'])  . '" ></div>';
                    } // end function map


                    /**
                    * Render a color picker field widget
                    *
                    * @since	0.1
                    * @param    array   $args
                    * @access	public
                    * @return   void
                    */ 
                    function color($args) {
                            echo "<div class='inline-rel'>";
                            echo "<span style='background:" . (!empty($args['value']) ? $args['value'] : "url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAIAAAAC64paAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzIwM0UzNzZEODc2MTFFMDgyM0RFQUJEOEU1NEI2NjkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzIwM0UzNzdEODc2MTFFMDgyM0RFQUJEOEU1NEI2NjkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDMjAzRTM3NEQ4NzYxMUUwODIzREVBQkQ4RTU0QjY2OSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDMjAzRTM3NUQ4NzYxMUUwODIzREVBQkQ4RTU0QjY2OSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Ps3q5KgAAAKOSURBVHjaXJRZTypBEIWZYVPgKsgeSAgQCUvgBeP//wGQyBaBRCFACKIgO7L7zdS94439MFTXqa5zqroapVqtXi6XdDpts9leXl4+Pz8jkUg4HN7tds/Pz4qiZLNZq9Xa6/XG47HX643H4wJZWIfDwWQyEcT3dDqxPZ/PJn0dj0dFX9g4f0FQKsvlEtf7+/t+vw8EAna7Hc9sNsPw+/3EQcixu7u76+vrr6+vj48PgUiqulyum5ubxWIxmUyurq7Y4sVerVZ/9DWfz9miEZtjBqRFkhgB0KIZTFVVDLZms5kuwGxAJCWSggVia+l2u0QUi0WONZtN9CcSiVgshtRyuUzE4+Mj306nMxgMQqHQ/f29QFrD0Ew+lJCP9G63m9D1ek1Lbm9vsYHISyQQhAZEvKYE5kqlgrdQKFDJaDR6fX2lqnw+D/T09ESfUqkUPaP+RqNhQBbqodskhvakL7zYeLBJjQEhMRJpQNoF1+t1IqhTJoHcwWCQO6Mx1ElEMpkEGg6H0+kU5dFoVCBkW7bbrVCxoRObzYYt0WTEplrujy+c1IVgA4Jf4dJlA8wY0CEkyX2wJZFApMADRP0CaUPCuPp8PlKgmcQIxouNSJ++uLx+vy9T5XA4DIiDP8xcgNPpRCEGtaCKrUAQQgWhiBdIGxJuhYiHhweO8VbgoUP0jxSlUun/IYGf18aQCPQzJOQjMYVxmVInzQOSITHry+Px0C0D+jskiOHqkZrJZCibIaEwhOVyOdBarUaTkEORvLZ2uy0QHKo8Zklh+rewZfIEEvsXpKGtVosfBgMZNA9VTAKqKOzt7Q2IOmkH/zC8czjhFwiniloO4GWq8RIBGzbt3ehLIAiBaLsBcfBbgAEArCsu6B0YK4AAAAAASUVORK5CYII=);")  ."' class='swatch'></span><input id='".$args['name']."' class=\"picker field\" type='text' data-tooltip='" .$args['tooltip']  . "' size='57'" . $this->placeholder('None') . "' name='" . $args['name'] . "' value='" . $args['value']  . "'/>";				
                            echo "<div  id='" . $args['name']  . "_picker' class='picker' style=''></div>";
                            echo $this->description($args['description']); // print a description if there is one
                            echo "</div>";
                    } // function

                    /**
                    * Retrieve the ID number of an image/file asset
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $image_src
                    * @return   int 
                    */ 	
                    function get_attachment_id ($image_src) {
                            global $wpdb;
                            $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
                            $id = $wpdb->get_var($query);
                            return $id;
                    }


                    /**
                    * Render an attachment field widget
                    *
                    * @since	0.1
                    * @param    array   $args
                    * @access	public
                    * @return   void
                    */ 
                    function attachment($args) {
                            // $args = $this->apply_name_fix($this->apply_default_args($args)) ;
                            echo "<div><input class='attachment field' id='" . $args['id'] . "' style='" .$this->width($args['width']) . "'  type='text' size='57' " . $this->placeholder($args['placeholder'] ) . " name='" . $args['name'] . "' value='" . $args['value']. "' />";
                            echo "<input class='attachment_upload button-secondary' id='" . $args['id'] . "-upload' type='button' value='Upload'/>";

                            // show a preview
                            $this->attachment_preview($args['value']);
                            echo $this->description($args['description']);	
                    } // function


                    /**
                    * Generate or display a thumbnail of the chosen file, needs a good cleanup
                    *
                    * @since	0.1
                    * @param   string  $original
                    * @access	public
                    * @return   void
                    */ 	
                    function attachment_preview($original) {
                        $file = str_replace(get_site_url().'/','' ,$original);
                        $file = str_replace('//','/',ABSPATH . $file);
                        // check if file exists
                        if (file_exists($file) && ($file != ABSPATH)) {
                            $thumb = wp_get_attachment_image( $this->get_attachment_id($original), array(80,80),1);

                            $ext = pathinfo($original, PATHINFO_EXTENSION);
                            // If the file hasnt been upload through wordpress
                            if (($this->get_attachment_id($original) == '') && ( in_array($ext ,$this->image_extensions))) {

                                $size = getimagesize($file);

                                if (($size[0] < 80) && ( $size[1] < 80)) {
                                    $thumb = "<img src='" . $original . "' />";
                                } else {
                                    $thumb =  "<img src='" . wp_create_thumbnail( $file, 40 ) . "' />";
                                }
                                //print var_export(wp_create_thumbnail( $file, 4 ),true);

                            } 
                            print "<div class='option_preview' ><a href='" . $original . "'>" . $this->filetourl($thumb) . "<br/>" .basename($original) . "</a></div>";
                        }
                    } // end function


                    /**
                    * Given a file return it as a url
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $file   a filename
                    * @return   string  a url path to a filename
                    */ 		 
                    function filetourl($file) {
                        return str_replace(ABSPATH , get_site_url().'/' ,$file);
                    }

                    /**
                    * Render a suggest posts field widget
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array  $args  field arguments
                    * @return   void
                    */ 		
                    function suggest($args) {
                        echo "<input type='text'  class='suggest field' data-id='" . $args['value'] . "' data-suggest='" . $args['suggestions']  . "'  size='57'  style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['name'] . "' value='" . $this->suggest_get_title($args['value'])  . "'/>";					
                        echo $this->description($args['description']);
                    } // function

                    /**
                    * Render a suggest user field widget
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array  $args  field arguments
                    * @return   void
                    */ 		
                    function suggest_users($args) {
                        echo "<input type='text'  class='suggest field' data-id='" . $args['value'] . "' data-suggest='" . $args['suggestions']  . "'  size='57'  style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['name'] . "' value='" . $this->suggest_get_title($args['value'])  . "'/>";					
                        echo $this->description($args['description']);
                    } // function

                    /**
                    * Render a suggest users field widget
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array  $args  field arguments
                    * @return   void
                    */ 		
                    function users($args) {
                        echo "<input type='text'  class='suggest-users' data-id='" . $args['value'] . "' data-suggest='" . $args['suggestions']  . "' data-roles='" . $args['roles']  . "'  size='57'  style='" . $this->width($args['width']) . "' " .  $this->placeholder($args['placeholder']) . " name='" . $args['name'] . "' value='" . $this->get_user_title($args['value'])  . "'/>";					
                        echo $this->description($args['description']);
                    } // function

                    /**
                    * Given an id show it along with the title in the autocmoplete textbox
                    *
                    * @since	0.1
                    * @see 		suggest
                    * @param    string  $id   
                    * @return   string
                    * @access	public
                    */ 
                    function suggest_get_title($id) {
                        if (empty($id)) { return "";   }
                        return get_the_title($id) . " [#". $id ."]";
                    }

                    /**
                    * Given an id show it along with the title in the autocmoplete textbox e.g. Title [# 101]
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $id     A post_id
                    * @return   string
                    */ 
                    function get_user_title($id) {
                        if (empty($id)) { return ""; }
                        $user_info = get_userdata($id);
                        $first_name = $user_info->first_name;
                        $last_name = $user_info->last_name;
                        return $first_name . " " . $last_name . " [*". $id ."]";
                    } // function

                    /**
                    * Ajax callback function to return list of post types
                    *
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */ 
                    function diy_suggest_posts_callback() {
                            global $wpdb, $post;

                            $posttype =  $wpdb->escape($_GET['type']);
                            $in =  $wpdb->escape($_GET['q']);

                            $query = "SELECT ID from $wpdb->posts where post_type = '$posttype' AND post_title like '%$in%' ";
                            $mypostids = $wpdb->get_col($query);

                            foreach ($mypostids as $key => $value) {
                                    print get_the_title($value) . " [#" .  $value . "]" . "\n";
                            }
                            die(); // this is required to return a proper result
                    } // function

                    /**
                    * Return a list of posts from a post type
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $type   The name of a registered post type
                    * @return   array   an array of ID => post_title
                    */ 
                    function get_by_type($type) {
                            $output = array();
                            $posts_array = get_posts( 'post_type=' . $type ); 
                            foreach( $posts_array as $post ) {
                                    setup_postdata($post); 
                                    $output[$post->ID] = $post->post_title ;
                            }
                            return $output;
                    } // function



                    /**
                    *  The Callback function to build a post metabox based on the arguments passed in from add_meta_box()
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $data
                    * @param    array   $args
                    * @return   void
                    */ 
                    function post_metabox_builder($data,$args) {
                            global $post;

                            // print var_export($args['args'],true);
                            $args=$args['args'] ;

                            if (!is_array($args)) {$args = array();}

                            foreach( $args as $field_group => $group) {
                                    echo "<table class='form-table'><tr><th scope='row'><strong>" . $group['title'] . "</strong></th><td>";		

                                    // Load up the current value
                                    $group_values = get_post_meta($post->ID, $group['group'], true);

                                    $this->print_field_group($group,$group_values);
                                    // if we have a repeatble group then add a button

                                    // call the function named in the type field
                                    // $this->{$meta_box['type']}($args);


                                    echo "</td></tr></table>";


                            } // end for

                    }

                    /**
                    *  Print a field group
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $group
                    * @param    array   $group_values
                    * @return   void
                    */ 
                    function print_field_group($group,$group_values) {
                        // if there are more than one field turn individual field titles on
                        if (count( $group['fields']) > 1) {$is_group = true;} else {$is_group = false;}
                        print '<div class="field-group-wrapper ' . ( ( $group['max'] > 1 )  ? 'field-group-multi' : '') . '" data-max="' . $group['max'] . '">';

                        // find out how many sets of data are stored for this group
                        if (count($group_values) > 1) {$sets = count($group_values); } else { $sets = 1;}

                        // Setup a counter to loop through the sets
                        $counter = 0;

                        while ($counter < $sets) {
                            print '<ul class="field-group" data-set="' . $counter . '">';
                            foreach( $group['fields'] as $field_name => $field) {
                                print '<li>';
                                if ($is_group) { print "<label class='" . ($field['label_style'] == 'block' ? "" : "fl" ) . "' style='" . ($field['label_width'] ? "width:" . $field['label_width'] . "px" : "" ) . "'>" . $field['title'] . "</label>";}

                                // Set the name attribute of the field
                                $field['name'] = "" . $group['group'] . "[" . $counter . "][" . $field_name . "]";
                                $field['id'] = $group['group'] . "-" . $counter . "-" . $field_name;
                                
                                // Set the current value of the field
                                if (is_array($group_values)) {
                                        $field['value'] = $group_values[$counter][$field_name];
                                } else {
                                        $field['value'] = "";
                                }
                                //print var_export($group_values,true);

                                // generate the form field
                                print $this->{$field['type']}($field);
                                print '</li>';	

                            } // end foreach

                            // for all but the first entry add a delete button
                            if ($counter > 0) {
                                    print '<a href="#" class="delete-group button">Delete</a>';
                            }

                            print '</ul>';

                            $counter++;
                        } // end while


                        if (($group['max'] > 1) && ($sets != $group['max'])) {print "<a href='#' class='another-group button'>Add Another</a>"; }

                        print '<div style="clear:both;"></div></div>';
                    } // end function

                    /**
                    *  Save the post meta box field data
                    *
                    * @since	0.1
                    * @access	public
                    * @param    string  $post_id    The post id we are saving
                    * @return   void
                    */ 
                    function diy_save_post( $post_id ) {
                        global $post, $new_meta_boxes;

                        // Stop WP from clearing custom fields on autosave
                        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                            return;

                        // Prevent quick edit from clearing custom fields
                        if (defined('DOING_AJAX') && DOING_AJAX)
                            return;

                        // Check some permissions
                        if ( 'page' == $_POST['post_type'] ) {
                                if ( !current_user_can( 'edit_page', $post_id ))
                                return $post_id;
                        } else {
                                if ( !current_user_can( 'edit_post', $post_id ))
                                return $post_id;
                        }
                        
                        // only save if we have something to save
                        if (isset($_POST['post_type'])  && $_POST['post_type'] && $this->meta[$_POST['post_type']]  ) {

                            // go through each of the registered post metaboxes
                            foreach ($this->meta[$_POST['post_type']] as $section_name => $section) {

                                // Go through each group in the metabox
                                foreach($section as $group_name => $group) {
                                    
                                    // Get the post data for this field group
                                    $data = $_POST[$group['group']];

                                    // Convert autosuggest value to a post id
                                    $data= $this->suggest_to_id($data);
                                        
                                    if(get_post_meta($post_id, $group['group']) == "") {
                                        add_post_meta($post_id, $group['group'], $data, true);
                                    } elseif ($data != get_post_meta($post_id, $group['group'], true)) {
                                        update_post_meta($post_id, $group['group'], $data);
                                    } elseif($data == "") {
                                        delete_post_meta($post_id, $group['group'], get_post_meta($post_id, $group['group'], true));
                                    }
                                    
                                    // save fields only for the current custom post type.	
                                    foreach($group['fields'] as $field_name => $field) {
                                        // if field is set to have expanded post meta
                                        if ($field['expanded'] == true) {
                                            // for each saved instance of this field save some post meta
                                            foreach ($data as $key => $instance) {
                                                $meta_field_name = $group['group'] . '_' . $key . '_' . $field_name;
                                                if(get_post_meta($post_id,  $meta_field_name) == "") {
                                                    add_post_meta($post_id,  $meta_field_name,  $data[$key][$field_name], true);
                                                } elseif ($data[$key][$field_name] != get_post_meta($post_id, $meta_field_name, true)) {
                                                    update_post_meta($post_id,  $meta_field_name,  $data[$key][$field_name]);
                                                } elseif($data[$key][$field_name] == "") {
                                                    delete_post_meta($post_id,  $meta_field_name, get_post_meta($post_id,  $meta_field_name, true));
                                                }
                                            }
                                            
                                        } // endif
                                    } // end foreach
                                    
                                } // end foreach
                            } // end foreach
                        } //end if isset
                    } // end function

                    /**
                    * Print the form field group on the settings page
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $args
                    * @return   void
                    */ 
                    function settings_field_callback($args) {

                            // Load up the current options
                            $group_values = get_option($args['group']);

                            $this->print_field_group($args,$group_values);
                    } // end function

                    /**
                    * Build the meta box content and print the fields
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $data
                    * @param    array   $args
                    * @return   void
                    */ 
                    function diy_option_field_builder($data,$args) {
                        // Print the metabox description at the top of the metabox
                        if ($args['args']['description']) {
                            echo '<div class="options-description" style="padding:10px; line-height: 1.6;">';
                                echo $args['args']['description'];
                            echo '</div>';
                        }
                        echo '<table class="form-table">'; 
                            // Output the settings fields asssigned to this section
                            do_settings_fields(  $this->page, $args['args']['section'] ); 
                        echo '</table>';
                    } // function

                    /**
                    * Convert all autocomplete fields to a post_id [# ]
                    *
                    * @since	0.1
                    * @access	public
                    * @param    array   $data   the array of options
                    * @return   array
                    */ 
                    function suggest_to_id($data) {
                        global $wpdb;
                        if (is_array($data)) {
                            // crawl through the array to check all values
                            foreach ($data as $key => $id) {
                                foreach ($id as $field => $value) {
                                    // if the [# string is found in the data
                                    if (strlen(strstr($data[$key][$field],'[#'))>0) {
                                        // extract it [# ] 
                                        preg_match('/.*\[#(.*)\]/', $data[$key][$field], $matches);
                                        $data[$key][$field] =  $matches[1];
                                        // Retrieve matching data from the posts table
                                        $result = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts AS wposts  WHERE wposts.ID = '" . $data[$key][$field] . "'");
                                        if ($result == 0) {
                                                $data[$key][$field]='';
                                        }
                                    }
                                } // end foreach
                            } // end foreach
                        } // end if
                        return $data;
                    } // function


                    /**
                    * Print the CSS styles
                    *
                    * The CSS is iself served instead of as separate file to keep the Diy class as a single file
                    * 
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */ 	
                    function diy_css() {
                            // Repeatable field group buttons
                            print '.field-group { padding: 0px 0 0px 0; margin-top: 0px; margin-bottom: 0px;}';
                            print '.field-group-multi .field-group {border-bottom: 1px solid #E3E3E3; margin: 0px 0 10px 0; }';
                            print '.another-group {float:right; margin-top: 10px; }';
                            print '.delete-group {float:right; margin-top: -34px; }';
                            print '.field-group label.fl {float:left; line-height: 30px; }';
                            // Re-align the grippie 
                            print 'form#settings #post-body .wp_themeSkin .mceStatusbar a.mceResize  { top:-2px; }';
                            print '#post-body .postbox  .wp_themeSkin .mceStatusbar a.mceResize  { top:-2px; }';

                            // Set the font size for field descriptions
                            print '.description { font-size: 11px !important;}';

                            print '#wpbody-content { overflow:visible !important; }';

                            /* Wysiwyg */
                            print '#editorcontainer textarea { width:100%; }';
                            print '#poststuff .postbox  .postarea{ margin-bottom: 0px; moz-box-shadow: none; -webkit-box-shadow:none; box-shadow: none; border:none; }';

                            /* Attachment previews*/
                            print '.option_preview { width: 100px;float: right;margin-top: 0px; word-wrap: break-word; text-align: center; line-height: 1.4; }';
                            print '.post-attachment  .option_preview { margin-top: 5px; }';

                            /* Color Field Widget */
                            print '.swatch { cursor: pointer; width: 20px; height: 20px; position: absolute; left: 4px; display: block; -moz-border-radius: 2px; -webkit-border-radius: 2px; border-radius: 2px;
                            -moz-background-clip: padding; -webkit-background-clip: padding-box; background-clip: padding-box; top: 4px; }';

                            print 'div.picker {position:absolute;display:none; z-index:9999; left:110px; bottom:-195px; background:#000000;-moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px; }';

                            print "input.picker {height:28px; width:100px; margin-left:0px; cursor:pointer; margin-top:0px; padding-left: 30px;  font-size: 13px; background-color: white;
                            background-image: -webkit-gradient(linear, left bottom, left top, color-stop(0, #eeeeee), color-stop(0.5, white));
                            background-image: -webkit-linear-gradient(center bottom, #eeeeee 0%, white 50%);
                            background-image: -moz-linear-gradient(center bottom, #eeeeee 0%, white 50%);
                            background-image: -o-linear-gradient(top, #eeeeee 0%,#ffffff 50%);
                            background-image: -ms-linear-gradient(top, #eeeeee 0%,#ffffff 50%);
                            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eeeeee', endColorstr='#ffffff',GradientType=0 );
                            background-image: linear-gradient(top, #eeeeee 0%,#ffffff 50%);
                            }";

                            // Give the field widget titles some room to breathe
                            print '.form-table th { width: 190px; font-weight: bold; }';

                            // Modify the height of text boxes
                            print 'input[type="text"] { height:28px; }';

                            // Modify the checkbox alignment
                            print 'input[type="checkbox"]  { top: -1px; position: relative; }';

                            // Form field styles
                            print 'input {background-color: white; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -moz-background-clip: padding; -webkit-background-clip: padding-box; background-clip: padding-box; border: 1px solid #DFDFDF !important; color: #444;}';
                            print '.inline-rel {position:relative; display:inline-block; height: 28px; width:105px; }';
                            print '.field-suffix {padding-left:4px;}';

                            // Social Media icons on the plugin options page
                            print '.icon-linkedin {padding-left:24px; background:url(data:image/png;base64,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) no-repeat left center;}';
                            print '.icon-twitter {padding-left:24px; background:url(data:image/png;base64,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) no-repeat left center;}';
                            print '.icon-wordpress {padding-left:24px; background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAAAvFJREFUeNpkk8trnFUYxn/nfGdumVuHGYLJdGxzqWKaagxZqTtBXbmyuHLhQhC67FYQwbULQfAvcNF/oJKKGy3SiwrpBWdMay5NTNLJJDPJfN833/eec1wkrYIvPKuX5+GBh5/65Lubn/tS/srI+azi3/OnAlCn+u8vp1WijuNvzEHGXN0ypizWAeC8x6EYM5pAndhS54nEYrR6HmQCTTNjrpr+KPFHWmO8J3WeM7kMUwVNFUs5UFhvGSSWg0DzOHbEXhEoiJSiP0q8wTqvrCN1jsmxHK+VDJeqOZaaDc7Vyljn6Oz1uLu5y8RByK1jx1AZlNZgndfOWiQVSlpxqQBvNQp8OH+e2VqJDI6cgqUXJ8gN+/zysM1iUaOiECcWZy3GiWC15pVans7GFusbjulKARlFfHbte2aaE3zxwTus7x+ybrMsqIApLTyMIpxzaCtCznsqXnij2WDlcERs8rz+0iytVot7kqdSKvPx229ysVpgtpxHj0LktIW2ImScJWuFy69OUxsdc729CcC7F87S3tjmt+0u58brLBQVi/UxBod9EIcXQTsRbCKEYUx1bIz3pl9g+d4qIxGiQZ8zGpY7W3T3D7g4XqWkQeIYLxYngvapMDyOOBjGtHe6XF6aY2t7l+X7j5ms1/ho8QI/rKzy4/0O8+dbrG4+IRcE2Dg+CcBakjhhb5hyY+VPJhs1FupFvrpxl9bZJu/PT7G2/oTbfx+RDTS3/viL/VDQ4nCpYFwqBE6z1k1oKOHaz7/zcr3E7b2YmYlxAOZqBbqhcP3OAzq9iN3Qk60oXJqezIgF5+HXrT4+Scj7hLnxGst3VhiFx8xUsvSjIT9t9njwNCRTrePTFBd4jBVRNnEESpF6uLneo1XKUI+6fNl+hLcppWKJnaMeO0MhX66CWKwXbFYr40W0xIJTz8hTrB2OePR0cIKdCmAQYkyAyReRRACP8+C10aYY+K8bWfWpxwfPYFVoyBX+x7B/DjMolC0G/tt/BgDgG46kl8G7FgAAAABJRU5ErkJggg==) no-repeat left center;}';

                            // Remove the underline from links in metaboxes
                            print 'div.inside a, div.inside a.rsswidget { text-decoration: none;  }';

                            // Datepicker Field Widget
                            print '.ui-state-default{
                                    background-color: #F5F5F5;
                                    background-image: -ms-linear-gradient(top,#f9f9f9,#f5f5f5);
                                    background-image: -moz-linear-gradient(top,#f9f9f9,#f5f5f5);
                                    background-image: -o-linear-gradient(top,#f9f9f9,#f5f5f5);
                                    background-image: -webkit-gradient(linear,left top,left bottom,from(#f9f9f9),to(#f5f5f5));
                                    background-image: -webkit-linear-gradient(top,#f9f9f9,#f5f5f5);
                                    background-image: linear-gradient(top,#f9f9f9,#f5f5f5);
                                    width: 25px; height: 25px;
                                    display: block; text-align: center;
                                    border-radius: 5px; text-decoration: none;
                                    line-height: 25px; color: #666666;
                                    font-size: 11px; }';

                            print '.ui-datepicker-group { width: 189px; float:left; margin-left:8px; }';

                            print '.ui-datepicker-group-first { margin-left:0px; }';

                            print '.ui-datepicker {background: white; border:1px solid #BBBBBB;
                            border-radius: 5px; width: 583px !important;
                            padding: 5px 5px;}';

                            print '.ui-datepicker-title {background-color: #F1F1F1;
                            background-image: -ms-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -moz-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -o-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -webkit-gradient(linear,left top,left bottom,from(#f9f9f9),to(#ececec));
                            background-image: -webkit-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: linear-gradient(top,#f9f9f9,#ececec);
                            border-radius: 5px;
                            margin: 1px;
                            height: 25px;
                            display: block;
                            text-align: center;
                            color: #464646;
                            font-size: 11px;
                            line-height: 25px;}';


                            print '.ui-datepicker-calendar  { border-collapse: collapse; padding: 0; margin: 1px 0 0 0; }';

                            print '.ui-datepicker-calendar  th {padding: 0; border: none;}';

                            print '.ui-datepicker-calendar th span {
                            background-color: #F1F1F1;
                            background-image: -ms-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -moz-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -o-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: -webkit-gradient(linear,left top,left bottom,from(#f9f9f9),to(#ececec));
                            background-image: -webkit-linear-gradient(top,#f9f9f9,#ececec);
                            background-image: linear-gradient(top,#f9f9f9,#ececec);
                            width: 25px;
                            height: 25px;
                            display: block;
                            text-align: center;
                            border-radius: 5px;
                            margin: 1px;
                            text-decoration: none;
                            line-height: 25px;
                            color: #464646;
                            font-size: 11px;
                            font-weight: normal;
                            }';

                            // Datepicker previous/next icons
                            print '.ui-icon-circle-triangle-w {background: transparent url(../../../wp-admin/images/arrows.png) no-repeat 6px -67px;
                            width: 25px; float: left; height:25px;}';
                            print '.ui-icon-circle-triangle-e {background: transparent url(../../../wp-admin/images/arrows.png) no-repeat 6px -103px;
                            width: 25px; float: right; height:25px;}';

                    } // end function


                    /**
                    * Print some javascript into the admin footer
                    *
                    * The JS is self served instead of as separate file to keep the diy as a single file
                    * 
                    * @since	0.1
                    * @access	public
                    * @return   void
                    */ 	
                    function diy_js() {
                            // open closure
                            print 'jQuery(document).ready(function ($) {' . PHP_EOL;
                            print '    "use strict";' . PHP_EOL;

                            // Apply sorting to DOM elements where class is field-group-sortable
                            print '    $(".field-group-sortable").sortable();' . PHP_EOL;

                            // if the delete group button is pressed
                            print '    $(".delete-group").live("click", function (event) {
                                    event.preventDefault();

                                    // Save a reference to the outer wrapper	
                                    var test = $(this).closest(".field-group-wrapper");		

                                    // Save the max as wheese gonna need it later
                                    var max = test.data("max");


                                    // remove teh one we want to delete
                                    $(this).closest(".field-group").remove();

                                    // Save how many groups we gots left
                                    var howmany = test.find(".field-group").length;

                                    // re-number the groups and fields
                                    test.find(".field-group").each(function(index){
                                            $(this).attr("data-set",index);
                                            var numset = index;
                                            $(this).find(".field").each(function(){
                                                    $(this).attr("name",
                                                            $(this).attr("name").replace(/\[(.*)\]\[/,"[" + (numset) + "][")
                                                    );
                                            });
                                    });

                                    // If we are now less than the max allowed groups add in the add-another button if it aint there

                                    if ((howmany < max) && (!(test.find(".another-group").length > 0 ))) {

                                            test.find(".field-group:last").after("<a href=\'#\' class=\'another-group button\'>Add Another</a>")
                                    }
                            });'; 

                            // If the add group button is pressed
                            print '$("body").on("click",".another-group",function(event) {
                                    event.preventDefault();

                                    var max = $(this).closest(".field-group-wrapper").data("max");

                                    // count how many groups we already have
                                    var howmany = $(this).closest(".field-group-wrapper").find(".field-group").length;
                                    //alert(howmany);
                                    // get the first instance
                                    var firstgroup  = $(this).closest(".field-group-wrapper").find(".field-group:first");

                                    // get the last instance
                                    var lastgroup  = $(this).closest(".field-group-wrapper").find(".field-group:last");

                                    var copied = firstgroup.clone();

                                    // Clear the attributes
                                    // Should not clear checkbox values..
                                    copied.find(".field").attr("value","");
                                    copied.find("[type=checkbox]").attr("value","1");

                                    // change the data set parameter to the newsy one
                                    copied.attr("data-set",howmany);

                                    // 
                                    copied.find(".field").each(function (index) {

                                    // jQuery(".field:first").attr("name").replace(/\[(.*)\]\[/,"[cheese][")
                                            $(this).attr("name",
                                                            $(this).attr("name").replace(/\[(.*)\]\[/,"[" + (howmany) + "][")
                                            );
                                            // remove the classes so the new fields get rebound with handlers
                                            $(this).removeClass("suggested picked");
                                    });

                                    copied.insertBefore($(this));
                                            $("<a href=\'#\' class=\'delete-group button\'>Delete </a>").appendTo($(this).prev());
                                    // if we already have reached the max groups then dont do any more
                                    if (max == howmany + 1) {
                                            $(this).remove();

                                    }
                                    // Attach handlers for any new fields that need them
                                    diy_picker();
                                    diy_suggester();
                            });';

                          
                            // When the upload button for attachment widget is pressed
                            print '     jQuery("body").on("click",".attachment_upload",function() {
                                            jQuery(".attachment").removeClass("active");
                                            jQuery(this).parent().find(".attachment:first").addClass("active");
                                            tb_show("","media-upload.php?post_id=0&TB_iframe=1");
                                            return false;';
                            print '     });';
                           
                             
                            // Duck punch the crap out of the send_to_editor function
                            print 'var _send_to_editor = window.send_to_editor;
                            window.send_to_editor = function (html) {
                                    var imgurl, aurl;
                                    if (jQuery(".attachment.active").length > 0) {
                                    console.log(html);
                                            imgurl = jQuery("img",html).attr("src");
                                            aurl = jQuery("a","<div>" + html + "</div>").attr("href");

                                            if (imgurl) {
                                                    jQuery(".attachment.active").val(imgurl);
                                            } else {
                                                    jQuery(".attachment.active").val(aurl);
                                            }
                                            jQuery(".attachment").removeClass("active");
                                            tb_remove();
                                    } else {
                                            _send_to_editor(html);
                                    }
                            };';
                            
                            print 'function diy_suggester() {';
                            // Apply jquery suggest to textboxes with class .suggest
                            print '     jQuery(".suggest:not(.suggested)").each(';
                            print '         function () { ';
                            print '             jQuery(this).suggest(';
                            print '                 ajaxurl + "?action=suggest_action&type=" + jQuery(this).data("suggest")';
                            print '             );';
                            print '             jQuery(this).addClass("suggested");';
                            print '         }';
                            print '      );';
                            print '}'; // end of diy_suggester()

                            print 'diy_suggester();';
                            
                            // Farbtastic it up for all .picker classes
                            print 'function diy_picker() {';
                            print '     var notyetpicked = jQuery("input.picker:not(.picked)");';
                            print '     notyetpicked.each(function () {';
                            print '         var saveid=jQuery(this);';
                            print '         jQuery(this).next("div.picker").farbtastic(function (color) { saveid.val(color.toUpperCase()).prev(".swatch").css("background",color); }); ';
                            print '     });';
                            
                            // Show and hide the picker 
                            print '     notyetpicked.focus(function () {jQuery(this).next("div.picker").show();});
                                        notyetpicked.blur(function () {jQuery(this).next("div.picker").hide();});';
                            
                            // Add the picked class so we dont attach things twice
                            print '     jQuery(this).addClass("picked");';
                            print '}'; // end of diy_picker()

                            // Enable all color pickers
                            print 'diy_picker();';

                            // Do the date picker using HTML5 data atrributes
                            print 'jQuery( ".datepicker" ).datepicker({
                                    defaultDate: "0",
                                    numberOfMonths: 3,
                                    showOtherMonths: true,
                                    altFormat: "dd/mm/yy"
                            });	';




                            print '


                                    // for each div with the class of gmap
                                    $(".gmap").each(function(index){
                                            var map = [];

                                            // populate the data attributes
                                            var savedlat = $("[name=\"" + $(this).data("latfield") + "\"]").val();
                                            var savedlong = $("[name=\"" + $(this).data("longfield") + "\"]").val();

                                            // Setup the map center/marker location
                                            var latlng = new google.maps.LatLng(savedlat, savedlong);

                                            // define the map options
                                            var options = {
                                                    zoom: $(this).data("zoom"),
                                                    center: latlng,
                                                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                                                    draggableCursor: "crosshair",
                                                    streetViewControl: false
                                            };


                                            map[index] = new google.maps.Map(document.getElementById( $(this).attr("id") ), options);

                                            // stick the map marker on the map
                                            var marker;
                                            marker = new google.maps.Marker({
                                                    position: latlng,
                                            map: map[index]});
                                            var tester = 1;

                                            // add the map clickerooner

                                            map[index].latfield = $(this).data("latfield");
                                            map[index].longfield = $(this).data("longfield");

                                            google.maps.event.addListener(map[index],"click", function(location) {


                                                    if (marker != null) {

                                                    marker.setMap(null);

                                                    }

                                                    marker = new google.maps.Marker({

                                                    position: location.latLng,

                                                    map: map[index]});



                                                    jQuery("[name=\"" + map[index].latfield + "\"]").val(location.latLng.lat());
                                                    jQuery("[name=\"" + map[index].longfield + "\"]").val(location.latLng.lng());

                                            });



                                    });';

                            // end closure
                            print '});';

                    } // end function
                } // end class definition

                function diy_option($group,$id,$instance = 0) {
                    $result = array();
                    $result = get_option($group);

                    if (is_array($result)) {
                            return $result[$instance][$id];
                    } else {
                            return '';
                    }
                } // end function


                function diy_post_meta($post_id,$group,$field,$instance = 0) {
                        $result = array();
                        $result = get_post_meta($post_id,$group,true);

                        if (is_array($result)) {
                                return $result[$instance][$field];
                        } else {
                                return '';
                        }
                } // end function
                    
    } // end if class exists
	
    // Run each of the dependant plugins own init methods
    do_action( 'diy_init' );
}

