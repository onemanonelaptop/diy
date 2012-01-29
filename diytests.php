<?php
/**
 * Plugin Name: Diy Plugin Test Suite
 * Plugin URI: http://github.com/onemanonelaptop/diy
 * Description: Field widget test suite for the diy plugin framework. When activated it adds one of every type of field to the options page, new post and new page screens
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
                       
                       
                    // *********************************************************  
                    // POST METABOX FIELD TEST
                    // *********************************************************
                      
                    $this->metabox(
                            array( 
                                'id' => 'field-post-tests-single',
                                'title' => 'Field Post Tests (Single)',
                                'post_type' => array('post','page')
                            )
                    ); 
                    
                    $this->metabox(
                            array( 
                                'id' => 'field-post-tests-multi',
                                'title' => 'Field Post Tests (Multi)',
                                'post_type' => array('post','page')
                            )
                    ); 
                        
                    $this->field(
                        array(
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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
                            "metabox" => "field-post-tests-single", // the id of the metabox this field resides inside
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

                   // Start the plugin
                   $this->start();

                } // end function
        } // end class
        $testdiy = new TestDiy(__FILE__); 
    } // end if class exists
} // end init



