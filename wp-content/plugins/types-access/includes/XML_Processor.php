<?php
/**
*   Access XML Processor
*   handles import-export to/from XML
*
**/
final class Access_XML_Processor
{
    public static $use_zip_if_available=true;
    
    private static $add_CDATA=false;
    private static $root='access';
    private static $filename='';
    
    private static function arrayToXml($array, $depth, $parent)
    {
        $output = '';
        $indent = str_repeat(' ', $depth * 4);
        $child_key = false;
        if (isset($array['__key'])) {
            $child_key = $array['__key'];
            unset($array['__key']);
        }
        foreach ($array as $key => $value) 
        {
            if (empty($key) && $key!==0) continue;
            
            if (!($key=='settings' && $parent==self::$root))
                $key = $child_key ? $child_key : $key;
            if (is_numeric($key))
                $key=$parent.'_item';//.$key;
            if (!is_array($value) && !is_object($value)) 
            {
                if (self::$add_CDATA && !is_numeric($value) && !empty($value))
                    $output .= $indent . "<$key><![CDATA[" . htmlspecialchars($value, ENT_QUOTES) . "]]></$key>\r\n";
                else
                    $output .= $indent . "<$key>" . htmlspecialchars($value, ENT_QUOTES) . "</$key>\r\n";
            } 
            else 
            {
                if (is_object($value))
                    $value=(array)$value;
                    
                $output_temp = self::arrayToXml($value, $depth+1, $key);
                if (!empty($output_temp)) {
                    $output .= $indent . "<$key>\r\n";
                    $output .= $output_temp;
                    $output .= $indent . "</$key>\r\n";
                }
            }
        }
        return $output;
    }
    
    private static function toXml($array, $root_element)
    {
        if (empty($array)) return "";
        $xml = "";
        $xml .= "<?xml version=\"1.0\" encoding=\"". get_option('blog_charset'). "\"?>\r\n";
        $xml .= "<$root_element>\r\n";
        $xml .= self::arrayToXml($array[$root_element], 1, $root_element);
        $xml .="</$root_element>";
        return $xml;
    }
    
    private static function toArray($element) 
    {
        $element = is_string($element) ? htmlspecialchars_decode(trim($element), ENT_QUOTES) : $element;
        if (!empty($element) && is_object($element)) 
        {
            $element = (array) $element;
        }
        if (empty($element)) 
        {
            $element = '';
        } 
        if (is_array($element)) 
        {
            foreach ($element as $k => $v) 
            {
                $v = is_string($v) ? htmlspecialchars_decode(trim($v), ENT_QUOTES) : $v;
                if (empty($v)) 
                {
                    $element[$k] = '';
                    continue;
                }
                $add = self::toArray($v);
                if (!empty($add)) 
                {
                    $element[$k] = $add;
                } 
                else 
                {
                    $element[$k] = '';
                }
                // numeric arrays when -> toXml take '_item' suffixes
                // do reverse process here, now it is generic
                if (is_array($element[$k]) && isset($element[$k][$k.'_item']))
                {
                    $element[$k] = array_values((array)$element[$k][$k.'_item']);
                }
            }
        }

        if (empty($element)) 
        {
            $element = '';
        }

        return $element;
    }
    
