<?php

class wp_feedback {

    public $plugin_options  =   array(  'fdb_form_description'  =>  'Use the form below to send us your comments.  Feedback will also be reviewed by other users of our website.',
                                        'fdb_form_response'     =>  'Thanks for your feedback!',
                                        'fdb_form_not_logged_in'=>  'Please <a href="./wp-login.php">login</a> to submit feedback.',
                                        'fdb_jqueryui_theme'    =>  'ui-lightness'
                                        );
    public $fdb_version     =   '1.0';
    public $blog_url        =   false;
    
    function __construct($options = array()) {
    
        foreach($options as $attribute => $value) {
            $this->$attribute   =   $value;
        }
        
        add_action('init', array($this, 'register_request_post_type'));
    }
    
    function activate_plugin() {
        
        foreach($this->plugin_options as $option => $value) {
            // activating the default values
            add_option($option, $value);
        }
        
        // create votes table
        $this->create_votes_table();
        
        // add feedback page with shortcode
        $this->fdb_insert_feedback_listing_page();
        
    }
    
    function deactivate_plugin() {
        
       // deletion of options
       foreach($this->plugin_options as $option => $value) {
           delete_option($option, $value);
       }
        
       // delete feedback page 
       $query   =   new WP_Query('post_type=page&meta_key=main_feedback_page' );

       if($query->have_posts()) { 
           
           while($query->have_posts()) { 
               $query->the_post();

               $main_feedback_page_id  =   get_the_ID();
               wp_delete_post($main_feedback_page_id);
           }
       }
       
       // drop votes table
       $this->drop_votes_table();
    }
    
    function uninstall_plugin() {
        
        // needed for proper deletion of every option
        delete_option('ept_option_3');        
        delete_option('fdb_version');  
        
        // deletion of options
        foreach($this->plugin_options as $option => $value) {
            delete_option($option, $value);
        }
        
        // drop votes table
        $this->drop_votes_table();
    }
    
