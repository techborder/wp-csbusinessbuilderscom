<?php
final class Access_Ajax_Helper
{
    public static function init()
    {
        /*
         * AJAX calls.
         */
        add_action('wp_ajax_wpcf_access_save_settings', array(__CLASS__, 'wpcf_access_save_settings'));
        add_action('wp_ajax_wpcf_access_ajax_reset_to_default',  array(__CLASS__, 'wpcf_access_ajax_reset_to_default'));
        add_action('wp_ajax_wpcf_access_suggest_user', array(__CLASS__, 'wpcf_access_wpcf_access_suggest_user_ajax'));
        add_action('wp_ajax_wpcf_access_ajax_set_level', array(__CLASS__, 'wpcf_access_ajax_set_level'));
        add_action('wp_ajax_wpcf_access_add_role', array(__CLASS__, 'wpcf_access_add_role_ajax'));
        add_action('wp_ajax_wpcf_access_delete_role', array(__CLASS__, 'wpcf_access_delete_role_ajax'));
    }

    /**
     * Saves Access settings. 
     */
    public static function wpcf_access_save_settings() 
    {
        if (
            isset($_POST['_wpnonce']) &&
            wp_verify_nonce($_POST['_wpnonce'], 'wpcf-access-edit')
        ) 
        {
            //taccess_log($_POST['types_access']);
            
            $model = TAccess_Loader::get('MODEL/Access');
            
            //$isTypesActive = Access_Helper::wpcf_access_is_wpcf_active();
            
            $access_bypass_template="<div class='error'><p>".__("<strong>Warning:</strong> The %s <strong>%s</strong> uses the same name for singular name and plural name. Access can't control access to this object. Please use a different name for the singular and plural names.", 'wpcf_access')."</p></div>";
            $access_conflict_template="<div class='error'><p>".__("<strong>Warning:</strong> The %s <strong>%s</strong> uses capability names that conflict with default Wordpress capabilities. Access can not manage this entity, try changing entity's name and / or slug", 'wpcf_access')."</p></div>";
            $access_notices='';
            $_post_types=Access_Helper::wpcf_object_to_array( $model->getPostTypes() );
            $_taxonomies=Access_Helper::wpcf_object_to_array( $model->getTaxonomies() );
            
            //taccess_log($_taxonomies);
            
            // start empty
            $settings_access_types_previous = $model->getAccessTypes();
            $settings_access_taxs_previous = $model->getAccessTaxonomies();
            $settings_access_types = array();
            $settings_access_taxs = array();
            
            // Post Types
            if (!empty($_POST['types_access']['types'])) 
            {
                $caps = Access_Helper::wpcf_access_types_caps_predefined();
                foreach ($_POST['types_access']['types'] as $type => $data) 
                {
                    $mode = isset($data['mode']) ? $data['mode'] : 'not_managed';
                    // Use saved if any and not_managed
                    if ($data['mode'] == 'not_managed'
                            && isset($settings_access_types_previous[$type])) {
                        $data = $settings_access_types_previous[$type];
                    }
                    $data['mode'] = $mode;
                    $data['permissions'] = Access_Helper::wpcf_access_parse_permissions($data, $caps);
                    //taccess_log($data['permissions']);
                    
                    if (
                        /*!Access_Helper::wpcf_is_object_valid('type', $_post_types[$type])*/
                        isset($_post_types[$type]['__accessIsNameValid']) && !$_post_types[$type]['__accessIsNameValid']
                    ) 
                    {
                        $data['mode'] = 'not_managed';
                        $access_notices.=sprintf($access_bypass_template,__('Post Type','wpcf_access'),$_post_types[$type]['labels']['singular_name']);
                    }
                    
                    if (
                        /*isset($_post_types[$type]['cap']) && Access_Helper::wpcf_check_cap_conflict(array_values($_post_types[$type]['cap']))*/
                        isset($_post_types[$type]['__accessIsCapValid']) && !$_post_types[$type]['__accessIsCapValid']
                    )
                    {
                        $data['mode'] = 'not_managed';
                        $access_notices.=sprintf($access_conflict_template,__('Post Type','wpcf_access'),$_post_types[$type]['labels']['singular_name']);
                    }
                    $settings_access_types[$type] = $data;
                }
                //taccess_log($settings_access_types);
                // update settings
                $model->updateAccessTypes($settings_access_types);
                unset($settings_access_types_previous);
            }
            
            // Taxonomies
            $caps = Access_Helper::wpcf_access_tax_caps();
            // when a taxonomy is unchecked, no $_POST data exist, so loop over all existing taxonomies, instead of $_POST data
            foreach ($_taxonomies as $tax=>$_taxdata) 
            {
                if (isset($_POST['types_access']['tax']) && isset($_POST['types_access']['tax'][$tax])) 
                {
                    $data=$_POST['types_access']['tax'][$tax];
                    //foreach ($_POST['types_access']['tax'] as $tax => $data) {
                    if (!isset($data['not_managed']))
                        $data['mode'] = 'not_managed';
                    
                    if (!isset($data['mode']))
                        $data['mode'] = 'permissions';
                    
                    $data['mode'] = isset($data['mode']) ? $data['mode'] : 'not_managed';
                    
                    $data['mode'] = Access_Helper::wpcf_access_get_taxonomy_mode($tax,  $data['mode']);
                    
                    // Prevent overwriting
                    if ($data['mode'] == 'not_managed') 
                    {
                        if (isset($settings_access_taxs_previous[$tax]) /*&& isset($settings_access_taxs_previous[$tax]['permissions'])*/)
                        {
                            //$data['permissions'] = $settings_access_taxs_previous[$tax]['permissions'];
                            $data = $settings_access_taxs_previous[$tax];
                            $data['mode'] = 'not_managed';
                        }
                    }
                    elseif ($data['mode'] == 'follow') 
                    {
                        if (!isset($data['__permissions']))
                        {
                            // add this here since it is needed elsewhere
                            // and it is missing :P
                            $data['__permissions'] = Access_Helper::wpcf_get_taxs_caps_default(); /*array(
                                'manage_terms' => array(
                                        'role' => 'administrator'
                                ),
                                'edit_terms' => array(
                                        'role' => 'administrator'
                                ),
                                'delete_terms' => array(
                                        'role' => 'administrator'
                                ),
                                'assign_terms' => array(
                                        'role' => 'administrator'
                                )
                            );*/
                        }
                        //taccess_log($_taxdata);
                        $tax_post_type = array_shift(array_values($_taxdata['object_type']));
                        $follow_caps = array();
                        // if parent post type managed by access, and tax is same as parent
                        // translate and hardcode the post type capabilities to associated tax capabilties
                        if (isset($settings_access_types[$tax_post_type]) && 'permissions'==$settings_access_types[$tax_post_type]['mode'])
                        {
                            $follow_caps = Access_Helper::wpcf_types_to_tax_caps($tax, $_taxdata, $settings_access_types[$tax_post_type]);
                        }
                        //taccess_log(array($tax, $follow_caps));
                        if (!empty($follow_caps))
                        {
                            $data['permissions'] = $follow_caps;
                        }
                        else
                        {
                            $data['mode']='not_managed';
                        }
                        //taccess_log(array($tax_post_type, $follow_caps, $settings_access_types[$tax_post_type]['permissions']));
                        
                        /*if (isset($settings_access_taxs[$tax]) && isset($settings_access_taxs[$tax]['permissions']))
                            $data['permissions'] = $settings_access_taxs[$tax]['permissions'];*/
                    }
                    //taccess_log($data['permissions']);
                    $data['permissions'] = Access_Helper::wpcf_access_parse_permissions($data,  $caps);
                    //taccess_log(array($tax, $data));
                    
                    if (
                        /*!Access_Helper::wpcf_is_object_valid('taxonomy', $_taxonomies[$tax])*/
                        isset($_taxonomies[$tax]['__accessIsNameValid']) && !$_taxonomies[$tax]['__accessIsNameValid']
                    ) 
                    {
                        $data['mode'] = 'not_managed';
                        $access_notices.=sprintf($access_bypass_template,__('Taxonomy','wpcf_access'),$_taxonomies[$tax]['labels']['singular_name']);
                    }
                    if (
                        /*isset($_taxonomies[$tax]['cap']) && Access_Helper::wpcf_check_cap_conflict(array_values($_taxonomies[$tax]['cap']))*/ 
                        isset($_taxonomies[$tax]['__accessIsCapValid']) && !$_taxonomies[$tax]['__accessIsCapValid']
                    )
                    {
                        $data['mode'] = 'not_managed';
                        $access_notices.=sprintf($access_conflict_template,__('Taxonomy','wpcf_access'),$_taxonomies[$tax]['labels']['singular_name']);
                    }
                    
                    $settings_access_taxs[$tax] = $data;
                }
                else 
                {
                    $data=array();
                    $data['mode'] = 'not_managed';
                    
                    // Prevent overwriting
                    if ($data['mode'] == 'not_managed') 
                    {
                        if (isset($settings_access_taxs_previous[$tax]) /*&& isset($settings_access_taxs_previous[$tax]['permissions'])*/)
                        {
                            //$data['permissions'] = $settings_access_taxs_previous[$tax]['permissions'];
                            $data = $settings_access_taxs_previous[$tax];
                            $data['mode'] = 'not_managed';
                        }
                    }
                    /*elseif ($data['mode'] == 'follow') 
                    {
                        if (isset($settings_access_taxs[$tax]) && isset($settings_access_taxs[$tax]['permissions']))
                            $data['permissions'] = $settings_access[$tax]['permissions'];
                    }*/
                    $data['permissions'] = Access_Helper::wpcf_access_parse_permissions($data, $caps);
                    
                    $settings_access_taxs[$tax] = $data;
                }
            }
            //taccess_log($settings_access_taxs);
            // update settings
            $model->updateAccessTaxonomies($settings_access_taxs);
            unset($settings_access_taxs_previous);
            
            // 3rd-Party
            if (!empty($_POST['types_access'])) 
            {
                // start empty
                //$settings_access_thirdparty_previous = $model->getAccessThirdParty();
                $third_party = array();
                foreach ($_POST['types_access'] as $area_id => $area_data) 
                {
                    // Skip Types
                    if ($area_id == 'types' || $area_id == 'tax') 
                    {
                        //unset($third_party[$area_id]);
                        continue;
                    }
                    $third_party[$area_id]=array();
                    foreach ($area_data as $group => $group_data) 
                    {
                        // Set user IDs
                        $group_data['permissions'] = Access_Helper::wpcf_access_parse_permissions($group_data,  $caps, true);
                        
                        $third_party[$area_id][$group] = $group_data;
                        $third_party[$area_id][$group]['mode'] = 'permissions';
                    }
                }
                //taccess_log($third_party);
                // update settings
                $model->updateAccessThirdParty($third_party);
            }
            
            // Roles
            if (!empty($_POST['roles'])) 
            {
                $access_roles = $model->getAccessRoles();
                foreach ($_POST['roles'] as $role => $level) 
                {
                    $role_data = get_role($role);
                    if (!empty(/*$role*/$role_data)) 
                    {
                        $level = intval($level);
                        for ($index = 0; $index < 11; $index++) 
                        {
                            if ($index <= $level)
                                $role_data->add_cap('level_' . $index, 1);
                            else
                                $role_data->remove_cap('level_' . $index);
                            
                            if (isset($access_roles[$role]))
                            {
                                if (isset($access_roles[$role]['caps']))
                                {
                                    if ($index <= $level)
                                    {
                                        $access_roles[$role]['caps']['level_' . $index]=true;
                                    }
                                    else
                                    {
                                        unset($access_roles[$role]['caps']['level_' . $index]);
                                    }
                                }
                            }
                        }
                    }
                }
                //taccess_log(array($_POST['roles'], $access_roles));
                $model->updateAccessRoles($access_roles);
            }
            
            if (defined('DOING_AJAX')) 
            {
                do_action('types_access_save_settings');
                echo "<div class='updated'><p>" . __('Access rules saved', 'wpcf_access') . "</p></div>";
                echo $access_notices;
                die();
            }
        }
    }

