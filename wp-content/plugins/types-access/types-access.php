<?php
/*
Plugin Name: Access
Plugin URI: http://wp-types.com/home/types-access/
Description: User access control and roles management
Author: OnTheGoSystems	 
Author URI: http://www.onthegosystems.com/
Version: 1.1.6
*/

// current version
define('TACCESS_VERSION','1.1.6');
if ( function_exists('realpath') )
    define('TACCESS_PLUGIN_PATH', realpath(dirname(__FILE__)));
else
    define('TACCESS_PLUGIN_PATH', dirname(__FILE__));
define('TACCESS_PLUGIN', plugin_basename(__FILE__));
define('TACCESS_PLUGIN_FOLDER', basename(TACCESS_PLUGIN_PATH));
define('TACCESS_PLUGIN_NAME',TACCESS_PLUGIN_FOLDER.'/'.basename(__FILE__));
define('TACCESS_PLUGIN_BASENAME', TACCESS_PLUGIN);
define('TACCESS_PLUGIN_URL',plugins_url().'/'.TACCESS_PLUGIN_FOLDER);
define('TACCESS_ASSETS_URL',TACCESS_PLUGIN_URL.'/assets');
define('TACCESS_ASSETS_PATH',TACCESS_PLUGIN_PATH.'/assets');
define('TACCESS_INCLUDES_PATH',TACCESS_PLUGIN_PATH.'/includes');
define('TACCESS_TEMPLATES_PATH',TACCESS_PLUGIN_PATH.'/templates');
define('TACCESS_LOGS_PATH',TACCESS_PLUGIN_PATH.'/logs');
define('TACCESS_LOCALE_PATH',TACCESS_PLUGIN_FOLDER.'/locale');
// backwards compatibility
define('WPCF_ACCESS_VERSION', TACCESS_VERSION);
// rename these, because conflicts
define('WPCF_ACCESS_ABSPATH_', TACCESS_PLUGIN_PATH);
define('WPCF_ACCESS_RELPATH_', TACCESS_PLUGIN_URL);
define('WPCF_ACCESS_INC_', TACCESS_INCLUDES_PATH);

// for WPML
define('TACCESS_WPML_STRING_CONTEXT','Types_Access');

//define('TACCESS_DEBUG',true);
//define('TACCESS_DEV',true);




// our global object
global $wpcf_access;




// logging function
if (!function_exists('taccess_log'))
{
if (defined('TACCESS_DEBUG')&&TACCESS_DEBUG)
{
    function taccess_log($message, $file=null, $level=1)
    {
        // check if we need to log..
        if (!defined('TACCESS_DEBUG')||!TACCESS_DEBUG) return false;
        
        // full path to log file
        if ($file==null)
        {
            $file='debug.log';
        }
        
        $file=TACCESS_LOGS_PATH.DIRECTORY_SEPARATOR.$file;

        /* backtrace */
        $bTrace = debug_backtrace(); // assoc array
        
        /* Build the string containing the complete log line. */
        $line = PHP_EOL.sprintf('[%s, <%s>, (%d)]==> %s', 
                                date("Y/m/d h:i:s", mktime()),
                                basename($bTrace[0]['file']), 
                                $bTrace[0]['line'], 
                                print_r($message,true) );
        
        if ($level>1)
        {
            $i=0;
            $line.=PHP_EOL.sprintf('Call Stack : ');
            while (++$i<$level && isset($bTrace[$i]))
            {
                $line.=PHP_EOL.sprintf("\tfile: %s, function: %s, line: %d".PHP_EOL."\targs : %s", 
                                    isset($bTrace[$i]['file'])?basename($bTrace[$i]['file']):'(same as previous)', 
                                    isset($bTrace[$i]['function'])?$bTrace[$i]['function']:'(anonymous)', 
                                    isset($bTrace[$i]['line'])?$bTrace[$i]['line']:'UNKNOWN',
                                    print_r($bTrace[$i]['args'],true));
            }
            $line.=PHP_EOL.sprintf('End Call Stack').PHP_EOL;
        }
        // log to file
        file_put_contents($file,$line,FILE_APPEND);
        
        return true;
    }
}
else
{
    function taccess_log()  { }
}
}



// <<<<<<<<<<<< includes --------------------------------------------------
include(TACCESS_PLUGIN_PATH.'/loader.php');
TAccess_Loader::load('CLASS/Helper');
// init
Access_Helper::init();


// update on activation
function taccess_on_activate()
{
    TAccess_Loader::load('CLASS/Updater');
    Access_Updater::maybeUpdate();
}
register_activation_hook( __FILE__, 'taccess_on_activate' );

// auxilliary global functions

// register the function for backwards compatibility
function wpcf_access_register_caps() {}


/**
 * WPML translate call.
 * 
 * @param type $name
 * @param type $string
 * @param type $string
 * @return type 
 */
function taccess_translate($name, $string, $context = TACCESS_WPML_STRING_CONTEXT) 
{
    if (function_exists('icl_t'))
        $string = icl_t($context, $name, stripslashes($string));
    return $string;
}


/**
 * Registers WPML translation string.
 * 
 * @param type $name
 * @param type $value 
 * @param type $context
 */
function taccess_translate_register_string($name, $value, $context = TACCESS_WPML_STRING_CONTEXT,  $allow_empty_value = false) 
{
    if (function_exists('icl_register_string')) {
        icl_register_string($context, $name, stripslashes($value),
                $allow_empty_value);
    }
}


// register if needed and translatev on the fly
function taccess_t($name, $str, $context = TACCESS_WPML_STRING_CONTEXT,  $allow_empty_value = false)
{
    taccess_translate_register_string($name, $str, $context,  $allow_empty_value);
    return taccess_translate($name, $str, $context);
}


// import / export functions
function taccess_import($xmlstring, $options)
{
    TAccess_Loader::load('CLASS/XML_Processor');
    $results=Access_XML_Processor::importFromXMLString($xmlstring, $options);
    return $results;
}

function taccess_export($what)
{
    TAccess_Loader::load('CLASS/XML_Processor');
    $xmlstring=Access_XML_Processor::exportToXMLString($what);
    return $xmlstring;
}


/**
 * Deactivation hook.
 * 
 * Reverts wp_user_roles option to snapshot created on activation.
 * Removes snapshot. 
 */
/*function wpcf_access_deactivation() {
//    $snapshot = get_option('wpcf_access_snapshot', array());
//    if (!empty($snapshot)) {
//        update_option('wp_user_roles', $snapshot);
//    }
//    delete_option('wpcf_access_snapshot');
}*/
