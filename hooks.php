<?php
define('REDCAP_ROOT', realpath(dirname(__FILE__).'/../').'/');
define('HOOKS_CONFIG', REDCAP_ROOT.'hooks/hooks.ini');


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
        } else {
            throw new Exception("Config file not readable at $config_file.");
        }
    }

    // Do not allow offset to be set, and therefore changed.
    public function offsetSet($index, $value) {
        return;
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    // Do not allow offset to be unset, and therefor changed.
    public function offsetUnset($offset) {
        return;
    }

    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->container[$offset] : null;
    }
}


/**
 * The REDCapHookRegistry allows independent functions, which implement a given
 * REDCap hook, to be defined and executed outside of a single, monolithic hook
 * file.
 *
 * This code relies upon an ini configuration file which defines the hook type /
 * PHP include file path / hook implementation function relationship (file path
 * defined above as HOOKS_CONFIG). 
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
 * replaced with <plugin-root>. For example, the 'example' plugin implements 
 * the redcap_save_record hook.  The hook function should be named
 * example_save_record and should be located in
 * <redcap-root>/plugins/<plugin-root>/hooks.php.
 *
 * NOTE: An unavoidable limitation is that all hook functions must be uniquely
 * named.
 */
class REDCapHookRegistry {

    private $CONFIG;

    public function __construct($config_path) {
        $this->CONFIG = new HooksConfig($config_path);
    }

    // It explodes, it trims, it... trimsplodes!
    // NOTE: You can rename this if you want Matt, but I refuse to!
    private function trimsplode($delimiter, $string) {
        return str_replace(' ', '', explode($delimiter, $string));
    }

    public function process_hook($hook, $project_id, $params) {
        foreach($this->CONFIG[$hook] as $file => $target) {
            list($function, $project_ids) = $this->trimsplode(':', $target);
            if($project_ids == '*' 
               or in_array($project_id, $this->trimsplode(',', $project_ids))
            ) {
                $filepath = REDCAP_ROOT.$file;
                if(is_readable($filepath)) {
                    require_once($filepath);
                    if(function_exists($function)) {
                        call_user_func_array($function, $params);
                    } else {
                        throw new Exception(
                            "REDCap hook function $function is not defined in"
                            ."hook file at $filepath."
                        );
                    }
                } else {
                    throw new Exception(
                        "REDCap hook file not readable at $filepath."
                    );
                }
            }
        }
    }
}


/**
 * The actual REDCap hook functions which are called by the REDCap application.
 */
function redcap_control_center() {
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $registered_hooks->process_hook('redcap_control_center', $project_id,
                                    array());
}

function redcap_custom_verify_username($username) {
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $registered_hooks->process_hook('redcap_custom_verify_username',
                                    $project_id, array($username));
}

function redcap_data_entry_form($project_id, $record, $instrument, $event_id,
                                $group_id)
{
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $params = array($project_id, $record, $instrument, $event_id, $group_id);
    $registered_hooks->process_hook('redcap_data_entry_form', $project_id,
                                    $params);
}

function redcap_save_record($project_id, $record, $instrument, $event_id,
                            $group_id, $survey_hash, $response_id)
{
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $params = array($project_id, $record, $instrument, $event_id, $group_id,
                    $survey_hash, $response_id);
    $registered_hooks->process_hook('redcap_save_record', $project_id, $params);
}

function redcap_survey_complete($project_id, $record, $instrument, $event_id,
                                $group_id, $survey_hash, $response_id)
{
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $params = array($project_id, $record, $instrument, $event_id, $group_id,
                    $survey_hash, $response_id);
    $registered_hooks->process_hook('redcap_survey_complete', $project_id,
                                    $params);
}

function redcap_survey_page($project_id, $record, $instrument, $event_id,
                            $group_id, $survey_hash, $response_id)
{
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $params = array($project_id, $record, $instrument, $event_id, $group_id,
                    $survey_hash, $response_id);
    $registered_hooks->process_hook('redcap_survey_page', $project_id, $params);
}

function redcap_user_rights($project_id) {
    $registered_hooks = new REDCapHookRegistry(HOOKS_CONFIG);
    $registered_hooks->process_hook('redcap_user_rights', $project_id,
                                    array($project_id));
}
?>