    public static function getSelectedSettingsForExport($settings=array(), $options=array(), &$mode)
    {
        if (empty($settings))
            return array();
        
        $data=array();
        $access_settings=array();
        $model=TAccess_Loader::get('MODEL/Access');
        //$isTypesActive = Access_Helper::wpcf_access_is_wpcf_active();

        foreach ((array)$settings as $set)
        {
            switch($set)
            {
                case 'types':
                    $access_settings['types']=$model->getAccessTypes();
                    /*if ($isTypesActive)
                    {
                        $types_settings = $model->getWpcfTypes();
                        $access_settings['types_wpcf']=array();
                        foreach ($types_settings as $typ=>$data)
                        {
                            if (isset($data['_wpcf_access_capabilities']))
                                $access_settings['types_wpcf'][$typ]=$data['_wpcf_access_capabilities'];
                        }
                    }*/
                    break;
                case 'taxonomies':
                    $access_settings['taxonomies']=$model->getAccessTaxonomies();
                    /*if ($isTypesActive)
                    {
                        $taxonomies_settings = $model->getWpcfTaxonomies();
                        $access_settings['taxonomies_wpcf']=array();
                        foreach ($taxonomies_settings as $tax=>$data)
                        {
                            if (isset($data['_wpcf_access_capabilities']))
                                $access_settings['taxonomies_wpcf'][$tax]=$data['_wpcf_access_capabilities'];
                        }
                    }*/
                    break;
                case 'third_party':
                    $access_settings['third_party']=$model->getAccessThirdParty();
                    break;
                case 'all':
                    $access_settings['types']=$model->getAccessTypes();
                    $access_settings['taxonomies']=$model->getAccessTaxonomies();
                    /*if ($isTypesActive)
                    {
                        $types_settings = $model->getWpcfTypes();
                        $taxonomies_settings = $model->getWpcfTaxonomies();
                        $access_settings['types_wpcf']=array();
                        foreach ($types_settings as $typ=>$data)
                        {
                            if (isset($data['_wpcf_access_capabilities']))
                                $access_settings['types_wpcf'][$typ]=$data['_wpcf_access_capabilities'];
                        }
                        $access_settings['taxonomies_wpcf']=array();
                        foreach ($taxonomies_settings as $tax=>$data)
                        {
                            if (isset($data['_wpcf_access_capabilities']))
                                $access_settings['taxonomies_wpcf'][$tax]=$data['_wpcf_access_capabilities'];
                        }
                    }*/
                    $access_settings['third_party']=$model->getAccessThirdParty();
                    break;
            }
        }
        
        // apply some filters for 3rd-party custom capabilities
        if (isset($access_settings['third_party']) && !empty($access_settings['third_party']))
        {
            foreach ($access_settings['third_party'] as $area=>$data)
            {
                $access_settings['third_party'][$area]=apply_filters('access_export_custom_capabilities_'.$area, $access_settings['third_party'][$area], $area);
            }
        }
        
        $mode='access';
        if ('all'==$settings)
        {
            $mode='all-access-settings';
        }
        else
        {
            $mode='selected-access-settings';
        }
        
        if (!empty($access_settings)) 
        {
            $data[self::$root] = $access_settings;
        }
        return $data;
    }
    
