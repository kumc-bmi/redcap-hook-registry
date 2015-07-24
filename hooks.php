<?php
/**
 * This is to deal with PHP relative path issues resulting from differences in
 * where a script is executed from.  NOTE: There has to be a better to deal with
 * this.
 */
if(defined('APP_PATH_DOCROOT')) { // Executed by REDCap process.
    define('REDCAP_ROOT', realpath(APP_PATH_DOCROOT.'../').'/');
    define('HOOKS_CONFIG', realpath(APP_PATH_DOCROOT.'../hooks/hooks.ini'));
} else { // For testing from REDCap root directory.
    define('REDCAP_ROOT', '');
    define('HOOKS_CONFIG', 'hooks/hooks.ini');
}

/**
 * An object for storing REDCapHookRegistry configuration values. The
 * constructor takes an ini filepath as a single construction parameter and 
 * populates itself with its contents. Otherwise, the object behaves like an 
 * immutable PHP Array.
 */
class HooksConfig implements ArrayAccess {

    private $container = array();

    public function __construct($config_file) {
        if(is_readable($config_file)) {
            $this->container = parse_ini_file($config_file, true);
        } /* else {
            ... we should handle this case!
        } */
    }

    public function offsetSet($index, $value) {
        return;
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        return;
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}


/**
 * The REDCapHookRegistry allows independent functions, which implement a given
 * REDCap hook, to be defined and executed outside of a single, monolithic hook
 * file.
 *
 * This code relies upon an ini configuration file which defines the hook type,
 * file, function relationship (file path defined above as HOOKS_CONFIG). 
 *
 * Config Example:
 *
 * [redcap_hook_function]
 * redcap/root/relative/path/to/hook/file.php= name_of_hook_function:<pid>,<pid>
 *
 * It is recommended that hook implementations that are project specific be
 * placed in a file named for the project and placed in 
 * <redcap-root>/hooks/projects/<project-string>.  Hook functions should have
 * the same form as the original REDCap hook function name with the leading 
 * 'redcap_' replaced with <project-string>. For example, the 'test' project
 * implements the redcap_save_record hook.  The hook function should be named
 * test_save_record and should be located in
 * <redcap-root>/hooks/projects/test.php.
 * 
 * Hook implementations that are plugin specific should be placed in a hook file
 * within the <plugin-root> directory.  Hook functions should have the same form
 * as the original REDCap hook function names with the leading 'redcap_'
 * replaced with <project-root>. For example, the 'example' plugin implements 
 * the redcap_save_record hook.  The hook function should be named
 * example_save_record and should be located in
 * <redcap-root>/plugins/<plugin-root>/hooks.php.
 */
class REDCapHookRegistry {

    private $CONFIG;

    public function __construct($CONFIG) {
        $this->CONFIG = $CONFIG;
    }

    public function redcap_data_entry_form($project_id, $record, $instrument,
                                           $event_id, $group_id)
    {
        foreach($this->CONFIG['redcap_data_entry_form'] as $file => $params) {
            list($function, $project_ids) = explode(':', $params);
            if(in_array($project_id, explode(',', $project_ids))) {
                if(is_readable(REDCAP_ROOT.$file)) {
                    require_once(REDCAP_ROOT.$file);
                    $function($project_id, $record, $instrument, $event_id);
                }
            } /* else {
                ... we should handle this case!
            } */
        }
    }

    public function redcap_save_record($project_id, $record, $instrument,
                                       $event_id, $group_id, $survey_hash,
                                       $response_id)
    {
        foreach($this->CONFIG['redcap_save_record'] as $file => $params) {
            list($function, $project_ids) = explode(':', $params);
            if(in_array($project_id, explode(',', $project_ids))) {
                if(is_readable(REDCAP_ROOT.$file)) {
                    require_once(REDCAP_ROOT.$file);
                    $function($project_id, $record, $instrument, $event_id,
                              $group_id, $survey_hash, $response_id);
                }
            } /* else {
                ... we should handle this case!
            } */
        }
    }

    /**
     * TODO: Implement remaining REDCap hook functions
     */
}

/**
 * The actual REDCap hook functions which are called by the REDCap application.
 */
function redcap_data_entry_form($project_id, $record, $instrument, $event_id,
                                $group_id)
{
    $CONFIG = new HooksConfig(HOOKS_CONFIG);
    $registered_hooks = new REDCapHookRegistry($CONFIG);
    $registered_hooks->redcap_data_entry_form(
        $project_id,
        $record,
        $instrument,
        $event_id,
        $group_id
    );
}

function redcap_save_record($project_id, $record, $instrument, $event_id,
                            $group_id, $survey_hash, $response_id)
{
    $CONFIG = new HooksConfig(HOOKS_CONFIG);
    $registered_hooks = new REDCapHookRegistry($CONFIG);
    $registered_hooks->redcap_save_record($project_id, $record, $instrument,
                                          $event_id, $group_id, $survey_hash,
                                          $response_id);
}

/**
 * TODO: Implement remaining REDCap hook functions
 */
?>
