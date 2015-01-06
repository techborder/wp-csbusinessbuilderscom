<?php
final class Access_Admin_Edit
{
/*
 * Edit access page.
 */

/**
 * Admin page form.
 */
public static function wpcf_access_admin_edit_access($enabled = true) 
{
    global $wpcf_access;
    $model = TAccess_Loader::get('MODEL/Access');
    $roles = Access_Helper::wpcf_get_editable_roles();
    $shortcuts = array();
    $output = '';
    $output .= '<form id="wpcf_access_admin_form" method="post" action="">';
    
    $access_bypass_template="<div class='error'><p>".__("<strong>Warning:</strong> The %s <strong>%s</strong> uses the same name for singular name and plural name. Access can't control access to this object. Please use a different name for the singular and plural names.", 'wpcf_access')."</p></div>";
    $access_conflict_template="<div class='error'><p>".__("<strong>Warning:</strong> The %s <strong>%s</strong> uses capability names that conflict with default Wordpress capabilities. Access can not manage this entity, try changing entity's name and / or slug", 'wpcf_access')."</p></div>";
    $access_notices='';
    
    //$isTypesActive = Access_Helper::wpcf_access_is_wpcf_active();
    
    // Types
    $settings_access = $model->getAccessTypes();
    $types = $model->getPostTypes();
    $types = Access_Helper::wpcf_object_to_array($types);
    //taccess_log($types);
    foreach ($types as $type_slug => $type_data) 
    {
        // filter types, excluding types that do not have different plural and singular names
        if (
            /*!Access_Helper::wpcf_is_object_valid('type', $type_data)*/
            isset($type_data['__accessIsNameValid']) && !$type_data['__accessIsNameValid']
        ) 
        {
            $access_notices.=sprintf($access_bypass_template,__('Post Type','wpcf_access'),$type_data['labels']['singular_name']);
            unset($types[$type_slug]);
            continue;
        }
        
        if (
            /*isset($type_data['cap']) && Access_Helper::wpcf_check_cap_conflict(array_values($type_data['cap']))*/
            isset($type_data['__accessIsCapValid']) && !$type_data['__accessIsCapValid']
        )
        {
            $access_notices.=sprintf($access_conflict_template,__('Post Type','wpcf_access'),$type_data['labels']['singular_name']);
            unset($types[$type_slug]);
            continue;
        }
        
        if (isset($settings_access[$type_slug])) {
            $types[$type_slug]['_wpcf_access_capabilities'] = $settings_access[$type_slug];
        }
        
        if (!empty($type_data/*->*/['_wpcf_access_inherits_post_cap'])) {
            $types[$type_slug]['_wpcf_access_inherits_post_cap'] = 1;
        }
    }
    
    // Put Posts and Pages in front
    $temp = array('page', 'post');
    foreach ($temp as $t) 
    {
        if (isset($types[$t])) 
        {
            $clone = array($t => $types[$t]);
            unset($types[$t]);
            $types = $clone + $types;
        }
    }

    if (!empty($types)) 
    {
        $output .= '<h3>' . __('Custom Types', 'wpcf_access') . '</h3>';
        foreach ($types as $type_slug => $type_data) {
            if ($type_data['public'] === 'hidden') {
                continue;
            }

            if ($type_slug == 'view-template' || $type_slug == 'view' || $type_slug == 'cred-form') {
                // Don't list Views and View templates separately.
                // Don't list CRED form post types.
                continue;
            }
            
            // Set data
            $mode = isset($type_data['_wpcf_access_capabilities']['mode']) ? $type_data['_wpcf_access_capabilities']['mode'] : 'not_managed';

            $output .= '<a name="' . $type_slug . '">&nbsp;</a><br />';
            $shortcuts[__('Post types', 'wpcf_access')][] = array($type_data['labels']['name'], $type_slug);
            $output .= '<div class="wpcf-access-type-item">';
            $output .= '<strong>' . $type_data['labels']['name'] . '</strong>';
            $output .= '<div class="wpcf-access-mode">';
            $output .= '<label><input type="checkbox" value="permissions"'
                    . ' onclick="wpcfAccess.Enable(jQuery(this));"';
            if (!$enabled) {
                $output .= 'disabled="disabled" readonly="readonly" ';
            }
            $output .= $mode != 'not_managed' ? 'checked="checked" />' : ' />';
            $output .= '<input type="hidden" class="wpcf-enable-set" '
                    . 'name="types_access[types]['
                    . $type_slug . '][mode]" value="' . $mode . '" />';
            $output .= '&nbsp;' . __('Managed by Access', 'wpcf_access') . '</label>';

            // Warning fallback
            if ((empty($type_data['_wpcf_access_outsider'])
                    || !empty($type_data['_wpcf_access_inherits_post_cap']))
                    && !in_array($type_slug, array('post', 'page'))) {
                $output .= '<div class="warning-fallback"';
                if ($mode != 'not_managed') {
                    $output .= ' style="display:none;"';
                }
                $output .= '><p>' . __('This post type will inherit the same access rights as the standard WordPress Post when not Managed by Access.',
                                'wpcf_access') . '</p></div>';
            }

            $permissions = !empty($type_data['_wpcf_access_capabilities']['permissions']) ? $type_data['_wpcf_access_capabilities']['permissions'] : array();
            $output .= self::wpcf_access_permissions_table($roles, $permissions,
                    Access_Helper::wpcf_access_types_caps_predefined(), 'types', $type_slug,
                    $enabled, $mode != 'not_managed');
            $output .= '</div><!-- wpcf-access-mode -->';
            $output .= self::wpcf_access_submit_button($enabled,
                    $mode != 'not_managed');
            $output .= '&nbsp;' . self::wpcf_access_reset_button($type_slug, 'type',
                            $enabled, $mode != 'not_managed');
            $output .= '<a href="#wpcf-access-top-anchor" style="float:right;">'. __('Back to Top', 'wpcf_access') .'</a>';
            $output .= '<div style="clear:both;"></div></div><!-- wpcf-access-type-item -->';
        }
    }

    // Taxonomies
    $supports_check = array();
    $settings_access = $model->getAccessTaxonomies();
    $taxonomies = $model->getTaxonomies();
    $taxonomies = Access_Helper::wpcf_object_to_array($taxonomies);
    //taccess_log($taxonomies);
    foreach ($taxonomies as $tax_slug => $tax_data) 
    {
        // filter taxonomies, excluding tax that do not have different plural and singular names
        if (
            /*!Access_Helper::wpcf_is_object_valid('taxonomy', $tax_data)*/
            isset($tax_data['__accessIsNameValid']) && !$tax_data['__accessIsNameValid']
        ) 
        {
            $access_notices.=sprintf($access_bypass_template,__('Taxonomy','wpcf_access'),$tax_data['labels']['singular_name']);
            unset($taxonomies[$tax_slug]);
            continue;
        }
        if (
            /*isset($tax_data['cap']) && Access_Helper::wpcf_check_cap_conflict(array_values($tax_data['cap']))*/ 
            isset($tax_data['__accessIsCapValid']) && !$tax_data['__accessIsCapValid']
        )
        {
            $access_notices.=sprintf($access_conflict_template,__('Taxonomy','wpcf_access'),$tax_data['labels']['singular_name']);
            unset($taxonomies[$tax_slug]);
            continue;
        }
        
        $taxonomies[$tax_slug]['supports'] = array_flip($tax_data['object_type']);
        
        if (isset($settings_access[$tax_slug]))
        {
            $taxonomies[$tax_slug]['_wpcf_access_capabilities'] = $settings_access[$tax_slug];
        }
        
        /*if ('product_shipping_class'==$tax_slug)        
        {
            taccess_log($taxonomies[$tax_slug]);
        }*/
        
        if ($enabled) 
        {
            $mode = isset($tax_data['_wpcf_access_capabilities']['mode']) ? $tax_data['_wpcf_access_capabilities']['mode'] : 'follow'/*'not_managed'*/;
            // Only check if in 'follow' mode
    //            if ($mode != 'follow' || empty($tax_data['supports'])) {
            if (empty($tax_data['supports']))
                continue;
            
            foreach ($tax_data['supports'] as $supports_type => $true) 
            {
                if (!isset($types[$supports_type]['_wpcf_access_capabilities']['mode']))
                    continue;
                
                $mode = $types[$supports_type]['_wpcf_access_capabilities']['mode'];
                
                if (!isset($types[$supports_type]['_wpcf_access_capabilities'][$mode]))
                    continue;
                
                $supports_check[$tax_slug][md5($mode . serialize($types[$supports_type]['_wpcf_access_capabilities'][$mode]))][] = $types[$supports_type]['labels']['name'];
            }
        }
    }

    // Put Categories and Tags in front
    $temp = array('post_tag', 'category');
    foreach ($temp as $t) 
    {
        if (isset($taxonomies[$t])) 
        {
            $clone = array($t => $taxonomies[$t]);
            unset($taxonomies[$t]);
            $taxonomies = $clone + $taxonomies;
        }
    }

    if (!empty($taxonomies)) 
    {
        $output .= '<br /><br /><h3>' . __('Custom Taxonomies', 'wpcf_access') . '</h3>';
        foreach ($taxonomies as $tax_slug => $tax_data) 
        {
            $mode = 'not_managed';
            if ($tax_data['public'] === 'hidden') {
                continue;
            }
            // Set data
            if (isset($tax_data['_wpcf_access_capabilities']['mode']))
            {
                $mode=$tax_data['_wpcf_access_capabilities']['mode'];
            }
            elseif ($enabled)
            {
                $mode = Access_Helper::wpcf_access_get_taxonomy_mode($tax_slug, $mode);
            }
            else
            {
                $mode='not_managed';
            }
        /*if ('product_shipping_class'==$tax_slug)        
        {
            taccess_log(array($mode, $taxonomies[$tax_slug]));
        }*/
            /*$mode = isset($tax_data['_wpcf_access_capabilities']['mode']) ? $tax_data['_wpcf_access_capabilities']['mode'] : 'not_managed';
            if ($enabled) {
                $mode = wpcf_access_get_taxonomy_mode($tax_slug, $mode);
            }*/
            // For built-in set default to 'not_managed'
            if (in_array($tax_slug, array('category', 'post_tag'))) {
                $mode = isset($tax_data['_wpcf_access_capabilities']['mode']) ? $tax_data['_wpcf_access_capabilities']['mode'] : 'not_managed';
            }
            $custom_data = Access_Helper::wpcf_access_tax_caps();
            if (isset($tax_data['_wpcf_access_capabilities']['permissions'])) 
            {
                foreach ($tax_data['_wpcf_access_capabilities']['permissions'] as $cap_slug => $cap_data) 
                {
                    $custom_data[$cap_slug]['role'] = $cap_data['role'];
                    $custom_data[$cap_slug]['users'] = isset($cap_data['users']) ? $cap_data['users'] : array();
                }
            }

            $output .= '<a name="' . $tax_slug . '">&nbsp;</a><br />';
            $shortcuts[__('Taxonomy', 'wpcf_access')][] = array($tax_data['labels']['name'], $tax_slug);
            $output .= '<div class="wpcf-access-type-item">';
            $output .= '<strong>' . $tax_data['labels']['name'] . '</strong>';
            // Add warning if shared and settings are different
            $disable_same_as_parent = false;
            if ($enabled && isset($supports_check[$tax_slug])
                    && count($supports_check[$tax_slug]) > 1) 
            {
                $txt = array();
                foreach ($supports_check[$tax_slug] as $sc_tax_md5 => $sc_tax_md5_data) 
                {
                    $txt = array_merge($txt, $sc_tax_md5_data);
                }
                $last_element = array_pop($txt);
                $warning = '<br /><img src="' . TACCESS_ASSETS_URL . '/images/warning.png" style="position:relative;top:2px;" />&nbsp;' . sprintf(__('You need to manually set the access rules for taxonomy %s. That taxonomy is shared between several post types that have different access rules.'),
                                $tax_data['labels']['name'],
                                implode(', ', $txt), $last_element);
                $output .= $warning;
                $disable_same_as_parent = true;
            }

            $output .= '<div class="wpcf-access-mode">';

            // Managed checkbox
            $output .= '<label><input type="checkbox" class="not-managed" name="types_access[tax]['
                    . $tax_slug . '][not_managed]" value="1"';
            if (!$enabled) {
                $output .= ' disabled="disabled" readonly="readonly"';
            }
            $output .= $mode != 'not_managed' ? ' checked="checked"' : '';
            $output .= '/>&nbsp;' . __('Managed by Access', 'wpcf_access') . '</label>';

            $output .= '<br />';

            // 'Same as parent' checkbox
            $output .= '<label><input type="checkbox" class="follow" name="types_access[tax]['
                    . $tax_slug . '][mode]" value="follow"';
            if (!$enabled) {
                $output .= ' disabled="disabled" readonly="readonly" checked="checked"';
            } else if ($disable_same_as_parent) {
                $output .= ' disabled="disabled" readonly="readonly"';
            } else {
                $output .= $mode == 'follow' ? ' checked="checked"' : '';
            }
            $output .= ' />&nbsp;' . __('Same as Parent', 'wpcf_access') . '</label>';

            $output .= '<div class="wpcf-access-mode-custom">';
            $output .= self::wpcf_access_permissions_table($roles, $custom_data,
                    $custom_data, 'tax', $tax_slug, $enabled,
                    $mode != 'not_managed');
            $output .= '</div>';
            $output .= '</div><!-- wpcf-access-mode -->';
            $output .= self::wpcf_access_submit_button($enabled,
                    $mode != 'not_managed');
            $output .= '&nbsp;' . self::wpcf_access_reset_button($tax_slug, 'tax',
                            $enabled);
            $output .= '<a href="#wpcf-access-top-anchor" style="float:right;">'. __('Back to Top', 'wpcf_access') .'</a>';
            $output .= '<div style="clear:both;"></div></div><!-- wpcf-access-type-item -->';
        }
    }

    // Allow 3rd party
    $third_party = $model->getAccessThirdParty();
    $areas = apply_filters('types-access-area', array());
    foreach ($areas as $area) 
    {
        // Do not allow 'types' ID
        if (in_array($area['id'], array('types', 'tax')))
            continue;
        
        // make all groups of same area appear on same line in shortcuts
        $shortcuts[$area['name']]=array();
        $output .= '<br /><br /><h3>' . $area['name'] . '</h3>';
        $groups = apply_filters('types-access-group', array(), $area['id']);
        foreach ($groups as $group) 
        {
            //$shortcuts[$area['name']][$group['name']]=array();
            $shortcuts[$area['name']]/*[$group['name']]*/[] = array($group['name'], $group['id']);
            $output .= '<a name="' . $group['id'] . '">&nbsp;</a><br />';
            $output .= '<div class="wpcf-access-type-item">';
            $output .= '<strong>' . $group['name'] . '</strong>';
            $output .= '<div class="wpcf-access-mode">';
            $caps = array();
            $caps_filter = apply_filters('types-access-cap', array(), $area['id'],
                    $group['id']);
            $saved_data = array();
            foreach ($caps_filter as $cap_slug => $cap) 
            {
                $caps[$cap['cap_id']] = $cap;
                if (isset($cap['default_role'])) {
                    $caps[$cap['cap_id']]['role'] = $cap['role'] = $cap['default_role'];
                }
                $saved_data[$cap['cap_id']] =
                        isset($third_party[$area['id']][$group['id']]['permissions'][$cap['cap_id']]) ?
                        $third_party[$area['id']][$group['id']]['permissions'][$cap['cap_id']] : array('role' => $cap['role']);
            }
            // Add registered via other hook
            if (!empty($wpcf_access->third_party[$area['id']][$group['id']]['permissions'])) {
                foreach ($wpcf_access->third_party[$area['id']][$group['id']]['permissions'] as $cap_slug => $cap) 
                {
                    // Don't allow duplicates
                    if (isset($caps[$cap['cap_id']])) {
                        unset($wpcf_access->third_party[$area['id']][$group['id']]['permissions'][$cap_slug]);
                        continue;
                    }
                    $saved_data[$cap['cap_id']] = $cap['saved_data'];
                    $caps[$cap['cap_id']] = $cap;
                }
            }
            if (isset($cap['style']) && $cap['style'] == 'dropdown') 
            {
            } 
            else 
            {
                $output .= self::wpcf_access_permissions_table($roles, $saved_data,
                        $caps, $area['id'], $group['id'], $enabled);
            }

            $output .= self::wpcf_access_submit_button($enabled, true);
            $output .= '<a href="#wpcf-access-top-anchor" style="float:right;">'. __('Back to Top', 'wpcf_access') .'</a>';
            $output .= '<div style="clear:both;"></div>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    // Custom roles
    $output .= '<a name="custom-roles"></a><br /><br />';
    $output .= '<h3>' . __('Custom Roles', 'wpcf_access') . '</h3>';
    $output .= self::wpcf_access_admin_set_custom_roles_level_form($roles, $enabled);
    $output .= wp_nonce_field('wpcf-access-edit', '_wpnonce', true, false);
    $output .= '<input type="hidden" name="action" value="wpcf_access_save_settings" />';
    $output .= '</form>';
    $output .= '<a href="#wpcf-access-top-anchor" style="float:right;">'. __('Back to Top', 'wpcf_access') .'</a>';
    $output .= '<div style="clear:both;"></div>';

    $output .= '<br /><br />' . self::wpcf_access_new_role_form($enabled);

    // Shortcuts
    $shortmenus = '';
    if (!empty($shortcuts)) 
    {
        $shortmenus .= '<h3 id="wpcf-access-top-anchor">' . __('On this page', 'wpcf_access') . '</h3>';
        foreach ($shortcuts as $section => $items) 
        {
            $shortmenu = '';
            if (!empty($items)) 
            {
                $shortmenu .= '<div class="wpcf-access-shortcuts-wrappet"><span class="wpcf-access-shortcut-section">'
                        . $section . '</span>: ';
                foreach ($items as $item) 
                {
                    $shortmenu .= '&nbsp;&nbsp;<a href="#' . $item[1]
                            . '" class="wpcf-access-shortcuts">' . $item[0]
                            . '</a>';
                }
                $shortmenus .= rtrim($shortmenu, ',') . '</div>'; // <br />
            }
        }
        $shortmenus .= '<br /><br />';
    }

    // Link to wp-types.com Access home URL
    $link_to_manual = '<a href="http://wp-types.com/documentation/user-guides/#Access" title="'
            . __('Access Manuals &raquo;') . '" target="_blank" '
            . 'class="wpcf-access-link-to-manual" style="display:block;font-weight:bold;background-image: url(\'' . TACCESS_ASSETS_URL . '/images/question.png\');background-repeat: no-repeat;text-indent: 18px;">'
            . __('Access Manuals &raquo;', 'wpcf_access') . '</a>';

    echo $link_to_manual . '<div id="wpcf_access_notices">' . $access_notices . '</div>' . $shortmenus . $output;
}

/**
 * Renders dropdown with editable roles.
 * 
 * @param type $roles
 * @param type $name
 * @param type $data
 * @return string 
 */
public static function wpcf_access_admin_roles_dropdown($roles, $name, $data = array(),
        $dummy = false, $enabled = true, $exclude = array()) {
    $default_roles = Access_Helper::wpcf_get_default_roles();
    $output = '';
    $output .= '<select name="' . $name . '"';
    $output .= isset($data['predefined']) ? 'class="wpcf-access-predefied-'
            . $data['predefined'] . '">' : '>';
    if ($dummy) {
        $output .= "\n\t<option";
        if (empty($data)) {
            $output .= ' selected="selected" disabled="disabled"';
        }
        $output .= ' value="0">' . $dummy . '</option>';
    }
    foreach ($roles as $role => $details) 
    {
        if (in_array($role, $exclude)) {
            continue;
        }
        if (in_array($role, $default_roles))
            $title = translate_user_role($details['name']);
        else
            $title = taccess_t($details['name'], $details['name']);
            
        $output .= "\n\t<option";
        if (isset($data['role']) && $data['role'] == $role) {
            $output .= ' selected="selected"';
        }
        if (!$enabled) {
            $output .= ' disabled="disabled"';
        }
        $output .= ' value="' . esc_attr($role) . "\">$title</option>";
    }
    // For now, let's add Guest only for read-only
    if (isset($data['predefined']) && $data['predefined'] == 'read-only') 
    {
        $output .= "\n\t<option";
        if (isset($data['role']) && $data['role'] == 'guest') {
            $output .= ' selected="selected"';
        }
        if (!$enabled) {
            $output .= ' disabled="disabled"';
        }
        $output .= ' value="guest">' . __('Guest', 'wp_access') . '</option>';
    }
    $output .= '</select>';
    return $output;
}

/**
 * Auto-suggest users search.
 * 
 * @param type $data
 * @param type $name
 * @return string 
 */
public static function wpcf_access_admin_users_form($data, $name, $enabled = true,
        $managed = true) {
    $output = '';
    $output .= self::wpcf_access_suggest_user($enabled, $managed);
    $output .= '<div class="wpcf-access-user-list">';
    if ($enabled && isset($data['users']) && is_array($data['users'])) 
    {
        foreach ($data['users'] as $user_id) 
        {
            $user = get_userdata($user_id);
            if (!empty($user)) 
            {
                $output .= '<div class="wpcf-access-remove-user-wrapper"><a href="javascript:;" class="wpcf-access-remove-user">&nbsp;</a><input type="hidden" name="'
                        . $name . '[users][]" value="' . $user->ID . '" />'
                        . $user->display_name . ' (' . $user->user_login . ')</div>';
            }
        }
    }
    $output .= '</div><div style="clear:both;"></div></div>';
    return $output;
}

/**
 * Renders pre-defined table.
 * 
 * @param type $type_slug
 * @param type $roles
 * @param type $name
 * @param type $data
 * @return string 
 */
public static function wpcf_access_admin_predefined($type_slug, $roles, $name, $data,
        $enabled = true) {
    $output = '';
    $output .= '<table class="wpcf-access-predefined-table">';
    foreach ($data as $mode => $mode_data) 
    {
        if (!isset($mode_data['title']) || !isset($mode_data['role']))
            continue;
        
        $output .= '<tr><td style="text-align:right;">' . $mode_data['title'] . '</td><td>';
        $output .= '<input type="hidden" class="wpcf-access-name-holder" name="wpcf_access_'
                . $type_slug . '_' . $mode . '" value="' . $name
                . '[' . $mode . ']" />';
        $output .= self::wpcf_access_admin_roles_dropdown($roles,
                $name . '[' . $mode . '][role]', $mode_data, false, $enabled);
        $output .= '</td><td>';
        $output .= self::wpcf_access_admin_users_form($mode_data,
                $name . '[' . $mode . ']', $enabled);
        $output .= '</td></tr>';
    }
    $output .= '</table>';
    return $output;
}

/**
 * Renders custom caps types table.
 * 
 * @param type $type_slug
 * @param type $roles
 * @param type $name
 * @param type $data
 * @return string 
 */
public static function wpcf_access_admin_edit_access_types_item($type_slug, $roles, $name,
        $data, $enabled = true) {
    $output = '';
    $output .= __('Set all capabilities to users of type:') . '&nbsp;'
            . self::wpcf_access_admin_roles_dropdown($roles,
                    'wpcf_access_bulk_set[' . $type_slug . ']', array(),
                    '-- ' . __('Choose user type', 'wpcf_access') . ' --', $enabled);
    $output .= self::wpcf_access_reset_button($type_slug, 'type', $enabled);
    $output .= '<table class="wpcf-access-caps-wrapper">';
    foreach ($data as $cap_slug => $cap_data) 
    {
        $output .= '<tr><td style="text-align:right;">';
        $output .= $cap_data['title'] . '<td/><td>';
        $output .= self::wpcf_access_admin_roles_dropdown($roles,
                $name . '[' . $cap_slug . '][role]', $cap_data, false, $enabled);
        $output .= '<input type="hidden" class="wpcf-access-name-holder" name="wpcf_access_'
                . $type_slug . '_' . $cap_slug . '" data-wpcfaccesscap="'
                . $cap_slug . '" data-wpcfaccessname="'
                . $name . '[' . $cap_slug . ']" value="' . $name
                . '[' . $cap_slug . ']" />';
        $output .= '</td><td>';
        $output .= self::wpcf_access_admin_users_form($cap_data,
                $name . '[' . $cap_slug . ']', $enabled);
        $output .= '</td></tr>';
    }
    $output .= '</td></tr></table>';
    return $output;
}

/**
 * Renders custom caps tax table.
 * 
 * @param type $type_slug
 * @param type $roles
 * @param type $name
 * @param type $data
 * @return string 
 */
public static function wpcf_access_admin_edit_access_tax_item($type_slug, $roles, $name,
        $data, $enabled = true) {
    $output = '';
    $output .= '<table class="wpcf-access-caps-wrapper">';
    foreach ($data as $cap_slug => $cap_data) 
    {
        $output .= '<tr><td style="text-align:right;">';
        $output .= $cap_data['title'] . '<td/><td>';
        $output .= self::wpcf_access_admin_roles_dropdown($roles,
                $name . '[' . $cap_slug . '][role]', $cap_data, false, $enabled);
        $output .= '<input type="hidden" class="wpcf-access-name-holder" name="wpcf_access_'
                . $type_slug . '_' . $cap_slug . '" value="' . $name
                . '[' . $cap_slug . ']" />';
        $output .= '</td><td>';
        $output .= self::wpcf_access_admin_users_form($cap_data,
                $name . '[' . $cap_slug . ']', $enabled);
        $output .= '</td></tr>';
    }
    $output .= '</td></tr></table>';
    return $output;
}

/**
 * Reset caps button.
 * 
 * @param type $type_slug
 * @param type $type
 * @return string 
 */
public static function wpcf_access_reset_button($type_slug, $type = 'type', $enabled = true,
        $managed = true) {
    $output = '';
    $output .= '<input type="submit" id="wpcf-access-reset-' . md5($type_slug . $type)
            . '" class="button-secondary wpcf-access-reset"';
    if (!$enabled) {
        $output .= ' href="javascript:;" disabled="disabled"';
    } else {
        if (!$managed) {
            $output .= ' disabled="disabled"';
        }
        $output .= ' href="' . admin_url('admin-ajax.php?action=wpcf_access_ajax_reset_to_default&amp;_wpnonce='
                        . wp_create_nonce('wpcf_access_ajax_reset_to_default') . '&amp;type='
                        . $type . '&amp;type_slug=' . $type_slug . '')
                . '" onclick="if (confirm(\''
                . addslashes(__('Are you sure? All permission settings for this type will change to their default values.',
                                'wpcf_access'))
                . '\')){ wpcfAccess.Reset(jQuery(this)); } return false;"';
    }
    $output .= ' value="' . __('Reset to defaults', 'wpcf_access') . '" />';
    return $output;
}

/**
 * Submit button.
 * 
 * @param type $enabled
 * @param type $managed
 * @return type 
 */
public static function wpcf_access_submit_button($enabled = true, $managed = true) {
    static $_id=0;
    
    $output = '';
    $_id++;
    if ($enabled && $managed) {
        $output .= '<input type="submit" value="' . __('Save Changes',
                        'wpcf_access') . '" id="submit-' . $_id . '" class="wpcf-access-submit button-primary" />';
    } else {
        $output .= '<input type="submit" value="' . __('Save Changes',
                        'wpcf_access') . '" id="submit-' . $_id . '" class="wpcf-access-submit button-primary" disabled="disabled" />';
    }
    if ($enabled || $managed) {
        $output .= '&nbsp;<img class="ajax-loading" alt="" src="'
                . admin_url('/images/wpspin_light.gif') . '" style="visibility: hidden;">';
    }
    return $output;
}

/**
 * Custom roles form.
 * 
 * @param type $roles
 * @return string 
 */
public static function wpcf_access_admin_set_custom_roles_level_form($roles, $enabled = true) 
{
    $levels = Access_Helper::wpcf_access_role_to_level_map();
    $builtin_roles = array();
    $default_roles = Access_Helper::wpcf_get_default_roles();
    $custom_roles = array();
    $output = '';
    foreach ($roles as $role => $details) 
    {
        if (!in_array($role, $default_roles)) 
        {
            $compare = 'init';
            foreach ($details['capabilities'] as $capability => $true) {
                if (strpos($capability, 'level_') !== false && $true) {
                    $current_level = intval(substr($capability, 6));
                    if ($compare === 'init' || $current_level > intval($compare)) {
                        $compare = $current_level;
                    }
                }
            }
            $level = $compare !== 'init' ? $compare : 'not_set';
            $custom_roles[$level][$role] = $details;
            //$custom_roles[$level][$role]['name'] = taccess_t($details['name'], $details['name']);
            $custom_roles[$level][$role]['level'] = $compare !== 'init' ? $compare : 'not_set';
        } 
        else if (isset($levels[$role])) 
        {
            $level = intval(substr($levels[$role], 6));
            $builtin_roles[$level][$role] = $details;
            $builtin_roles[$level][$role]['name'] = translate_user_role($details['name']);
            $builtin_roles[$level][$role]['level'] = $level;
        }
    }
    if (empty($custom_roles)) 
    {
        return '<div id="wpcf-access-custom-roles-wrapper">'
                . __('No custom roles defined', 'wpcf_access') . '</div>';
    }
    $output .= '<div id="wpcf-access-custom-roles-wrapper">';
    $output .= '<p>' . __('The user level determines which admin actions WordPress allows different kinds of users to perform.',
                    'wpcf_access') . '</p>';
    $output .= '<div id="wpcf-access-custom-roles-table-wrapper">';
    $output .= '<table cellpadding="10" cellspacing="0" class="wpcf-access-custom-roles-table"><tbody>';
    for ($index = 10; $index >= 0; $index--) 
    {
        $level_empty = true;
        $row = '<tr><td><div class="wpcf-access-roles-level">'
                . sprintf(__('Level %d', 'wpcf_access'), $index)
                . '</div></td><td>';
        if (isset($builtin_roles[$index])) 
        {
            $level_empty = false;
            foreach ($builtin_roles[$index] as $role => $details) 
            {
                $row .= '<div class="wpcf-access-roles-builtin">'
                        . $details['name'] . '</div>';
            }
        }
        if (isset($custom_roles[$index])) 
        {
            $level_empty = false;
            foreach ($custom_roles[$index] as $role => $details) 
            {
                $dropdown = '<div class="wpcf-access-custom-roles-select-wrapper">'
                        . '<select name="roles[' . $role
                        . ']" class="wpcf-access-custom-roles-select">';
                for ($index2 = 10; $index2 > -1; $index2--) 
                {
                    $dropdown .= '<option value="' . $index2 . '"';
                    if ($index == $index2) {
                        $dropdown .= ' selected="selected"';
                    }
                    if (!$enabled) {
                        $dropdown .= ' disabled="disabled"';
                    }
                    $dropdown .= '>' . sprintf(__('Level %d', 'wpcf_access'),
                                    $index2);
                    $dropdown .= '</option>';
                }
                $dropdown .= '</select>&nbsp;<a href="javascript:;" '
                        . 'class="wpcf-access-change-level-apply button-primary">'
                        . __('Apply', 'wpcf_access') . '</a>&nbsp;<a href="javascript:;" '
                        . 'class="wpcf-access-change-level-cancel button-secondary">'
                        . __('Cancel') . '</a>'
                        . '</div>';
                $row .= '<div class="wpcf-access-roles-custom">'
                        // provide custom role (display) name wpml localisation through wpml string plugin
                        . taccess_t($details['name'], $details['name']) . '&nbsp;'
                        . '<a href="javascript:;"';
                if ($enabled) {
                    $row .= ' class="wpcf-access-change-level"';
                }
                $row .= '>' . __('Change level', 'wpcf_access') . '</a>'
                        . '&nbsp;';
                if ($enabled) {
                    $row .= $dropdown;
                }
                $row .=' &nbsp;'
                        . '<a ';
                if ($enabled) {
                    $row .= 'href="#TB_inline?height=155&width=500&inlineId=wpcf-access-reassign-' . sanitize_title($role) . '&modal=true" class="wpcf-access-delete-role thickbox"';
                } else {
                    $row .= 'href="javascript:;"';
                }
                $row .= '>' . __('Delete role', 'wpcf_access') . '</a>'
                        . '&nbsp;';
                if ($enabled) {
                    $row .= self::wpcf_access_reassign_role_form($role);
                }
                $row .= '</div>';
            }
        }
        $row .= '</td></tr>';
        if (!$level_empty) {
            $output .= $row;
        }
    }

    if (isset($custom_roles['not_set'])) 
    {
        $output .= '<tr><td><div class="wpcf-access-roles-level">'
                . __('Undefined', 'wpcf_access') . '</div></td><td>';
        foreach ($custom_roles['not_set'] as $role => $details) 
        {
            $dropdown = '<div class="wpcf-access-custom-roles-select-wrapper">'
                    . '<select name="roles[' . $role
                    . ']" class="wpcf-access-custom-roles-select">';
            for ($index2 = 10; $index2 >= 0; $index2--) {
                $dropdown .= '<option value="' . $index2 . '"';
                if ($index2 == 1) {
                    $dropdown .= ' selected="selected"';
                }
                if (!$enabled) {
                    $dropdown .= ' disabled="disabled"';
                }
                $dropdown .= '>'
                        . sprintf(__('Level %d', 'wpcf_access'), $index2)
                        . '</option>';
            }
            $dropdown .= '</select>&nbsp;<a href="javascript:;" '
                    . 'class="wpcf-access-change-level-apply button-primary">'
                    . __('Apply', 'wpcf_access') . '</a>&nbsp;<a href="javascript:;" '
                    . 'class="wpcf-access-change-level-cancel button-secondary">'
                    . __('Cancel') . '</a>'
                    . '</div>';
            $output .= '<div class="wpcf-access-roles-custom">'
                    . $details['name'] . '&nbsp;'
                    . '<a href="javascript:;"';
            if ($enabled) {
                $output .= ' class="wpcf-access-change-level"';
            }
            $output .= '>' . __('Change level', 'wpcf_access') . '</a>'
                    . '&nbsp;';
            if ($enabled) {
                $output .= $dropdown;
            }
            $output .= '<a ';
            if ($enabled) {
                $output .= 'href="#TB_inline?height=155&width=500&inlineId=wpcf-access-reassign-' . $role . '&modal=true" class="wpcf-access-delete-role thickbox"';
            } else {
                $output .= 'href="javascript:;"';
            }
            $output .= '>' . __('Delete role', 'wpcf_access') . '</a>'
                    . '&nbsp;';
            if ($enabled) {
                $output .= self::wpcf_access_reassign_role_form($role);
            }
        }
        $output .= '</div></td></tr>';
    }
    $output .= '</tbody></table>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

/**
 * HTML formatted permissions table.
 * 
 * @param type $roles
 * @param type $permissions
 * @param type $name
 * @return string 
 */
public static function wpcf_access_permissions_table($roles, $permissions, $settings,
        $group_id, $id, $enabled = true, $managed = true) {
    
    $ordered_roles = Access_Helper::wpcf_access_order_roles_by_level($roles);
    $default_roles = Access_Helper::wpcf_get_default_roles();
    $output = '';
    $output .= '<table><tr><th>' . __('Action', 'wpcf_access') . '</th>';
    foreach ($ordered_roles as $levels => $roles_data) 
    {
        if (empty($roles_data))
            continue;
        
        $title = '';
        foreach ($roles_data as $role => $details) 
        {
            if (in_array($role, $default_roles))
                $title .= translate_user_role($details['name']) . '<br />';
            else
                $title .= taccess_t($details['name'], $details['name']) . '<br />';
        }
        $output .= '<th>' . $title . '</th>';
    }
    // Add Guest
    $output .= '<th>' . __('Guest', 'wpcf_access') . '</th>';
    $output .= '<th>' . __('Specific user', 'wpcf_access') . '</th></tr><tbody>';
    foreach ($settings as $permission_slug => $data) 
    {
        // Change slug for 3rd party
        if (!in_array($group_id, array('types', 'tax'))) {
            $permission_slug = $data['cap_id'];
        }
        $check = true;
        $output .= '<tr><td class="wpcf-access-table-action-title">' . $data['title'] . '</td>';
        $name = 'types_access[' . $group_id . '][' . $id . '][permissions]'
                . '[' . $permission_slug . '][role]';
        // If no settings saved use default setting [role]
        $role_check = !empty($permissions[$permission_slug]['role']) ? $permissions[$permission_slug]['role'] : $data['role'];
        foreach ($ordered_roles as $levels => $roles_data) 
        {
            if (empty($roles_data))
                continue;
            
            // Render only first (built-in)
            $role = key($roles_data);
            $details = array_shift($roles_data);
            $att_id = $group_id . '_' . $id . '_permissions_' . $permission_slug . '_'
                    . $role . '_role';
            $attributes = $check ? ' checked="checked"' : '';
            $attributes .=!$managed ? ' readonly="readonly" disabled="disabled"' : '';
            $output .= '<td><input type="checkbox" name="';
            $output .= $role_check == $role ? $name : 'dummy';
            $output .= '" id="' . $att_id . '" value="' . $role . '"'
                    . $attributes . ' class="wpcf-access-check-left wpcf-access-'
                    . $permission_slug . '" data-wpcfaccesscap="'
                    . $permission_slug . '" data-wpcfaccessname="'
                    . $name . '" '
                    . 'onclick="wpcfAccess.AutoThick(jQuery(this), \''
                    . $permission_slug . '\', \''
                    . $name . '\');"';
            if (!$enabled) {
                $output .= ' disabled="disabled" readonly="readonly"';
            }
            $output .= '/></td>';
            // Turn off onwards checking
            if ($role_check == $role) {
                $check = false;
            }
        }
        // Add Guest
        $name = 'types_access[' . $group_id . '][' . $id . '][permissions]'
                . '[' . $permission_slug . '][role]';
        $attributes = $check ? ' checked="checked"' : '';
        $attributes .=!$managed ? ' readonly="readonly" disabled="disabled"' : '';
        $output .= '<td><input type="checkbox" name="';
        $output .= $role_check == 'guest' ? $name : 'dummy';
        $output .= '" id="' . $group_id . '_' . $id . '_permissions_'
                . $permission_slug
                . '_guest_role" value="guest"'
                . $attributes . ' class="wpcf-access-check-left wpcf-access-'
                . $permission_slug . '" data-wpcfaccesscap="'
                . $permission_slug . '" data-wpcfaccessname="'
                . $name . '" '
                . 'onclick="wpcfAccess.AutoThick(jQuery(this), \''
                . $permission_slug . '\', \''
                . $name . '\');"';
        if (!$enabled) {
            $output .= ' disabled="disabled" readonly="readonly"';
        }
        $output .= ' />';
        // Add admin if all disabled
        $output .= '<input type="hidden" name="types_access[' . $group_id . '][' . $id . '][__permissions]'
                . '[' . $permission_slug . '][role]" value="administrator" />';
        $output .= '</td>';

        $data['users'] = !empty($permissions[$permission_slug]['users']) ? $permissions[$permission_slug]['users'] : array();
        $output .= '<td>'
                . '<input type="hidden" class="wpcf-access-name-holder" name="wpcf_access_'
                . $id . '_' . $permission_slug . '" data-wpcfaccesscap="'
                . $permission_slug . '" data-wpcfaccessname="'
                . 'types_access[' . $group_id . ']['
                . $id . ']'
                . '[permissions][' . $permission_slug . ']" value="types_access[' . $group_id . ']['
                . $id . ']'
                . '[permissions][' . $permission_slug . ']" />'
                . self::wpcf_access_admin_users_form($data,
                        'types_access[' . $group_id . '][' . $id . '][permissions]'
                        . '[' . $permission_slug . ']', $enabled, $managed)
                . '</td></tr>';
    }
    $output .= '</tbody></table>';
    return $output;
}

/**
 * Suggest user form.
 * 
 * @global type $wpdb
 * @return string 
 */
public static function wpcf_access_suggest_user($enabled = true, $managed = false) 
{
    static $_id=0;
    global $wpdb;
    // Select first 5 users
    $users = $wpdb->get_results("SELECT ID, user_login, display_name FROM $wpdb->users LIMIT 5");
    $output = '';
    $_id++;
    $output = '<div class="types-suggest-user types-suggest" id="types-suggest-user-'
            . $_id . '">';
    $output .= '<input type="text" class="input" placeholder="' . esc_attr__('search',
                    'wpcf_access') . '"';
    if (!$enabled || !$managed) {
        $output .= ' readonly="readonly" disabled="disabled"';
    }
    $output .= ' />';
    $output .= '<img src="' . esc_url(admin_url('images/wpspin_light.gif')) . '" class="img-waiting" alt="" />';
    $output .= '&nbsp;<a href="javascript:;" class="confirm toggle button-primary">'
            . __('OK', 'wpcf_access') . '</a>';
    $output .= '&nbsp;<a href="javascript:;" class="cancel toggle button-secondary">'
            . __('Cancel', 'wpcf_access') . '</a>';
    $output .= '<br /><select size="' . count($users)
            . '" class="dropdown">';
    foreach ($users as $u) {
        $output .= '<option value="' . $u->ID . '">' . $u->display_name . ' (' . $u->user_login . ')' . '</option>';
    }
    $output .= '</select>';
    $output .= '</div>';
    return $output;
}

/**
 * New role form.
 * 
 * @return string 
 */
public static function wpcf_access_new_role_form($enabled) 
{
    $output = '';
    $output .= '<div id="wpcf-access-new-role">';
    $output .= '<a href="javascript:;" class="button button-primary"';
    if (!$enabled) {
        $output .= ' disabled="disabled" readonly="readonly"';
    }
    $output .= '>' . __('New role', 'wpcf_access') . '</a>';
    $output .= '<div class="toggle">';
    $output .= '<input type="text" name="types_access[new_role]" class="input" value="" />';
    $output .= '<img src="' . esc_url(admin_url('images/wpspin_light.gif')) . '" class="img-waiting" alt="" />';
    $output .= '&nbsp;<a href="javascript:;" class="confirm toggle button-primary" disabled="disabled">'
            . __('OK', 'wpcf_access') . '</a>';
    $output .= '&nbsp;<a href="javascript:;" class="cancel toggle button-secondary">'
            . __('Cancel', 'wpcf_access') . '</a>';
    $output .= '</div>';
    $output .= '<div class="ajax-response"></div>';
    $output .= '</div>';
    return $output;
}

/**
 * Reassing role form.
 * 
 * @param type $role
 * @return string 
 */
public static function wpcf_access_reassign_role_form($role) 
{
    $output = '';
    $output .= '<div class="wpcf-access-reassign-role" id="wpcf-access-reassign-'
            . sanitize_title($role) . '"><div class="wpcf-access-reassign-role-popup">';
    $users = get_users('role=' . $role . '&number=5');
    $users_txt = '';
    foreach ($users as $user) 
    {
        $users_txt[] = $user->display_name;
    }
    if (!empty($users)) 
    {
        $users_txt = implode(', ', $users_txt);
        $output .= sprintf(__('Choose what role to change current %s users to:',
                        'wpcf_access'), '<em>' . $users_txt . '</em>');
        $output .= self::wpcf_access_admin_roles_dropdown(Access_Helper::wpcf_get_editable_roles(),
                'wpcf_reassign', array(),
                __('--- chose role ---', 'wpcf_access'), true, array($role));
    } else {
        $output .= '<input type="hidden" name="wpcf_reassign" value="ignore" />';
        $output .= __('Do you really want to remove this role?', 'wpcf_access');
    }
    $output .= '<input type="hidden" name="wpcf_access_delete_role" value="'
            . $role . '" />
                <input type="hidden" name="wpcf_access_delete_role_nonce" value="'
            . wp_create_nonce('delete_role') . '" />
        <div class="modal">
        <a href="javascript:;" class="button-primary confirm"';
    if (!empty($users)) {
        $output .= ' disabled="disabled">' . __('Save', 'wpcf_access');
    } else {
        $output .= '>' . __('Delete', 'wpcf_access');
    }
    $output .= '</a>
        <a href="javascript:;" class="button-secondary cancel" onclick="javascript:tb_remove();">'
            . __('Cancel', 'wpcf_access') . '</a>&nbsp;<img src="' . esc_url(admin_url('images/wpspin_light.gif')) . '" class="img-waiting" alt="" />
    </div>';
    $output .= '<div class="ajax-response"></div></div></div>';
    return $output;
}
}