    function create_votes_table() {
        global $wpdb;
        
        $table_name =   $wpdb->prefix . "fdb_votes";
        $sql        =   "CREATE TABLE $table_name (
        fdb_vote_id INT NOT NULL AUTO_INCREMENT,
        fdb_post_id INT NOT NULL,
        fdb_user_id INT NOT NULL,
        fdb_vote INT NOT NULL,
        fdb_vote_updated TIMESTAMP NOT NULL,
        PRIMARY KEY fdb_vote_id (fdb_vote_id),
        UNIQUE INDEX fdb_user_id_post_id(fdb_post_id, fdb_user_id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
     
        add_option("fdb_version", $this->fdb_version);
    }
    
    function drop_votes_table() {
        global $wpdb;
        
        $table_name =   $wpdb->prefix . "fdb_votes";
        $sql        =   "DROP TABLE $table_name";

        $wpdb->query($sql);
    }
    
    function fdb_register_settings() {
        
        foreach($this->plugin_options as $option => $value) {
            //register settings
            register_setting( 'fdb-settings-group', $option );
        }
    }
    
    function admin_create_menu() {
    
        // // create new top-level menu
        // add_menu_page( 
        // __('WP Feedback', EMU2_I18N_DOMAIN),
        // __('TDB', EMU2_I18N_DOMAIN),
        // 0,
        // FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php',
        // '',
        // plugins_url(FDB_PLUGIN_DIRECTORY.'/images/wp_feedback_icon.png'));
        // 
        // 
        // add_submenu_page( 
        // FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php',
        // __("What the hell", EMU2_I18N_DOMAIN),
        // __("Menu title2234", EMU2_I18N_DOMAIN),
        // 0,
        // FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php'
        // );
        // 
        // add_submenu_page( 
        // FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php',
        // __("HTML Title2 test", EMU2_I18N_DOMAIN),
        // __("Menu title 2 test", EMU2_I18N_DOMAIN),
        // 9,
        // FDB_PLUGIN_DIRECTORY.'/fdb_settings_page2.php'
        // );
    
        // or create options menu page
        add_options_page(__('WP Feedback', EMU2_I18N_DOMAIN), __("WP Feedback", EMU2_I18N_DOMAIN), 9,  FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php');
    
        // or create sub menu page
        // $parent_slug="index.php";	# For Dashboard
        #$parent_slug="edit.php";		# For Posts
        // more examples at http://codex.wordpress.org/Administration_Menus
        // add_submenu_page( $parent_slug, __("HTML Title 4", EMU2_I18N_DOMAIN), __("Menu title 4", EMU2_I18N_DOMAIN), 9, FDB_PLUGIN_DIRECTORY.'/fdb_settings_page.php');
    }
    
    function admin_enqueue_scripts() {
        wp_enqueue_script('wp_features_admin', $this->blog_url.'/wp-content/plugins/'.FDB_PLUGIN_DIRECTORY.'/js/wp_settings_admin.js');
    }
    
    function admin_icons(){
        include_once dirname(__FILE__).'/../templates/admin_icons.css.php';
    }
    
    function register_request_post_type() {
        
        $labels = array(
            'name'              =>  _x('Feedback', 'WP_Feedback top level menu'),
            'singular_name'     =>  _x('Feedback', 'WP_Feedback menu item'),
            'add_new'           =>  _x('Add New', 'Request'),
            'add_new_item'      =>  __('Add New Request'),
            'edit_item'         =>  __('Edit Request'),
            'new_item'          =>  __('New Request'),
            'view_item'         =>  __('View Suggestion'),
            'search_items'      =>  __('Search Requests'),
            'not_found'         =>  __('No Requests found'),
            'not_found_in_trash'=>  __('No Requests found in Trash'),
            'parent_item_colon' =>  ''
        );
    
        $supports = array('title', 'editor', 'author', 'custom-fields', 'revisions', 'excerpt');
    
        register_post_type( 'request',
            array(
              'labels' => $labels,
              'public' => true,
              'supports' => $supports
            )
        );
    }
    /* scripts and styles */
    function fdb_register_scripts() {        
        wp_enqueue_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
        wp_enqueue_script('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js', false, '1.8.2');
        wp_enqueue_script('jquery-validate', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', false, '1.9.0');
        
        // tdb -- option enabled
        wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css');
        wp_enqueue_style('wp-feedback', $this->blog_url.'/wp-content/plugins/' . FDB_PLUGIN_DIRECTORY . '/css/wp_feedback.css');
    }
    
    function fdb_insert_feedback_listing_page() {
        global $user;   
        
        // Create post object
        $feedback_page = array(
           'post_title'     =>  'Feedback',
           'post_content'   =>  '[display_feedback]',
           'post_status'    =>  'publish',
           'post_author'    =>  $user->ID, // tbd:  find user
           'comment_status' =>  'closed',
           //'post_category' => array(8,39)
           'post_type' => 'page'
        );
         
        // Insert the post into the database
        $post_id    =   wp_insert_post($feedback_page);
        update_post_meta($post_id, 'main_feedback_page', 1);
    }
    
    function fdb_insert_vote() {
        global $wpdb;
        
        $user   =   wp_get_current_user();
        
        $expected_post_keys     =   array('post_id', 'vote');
        $valid_post_conditions  =   $this->post_keys_exist($expected_post_keys);
        
        if($valid_post_conditions) {
        
            $post_id    =   wp_kses_post($_POST['post_id']);
            $vote       =   wp_kses_post($_POST['vote']);
            $vote       =   ($vote == 1) ? 1 : -1; 
           
            
            $table_name     =   $wpdb->prefix . "fdb_votes";
            $rows_affected  =   $wpdb->replace($table_name, 
                                    array(  'fdb_post_id'   =>  $post_id, 
                                            'fdb_user_id'   =>  $user->ID,
                                            'fdb_vote'      =>  $vote
                                            )
                                    );

            // update wordpress posting with voting total
            $votes      =   $this->fdb_get_vote($post_id);
            update_post_meta($post_id, 'votes', $votes);
            
            header("Content-Type: application/json; charset=UTF-8");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            
            echo json_encode(array('votes' => $votes));
            die();
        }
    }
    
    function fdb_get_vote($post_id) {
        global $wpdb;
        
        $table_name     =   $wpdb->prefix . "fdb_votes";
        $sql            =   $wpdb->prepare('SELECT IFNULL(SUM(fdb_vote), 0) FROM '.$table_name.' WHERE fdb_post_id = %d', $post_id);
        list($votes)    =   $wpdb->get_col($sql);
        
        return $votes;
    }
    
    function post_keys_exist($expected_post_keys) {
 
        $valid_post_conditions  =   true; /* (in_array($expected_post_keys, array_keys($_POST))) ? true : false; */
        
        foreach($expected_post_keys as $key) {
            
            if(!isset($_POST[$key])) {
                $valid_post_conditions  =   false;
            }   
        }
        return $valid_post_conditions;
    }
    
    function fdb_insert_request() {
        
        $expected_post_keys     =   array('user_name', 'request_title', 'request_summary');
        $valid_post_conditions  =   $this->post_keys_exist($expected_post_keys);

        if($valid_post_conditions) {
            
            // Create post object
            $my_post = array(
               'post_title'     =>  wp_kses_post($_POST['request_title']),
               'post_content'   =>  wp_kses_post($_POST['request_summary']),
               'post_status'    =>  'publish',
               'post_author'    =>  1, // tbd:  find user
               //'post_category' => array(8,39)
               'post_type' => 'request'
            );
             
            // Insert the post into the database
            $post_id    =   wp_insert_post($my_post);
            
            // Add custom meta-data
            if($post_id) {
                
                $current_user = wp_get_current_user();
                
                if($current_user->ID == 0) {
                    update_post_meta($post_id, 'anonymous_user_name', wp_kses_post($_POST['user_name']));
                    update_post_meta($post_id, 'user_id', false);
                } else {
                    update_post_meta($post_id, 'anonymous_user_name', false);
                    update_post_meta($post_id, 'user_id', $current_user->ID);
                }
                update_post_meta($post_id, 'votes', 0);
            }
            
            echo "success";
            
        } else {
            echo "incomplete post!";
        }
        
        die(); // booo!
    }
    
    function fdb_display_posts() {
        include_once dirname(__FILE__).'/../templates/display_requests.php';
    }
    
    function fdb_display_frontend() {        
        include_once dirname(__FILE__).'/../templates/display_feedback_form.php';
    }
    

    
    function insert_voting_frontend($request) {
        global $post;
        
        if($post->post_type == 'request') {
            
            $before    =  '<div id="request-vote">
                <div id="vote-up" title="'.$post->ID.'">&#x25B2</div>
                <div id="vote-count">'.$this->fdb_get_vote($post->ID).'</div>
                <div id="vote-processing"><img src="'.FDB_PLUGIN_URL.'/images/loading.gif" /></div>
                <div id="vote-down" title="'.$post->ID.'">&#x25BC;</div>
            </div>';
            
            return $before.$request;
        } 
        

        return $request;
    
    }
}
