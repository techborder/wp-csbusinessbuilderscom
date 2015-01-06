<?php

/**
 * Initialize the meta boxes. 
 */
add_action( 'admin_init', 'custom_meta_boxes' );

function custom_meta_boxes() {

  $page_options = array(
    'id'        => 'page_options',
    'title'     => 'Skeleton Page Options',
    'desc'      => '',
    'pages'     => array( 'page' ),
    'context'   => 'normal',
    'priority'  => 'high',
    'fields'    => array(
      
     array(
    	'id' => 'title_1',
		'label' => 'Page Specific Options',			
		'desc' => 'This is a set of custom options for just this page. These are entirely optional, but they bring some extra functionality to pages. Some page templates (Portfolios, Blogs, etc.) may need some of these options filled out to work properly.',
		'type' => 'textblock',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
	
	array(
    	'id' => 'custom_background_image',
		'label' => 'Body Background Image',			
		'desc' => 'Upload an image, then place the URL here. ie: http://yoursite.com/images/custom_bg_image.jpg',					
		'type' => 'upload',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
		
	array(
        'id'          => 'category_filter',
        'label'       => 'Category Filter',
        'desc'        => 'Select which categories you would like included. <strong>These are only used if the page template that you have selected uses blog posts, like the Portfolio or Blog templates</strong>.',
        'std'         => '',
        'type'        => 'category-checkbox',
        'class'       => ''
      ),
      
      array(
    	'id' => 'post_count',
		'label' => 'Number of Posts Per Page',			
		'desc' => 'If this is a page template that uses blog posts, set a number for how many posts you want to show up per page. The default is set to show ALL posts found within the above category(s).',					
		'type' => 'text',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
	
	array(
    	'id' => 'column_flip',
		'label' => 'Flip Columns?',			
		'desc' => 'Do you want to swap the sides for the Main Column and Sidebar Column?',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
	array(
    	'id' => 'portfolio_view',
		'label' => 'Default Portfolio View',			
		'desc' => 'If this is a Portfolio page template, you can set the default view here.',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Grid',
            'label'       => 'Grid',
            'src'         => ''
          ),
          array(
            'value'       => 'Hybrid',
            'label'       => 'Hybrid',
            'src'         => ''
          ),
          array(
            'value'       => 'List',
            'label'       => 'List',
            'src'         => ''
          )
        ),
	),
	
	array(
    	'id' => 'frontpage_slider',
		'label' => 'FrontPage Slider: On/Off',			
		'desc' => 'Toggle the slider element on or off from this option. <strong>Note:</strong> This is specifically to grab the front-page slider that is managed from the Theme Options panel. The page-specific slider manager is below this module.',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
	array(
    	'id' => 'breakout_hide',
		'label' => 'Hide the "From the Blog" Row?',			
		'desc' => 'Toggle the "From the Blog" element off from this option (only relevant if it is turned on in the Theme Options panel). ',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
    )
  );







  $post_options = array(
    'id'        => 'post_options',
    'title'     => 'Skeleton Post Options',
    'desc'      => '',
    'pages'     => array( 'post' ),
    'context'   => 'normal',
    'priority'  => 'high',
    'fields'    => array(
      
     array(
    	'id' => 'title_1',
		'label' => 'Post Specific Options',			
		'desc' => 'This is a set of custom options for just this post. These are entirely optional, but they bring some extra functionality to post.',
		'type' => 'textblock',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
	array(
    	'id' => 'custom_background_image',
		'label' => 'Body Background Image',			
		'desc' => 'Upload an image, then place the URL here. ie: http://yoursite.com/images/custom_bg_image.jpg',					
		'type' => 'upload',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
	
	array(
    	'id' => 'lightbox_link',
		'label' => 'Custom Lightbox Link',			
		'desc' => 'Insert a URL for an image or video (Vimeo, YouTube, or .MOV) to be launched inside a lightbox from the post thumbnail. See the <a href="http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/">lightbox documentation</a> for acceptable media.',					
		'type' => 'text',					
		'std' => '',
		'class' => '',
      	'choices' => array()
	),
		
	array(
    	'id' => 'remove_sidebar',
		'label' => 'Remove the Sidebar?',			
		'desc' => 'Do you want to remove the sidebar and use the full-width template for this post?',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
		
	array(
    	'id' => 'column_flip',
		'label' => 'Flip Columns?',			
		'desc' => 'Do you want to swap the sides for the Main Column and Sidebar Column?',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
	array(
    	'id' => 'breakout_hide',
		'label' => 'Hide the "From the Blog" Row?',			
		'desc' => 'Toggle the "From the Blog" element off from this option (only relevant if it is turned on in the Theme Options panel). ',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
    )
  );








$slider_options = array(
    'id'        => 'slider_options',
    'title'     => 'Skeleton Slider Options',
    'desc'      => '',
    'pages'     => array( 'page', 'post' ),
    'context'   => 'normal',
    'priority'  => 'high',
    'fields'    => array(
      
    array(
        'id'          => 'slider_notes',
        'label'       => 'Slider Notes',
        'desc'        => 'The settings below will control the slider options for just this page. Make sure you fill out all of the options (even if you want to leave an option off), or the slider might break.',
        'std'         => '',
        'type'        => 'textblock',
        'class'       => ''
      ),
      
      array(
    	'id' => 'image_slider',
		'label' => 'Page Slider: On/Off',			
		'desc' => 'Toggle the slider element on or off from this option.',					
		'type' => 'radio',					
		'std' => '',
		'class' => '',
      	'choices'     => array( 
          array(
            'value'       => 'Yes',
            'label'       => 'Yes',
            'src'         => ''
          ),
          array(
            'value'       => 'No',
            'label'       => 'No',
            'src'         => ''
          )
        ),
	),
	
      array(
        'id'          => 'homepage_slider',
        'label'       => 'Slider: Slide Manager',
        'desc'        => 'Upload images that you\'d like to be used as slides, as well as a simple destination URL for when visitors click each slide. 

Note: The theme will automatically resize any oversized images to fit the space. Images should all be roughly the same height, and images that are too small will not be scaled "up" to fit the space.',
        'std'         => '',
        'type'        => 'list-item',
      ),
      array(
        'id'          => 'slider_fx',
        'label'       => 'Slider: Transition Effect',
        'desc'        => 'Select the effect that will transition you from one set of slides to another.',
        'std'         => '',
        'type'        => 'select',
        'choices'     => array( 
          array(
            'value'       => 'fade',
            'label'       => 'fade',
            'src'         => ''
          ),
          array(
            'value'       => 'slide',
            'label'       => 'slide',
            'src'         => ''
          ),
        ),
      ),
      array(
        'id'          => 'slider_auto',
        'label'       => 'Slider: AutoPlay',
        'desc'        => 'Selecting "true" will result in a slider that plays automatically. <br /><br />Selecting "false" will require users to manually advance the slider with the  buttons, keyboard  keys, or a finger swipe.',
        'std'         => '',
        'type'        => 'radio',
        'choices'     => array( 
          array(
            'value'       => 'true',
            'label'       => 'true',
            'src'         => ''
          ),
          array(
            'value'       => 'false',
            'label'       => 'false',
            'src'         => ''
          )
        ),
      ),
      array(
        'id'          => 'slider_autoduration',
        'label'       => 'Slider: AutoPlay Pause Time',
        'desc'        => 'Select a time, in milliseconds, for the pause duration of each slide if you selected "true" for the auto play feature. Hint: "2000" is fast, "8000" is slower.',
        'std'         => '',
        'type'        => 'select',
        'choices'     => array( 
          array(
            'value'       => '2000',
            'label'       => '2000',
            'src'         => ''
          ),
          array(
            'value'       => '2500',
            'label'       => '2500',
            'src'         => ''
          ),
          array(
            'value'       => '3000',
            'label'       => '3000',
            'src'         => ''
          ),
          array(
            'value'       => '3500',
            'label'       => '3500',
            'src'         => ''
          ),
          array(
            'value'       => '4000',
            'label'       => '4000',
            'src'         => ''
          ),
          array(
            'value'       => '4500',
            'label'       => '4500',
            'src'         => ''
          ),
          array(
            'value'       => '5000',
            'label'       => '5000',
            'src'         => ''
          ),
          array(
            'value'       => '5500',
            'label'       => '5500',
            'src'         => ''
          ),
          array(
            'value'       => '6000',
            'label'       => '6000',
            'src'         => ''
          ),
          array(
            'value'       => '6500',
            'label'       => '6500',
            'src'         => ''
          ),
          array(
            'value'       => '7000',
            'label'       => '7000',
            'src'         => ''
          ),
          array(
            'value'       => '7500',
            'label'       => '7500',
            'src'         => ''
          ),
          array(
            'value'       => '8000',
            'label'       => '8000',
            'src'         => ''
          )
        ),
      ),
	
    )
  );


 
  ot_register_meta_box( $page_options );
  ot_register_meta_box( $post_options );
  ot_register_meta_box( $slider_options );
  
}

?>