    /**
     * AJAX revert to default call. 
     */
    public static function wpcf_access_ajax_reset_to_default() 
    {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'],
                        'wpcf_access_ajax_reset_to_default')) {
            die('verification failed');
        }
        if ($_GET['type'] == 'type') {
            $caps = Access_Helper::wpcf_access_types_caps_predefined();
        } else if ($_GET['type'] == 'tax') {
            $caps = Access_Helper::wpcf_access_tax_caps();
        }
        if (!empty($caps) && isset($_GET['button_id'])) {
            $output = array();
            foreach ($caps as $cap => $cap_data) {
                $output[$cap] = $cap_data['role'];
            }
            echo json_encode(array(
                'output' => $output,
                'type' => $_GET['type'],
                'button_id' => $_GET['button_id'],
            ));
        }
        die();
    }

    /**
     * AJAX set levels default call. 
     */
    public static function wpcf_access_ajax_set_level() 
    {
        if (!isset($_POST['_wpnonce'])
                || !wp_verify_nonce($_POST['_wpnonce'], 'execute')) {
            die('verification failed');
        }
        TAccess_Loader::load('CLASS/Admin_Edit');
        $model = TAccess_Loader::get('MODEL/Access');
        
        if (!empty($_POST['roles'])) 
        {
            $access_roles = $model->getAccessRoles();
            foreach ($_POST['roles'] as $role => $level) 
            {
                $role_data = get_role($role);
                if (!empty(/*$role*/$role_data)) 
                {
                    $level = intval($level);
                    for ($index = 0; $index < 11; $index++) 
                    {
                        if ($index <= $level) {
                            $role_data->add_cap('level_' . $index, 1);
                        } else {
                            $role_data->remove_cap('level_' . $index);
                        }
                        if (isset($access_roles[$role]))
                        {
                            if (isset($access_roles[$role]['caps']))
                            {
                                if ($index <= $level)
                                {
                                    $access_roles[$role]['caps']['level_' . $index]=true;
                                }
                                else
                                {
                                    unset($access_roles[$role]['caps']['level_' . $index]);
                                }
                            }
                        }
                    }
                }
            }
            //taccess_log(array($_POST['roles'], $access_roles));
            $model->updateAccessRoles($access_roles);
        }
        echo json_encode(array(
            'output' => Access_Admin_Edit::wpcf_access_admin_set_custom_roles_level_form( 
                            Access_Helper::wpcf_get_editable_roles(),
                            true
                        ),
        ));
        die();
    }

    /**
     * Suggest user AJAX. 
     */
    public static function wpcf_access_wpcf_access_suggest_user_ajax() 
    {
        global $wpdb;
        $users = array();
        $q = $wpdb->escape(trim($_POST['q']));
        $q = like_escape($q);
        $found = $wpdb->get_results("SELECT ID, display_name, user_login FROM $wpdb->users WHERE user_nicename LIKE '%%$q%%' OR user_login LIKE '%%$q%%' OR display_name LIKE '%%$q%%' OR user_email LIKE '%%$q%%' LIMIT 10");
        if (!empty($found)) {
            foreach ($found as $user) {
                $users[$user->ID] = $user->display_name . ' (' . $user->user_login . ')';
            }
        }
        echo json_encode($users);
        die();
    }

    /**
     * Adds new custom role. 
     */
    public static function wpcf_access_add_role_ajax() 
    {
        TAccess_Loader::load('CLASS/Admin_Edit');
        $model = TAccess_Loader::get('MODEL/Access');
        $access_roles = $model->getAccessRoles();
        $capabilities = array('level_0' => true, 'read' => true);
        $caps = Access_Helper::wpcf_access_types_caps();
        foreach ($caps as $cap => $data) 
        {
            if ($data['predefined'] == 'read') 
            {
                $capabilities[$cap] = true;
            }
        }
        $role_slug = str_replace('-', '_', sanitize_title($_POST['role']));
        $success = add_role($role_slug, $_POST['role'], $capabilities);
        if (!is_null($success))
        {
            $access_roles[$role_slug]=array(
                'name'=> $_POST['role'],
                'caps'=> $capabilities
            );
            $model->updateAccessRoles($access_roles);
        }
        //taccess_log(array($_POST['role'], $access_roles));
        echo json_encode(array(
            'error' => is_null($success) ? 'true' : 'false',
            'output' => is_null($success) ? '<div class="error"><p>' . __('Role already exists',
                            'wpcf_access') . '</p></div>' : Access_Admin_Edit::wpcf_access_admin_set_custom_roles_level_form(Access_Helper::wpcf_get_editable_roles()),
        ));
        die();
    }

    /**
     * Deletes custom role. 
     */
    public static function wpcf_access_delete_role_ajax() 
    {
        if (!isset($_POST['wpcf_access_delete_role_nonce'])
                || !wp_verify_nonce($_POST['wpcf_access_delete_role_nonce'],
                        'delete_role')) {
            die('verification failed');
        }
        
        if (in_array(strtolower(trim($_POST['wpcf_access_delete_role'])),
                        Access_Helper::wpcf_get_default_roles())) 
        {
            $error = 'true';
            $output = '<div class="error"><p>' . __('Role can not be deleted',
                            'wpcf_access') . '</p></div>';
        } 
        else 
        {
            TAccess_Loader::load('CLASS/Admin_Edit');
            $model = TAccess_Loader::get('MODEL/Access');
            $access_roles = $model->getAccessRoles();
            if ($_POST['wpcf_reassign'] != 'ignore') 
            {
                $users = get_users('role=' . $_POST['wpcf_access_delete_role']);
                foreach ($users as $user) 
                {
                    $user = new WP_User($user->ID);
                    $user->add_role($_POST['wpcf_reassign']);
                }
            }
            remove_role($_POST['wpcf_access_delete_role']);
            if (isset($access_roles[$_POST['wpcf_access_delete_role']]))
            {
                unset($access_roles[$_POST['wpcf_access_delete_role']]);
            }
            //taccess_log(array($_POST['wpcf_access_delete_role'], $access_roles));
            $model->updateAccessRoles($access_roles);
            
            $error = 'false';
            $output = Access_Admin_Edit::wpcf_access_admin_set_custom_roles_level_form(Access_Helper::wpcf_get_editable_roles());
        }
        echo json_encode(array(
            'error' => $error,
            'output' => $output,
        ));
        die();
    }
}

// init on load
Access_Ajax_Helper::init();