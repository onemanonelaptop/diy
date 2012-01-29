<?php
/**
 * Plugin Name: Diy Plugin Test Suite
 * Plugin URI: http://github.com/onemanonelaptop/diy
 * Description: Field widget test suite for the diy plugin framework
 * Version: 0.0.1
 * Author: Rob Holmes
 * Author URI: http://github.com/onemanonelaptop
 */
add_action( 'diy_init', 'testdiy_init' );
function testdiy_init() {  
    if ( !class_exists( 'TestDiy' ) ) {
        // Extend the framework and make a plugin
        class TestDiy extends Diy {

                // Set the plugin defaults
                var $slug = 'diy';
                var $usage = 'plugin';
                var $settings_page_title = 'Diy Plugin Test Suite';
                var $settings_page_link = 'Diy Plugin Tests';

                // Define the metaboxes and fields (called by parent class contructor)
                function setup() {
                    
                    // Add the new meta box
                    $this->metabox(
                            array( 
                                    'id' => 'field-tests-single',
                                    'title' => 'Field Tests (Single)'
                            )
                    ); 
                    
                    $this->metabox(
                            array( 
                                    'id' => 'field-tests-multi',
                                    'title' => 'Field Tests (Single)'
                            )
                    ); 
                    
                    
                   $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-checkbox-single", // The form field name
                            "title" => "Checkbox", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "checkbox",
                                    "description" => "Checkbox Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-text-single", // The form field name
                            "title" => "Text", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "text",
                                    "description" => "Text Description",
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-textarea-single", // The form field name
                            "title" => "Textarea", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "textarea",
                                    "description" => "Textarea Description",
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-select-single", // The form field name
                            "title" => "Select", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "select",
                                    "description" => "Select Description",
                                    "selections" => array("0"=>"0","1"=>"1","2"=>"2")
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-color-single", // The form field name
                            "title" => "Color", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "color",
                                    "description" => "Color Description",
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-attachment-single", // The form field name
                            "title" => "Attachment", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "attachment",
                                    "description" => "Attachment Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-wysiwyg-single", // The form field name
                            "title" => "WYSIWYG", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "text",
                                    "description" => "WYSIWYG Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-date-single", // The form field name
                            "title" => "Date", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "text",
                                    "description" => "Date Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-suggest-post-single", // The form field name
                            "title" => "Suggest Posts", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "suggest",
                                    "description" => "Suggest Posts Description",
                                    "suggestions" => "post",
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-single", // the id of the metabox this field resides inside
                            "group" => "test-suggest-pages-single", // The form field name
                            "title" => "Suggest Posts", // Title used when prompting for input
                            "max" => "1",
                            "fields" => array(
                                "value" => array(
                                    "type" => "suggest",
                                    "description" => "Suggest Pages Description",
                                    "suggestions" => "page",
                                )
                            ) // end fields
                        ) // end array
                    ); 
                    
                    
                    // MULTI FIELD TESTS
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-checkbox-multi", // The form field name
                            "title" => "Checkbox", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "checkbox",
                                    "description" => "Multi Checkbox Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-text-multi", // The form field name
                            "title" => "Text", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "text",
                                    "description" => "Multi Text Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-textarea-multi", // The form field name
                            "title" => "Textarea", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "textarea",
                                    "description" => "Multi Textarea Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                    $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-color-multi", // The form field name
                            "title" => "Color", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "color",
                                    "description" => "Multi Color Description"
                                )
                            ) // end fields
                        ) // end array
                    ); 
                    
                    
                      $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-attachment-multi", // The form field name
                            "title" => "Attachment", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "attachment",
                                    "description" => "Multi Attachment Description"
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                      
                      $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-select-multi", // The form field name
                            "title" => "Select", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "select",
                                    "description" => "Multi Select Description",
                                    "selections" => array("0"=>"0","1"=>"1","2"=>"2")
                                )
                            ) // end fields
                        ) // end array
                    );
                      
                       $this->field(
                        array(
                            "metabox" => "field-tests-multi", // the id of the metabox this field resides inside
                            "group" => "test-suggest-post-multi", // The form field name
                            "title" => "Suggest Posts", // Title used when prompting for input
                            "max" => "5",
                            "fields" => array(
                                "value" => array(
                                    "type" => "suggest",
                                    "description" => "Suggest Posts Description",
                                    "suggestions" => "post",
                                )
                            ) // end fields
                        ) // end array
                    );
                    
                   // Start the plugin
                   $this->start();
                   
                   // Plugin functionality
                  
                } // end function
                
                
                
                
                
          
        } // end class
        $testdiy = new TestDiy();
    } // end if class exists
} // end init