    private static function output($xml, $ajax, $mode)
    {
        $sitename = sanitize_key(get_bloginfo('name'));
        if (!empty($sitename)) {
            $sitename .= '-';
        }
        
        $filename = $sitename . $mode . '-' . date('Y-m-d') . '.xml';
        
        $data=$xml;
        
        if (self::$use_zip_if_available && class_exists('ZipArchive')) 
        { 
            $zipname = $filename . '.zip';
            $zip = new ZipArchive();
            $tmp='tmp';
            // http://php.net/manual/en/function.tempnam.php#93256
            if (function_exists('sys_get_temp_dir'))
                $tmp=sys_get_temp_dir();
            $file = tempnam($tmp, "zip");
            $zip->open($file, ZipArchive::OVERWRITE);
        
            $res = $zip->addFromString($filename, $xml);
            $zip->close();
            $data = file_get_contents($file);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $zipname);
            header("Content-Type: application/zip");
            header("Content-length: " . strlen($data) . "\n\n");
            header("Content-Transfer-Encoding: binary");
            if ($ajax)
                header("Set-Cookie: __AccessExportDownload=true; path=/");
            echo $data;
            unset($data);
            unset($xml);
            unlink($file);
            die();
        } 
        else 
        {
            // download the xml.
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Content-Type: application/xml");
            header("Content-length: " . strlen($data) . "\n\n");
            if ($ajax)
                header("Set-Cookie: __AccessExportDownload=true; path=/");
            echo $data;
            unset($data);
            unset($xml);
            die();
        }
    }
    
    private static function readXML($file)
    {
        $data = array();
        $info = pathinfo($file['name']);
        $is_zip = $info['extension'] == 'zip' ? true : false;
        if ($is_zip) 
        {
            $zip = zip_open(urldecode($file['tmp_name']));
            if (is_resource($zip)) 
            {
                $zip_entry = zip_read($zip);
                if (is_resource($zip_entry) && zip_entry_open($zip, $zip_entry))
                {
                    $data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close ( $zip_entry );
                }
                else
                    return new WP_Error('could_not_open_file', __('No zip entry', 'wpcf_access'));
            } 
            else 
            {
                return new WP_Error('could_not_open_file', __('Unable to open zip file', 'wpcf_access'));
            }
        } 
        else 
        {
            $fh = fopen($file['tmp_name'], 'r');
            if ($fh) 
            {
                $data = fread($fh, $file['size']);
                fclose($fh);
            }
        }
        
        if (!empty($data)) 
        {

            if (!function_exists('simplexml_load_string')) 
            {
                return new WP_Error('xml_missing', __('The Simple XML library is missing.','wpcf_access'));
            }
            $xml = simplexml_load_string($data);
            //print_r($xml);

            if (!$xml) 
            {
                return new WP_Error('not_xml_file', sprintf(__('The XML file (%s) could not be read.','wpcf_access'), $file['name']));
            }

            $import_data = self::toArray($xml);
            
            /*taccess_log($import_data);
            taccess_log(TAccess_loader::get('MODEL/Access')->getAccessTypes());
            $import_data=array();*/
            
            unset($xml);
            //print_r($import_data);
            return $import_data;

        } 
        else 
        {
            return new WP_Error('could_not_open_file', __('Could not read the import file.','wpcf_access'));
        }
        return new WP_Error('unknown error', __('Unknown error during import','wpcf_access'));
    }
    
    private static function importSettings($data, $options=array())
    {
        $model=TAccess_Loader::get('MODEL/Access');
        
        $results=array(
            'new'=>0,
            'updated'=>0,
            'deleted'=>0,
            'failed'=>0,
            'errors'=>array()
        );
        
        $dataTypes=isset($data['types']);
        $dataTax=isset($data['taxonomies']);
        $data3=isset($data['third_party']);

        $diff=array();
        $intersect=array();
        
        $access_settings=array(
            'types'=>$model->getAccessTypes(),
            'taxonomies'=>$model->getAccessTaxonomies(),
            'third_party'=>$model->getAccessThirdParty()
        );
        
        if ($dataTypes)
        {
            $diff['types']=array_diff_key($data['types'], $access_settings['types']);
            $intersect['types']=array_intersect_key($data['types'], $access_settings['types']);
        }   
        if ($dataTax)
        {
            $diff['taxonomies']=array_diff_key($data['taxonomies'], $access_settings['taxonomies']);
            $intersect['taxonomies']=array_intersect_key($data['taxonomies'], $access_settings['taxonomies']);
        }
        
        // apply filters for custom 3rd-party capabilities
        if ($data3)
        {
            $diff['third_party']=array();
            $intersect['third_party']=array();
            foreach ($data['third_party'] as $area=>$adata)
            {
                $data['third_party'][$area]=apply_filters('access_import_custom_capabilities_'.$area, $data['third_party'][$area], $area);
                if (isset($access_settings['third_party'][$area]))
                {
                    $diff['third_party'][$area]=array_diff_key($data['third_party'][$area], $access_settings['third_party'][$area]);
                    $intersect['third_party'][$area]=array_intersect_key($data['third_party'], $access_settings['third_party'][$area]);
                }
                else
                {
                    $diff['third_party'][$area]=$data['third_party'][$area];
                    $intersect['third_party'][$area]=array();
                }
            }
        }
        
        //taccess_log(array('Before', $access_settings, $diff, $intersect));
        
        // import / merge extra settings
        // Types
        if ($dataTypes)
        {
            $access_settings['types']=array_merge($access_settings['types'], $diff['types']);
            $results['new']+=count($diff['types']);
        }
        
        // Taxonomies
        if ($dataTax)
        {
            $access_settings['taxonomies']=array_merge($access_settings['taxonomies'], $diff['taxonomies']);
            $results['new']+=count($diff['taxonomies']);
        }
        
        // Third-Party
        if ($data3)
        {
            if (!isset($access_settings['third_party']))
                $access_settings['third_party']=array();
                
            foreach ($diff['third_party'] as $area=>$adata)
            {
                if (isset($access_settings['third_party'][$area]))
                    $access_settings['third_party'][$area]=array_merge($access_settings['third_party'][$area], $diff['third_party'][$area]);
                else
                    $access_settings['third_party'][$area]=$diff['third_party'][$area];
                $results['new']+=count($diff['third_party'][$area]);
            }
        }
        
        //taccess_log(array('Import Extra', $access_settings, $diff, $intersect));
        
        // overwrite existing settings
        if (isset($options['access-overwrite-existing-settings']))
        {
            if ($dataTypes)
            {
                $access_settings['types']=array_merge($access_settings['types'], $intersect['types']);
                $results['updated']+=count($intersect['types']);
            }
            if ($dataTax)
            {
                $access_settings['taxonomies']=array_merge($access_settings['taxonomies'], $intersect['taxonomies']);
                $results['updated']+=count($intersect['taxonomies']);
            }
            if ($data3)
            {
                foreach ($access_settings['third_party'] as $area=>$adata)
                {
                    if (isset($intersect['third_party'][$area]))
                    {
                        $access_settings['third_party'][$area]=array_merge($access_settings['third_party'][$area], $intersect['third_party'][$area]);
                        $results['updated']+=count($intersect['third_party'][$area]);
                    }
                }
            }
        }
        
        //taccess_log(array('Overwrite', $access_settings, $diff, $intersect));
        
        // remove not imported settings
        if (isset($options['access-remove-not-included-settings']))
        {
            if ($dataTypes)
            {
                $tmp=count($access_settings['types']);
                $access_settings['types']=array_intersect_key($access_settings['types'], $data['types']);
                $results['deleted']+=$tmp-count($access_settings['types']);
            }
            if ($dataTax)
            {
                //taccess_log(array($access_settings['taxonomies'], $data['taxonomies']));
                $tmp=count($access_settings['taxonomies']);
                $access_settings['taxonomies']=array_intersect_key($access_settings['taxonomies'], $data['taxonomies']);
                $results['deleted']+=$tmp-count($access_settings['taxonomies']);
                //taccess_log(array($access_settings['taxonomies'], $data['taxonomies']));
            }
            if ($data3)
            {
                foreach ($access_settings['third_party'] as $area=>$adata)
                {
                    if (!isset($data['third_party'][$area]))
                    {
                        //$tmp=count($access_settings['third_party'][$area]);
                        //$access_settings['third_party']=array_diff_key($access_settings['third_party'], $data['third_party']);
                        $results['deleted']+=1; //$tmp-count($access_settings['third_party'][$area]);
                        unset($access_settings['third_party'][$area]);
                    }
                }
            }
        }
        
        //taccess_log(array('Remove', $access_settings, $diff, $intersect));
        
        // update settings
        $model->updateAccessTypes($access_settings['types']);
        $model->updateAccessTaxonomies($access_settings['taxonomies']);
        $model->updateAccessThirdParty($access_settings['third_party']);
        
        return $results;
    }
    
    public static function exportToXML($settings, $ajax=false)
    {
        $mode='forms';
        $data=self::getSelectedSettingsForExport($settings, array(), $mode);
        $xml=self::toXml($data, self::$root);
        self::output($xml, $ajax, $mode);
    }
    
    public static function exportToXMLString($settings, $options=array())
    {
        $mode='access';
        // add hashes as extra
        $data=self::getSelectedSettingsForExport($settings, $options, $mode);
        $xml=self::toXml($data,self::$root);
        return $xml;
    }
    
    public static function importFromXML($file, $options=array())
    {
        $dataresult=self::readXML($file);
        if ($dataresult!==false && !is_wp_error($dataresult))
        {
           $results = self::importSettings($dataresult, $options);
           return $results;
        }
        else
        {
            return $dataresult;
        }
    }
    
    public static function importFromXMLString($xmlstring, $options=array())
    {
        if (!function_exists('simplexml_load_string')) 
        {
            return new WP_Error('xml_missing', __('The Simple XML library is missing.','wpcf_access'));
        }
        $xml = simplexml_load_string($xmlstring);
        
        $dataresult=self::toArray($xml);

        if (false!==$dataresult && !is_wp_error($dataresult))
        {
           $results = self::importSettings($dataresult, $options);
           return $results;
        }
        else
        {
            return $dataresult;
        }
    }
}
