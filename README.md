# REDCap Hook Registry
---
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Testing
 * Validation
 * Maintainers


### INTRODUCTION
This custom REDCap hook registry allows independent functions, which implement
a given REDCap hook, to be defined and executed outside of a single, monolithic
hook file.


### REQUIREMENTS
Currently there are no requirements other than REDCap. 

### INSTALLATION
To install this code:
 1. Clone the hook registry code from (https://github.com/kumc-bmi/redcap-hook-registry.git) into `<redcap root directory>/hooks`.
 2. Login to REDCap with a user which has administrator priviledges, and
    associate the absolute path of `<redcap root directory>/hooks/hooks.php`
    with the  REDCap Hooks field in the General Configuration located in the
    Control Center.

### CONFIGURATION
This code relies upon an `ini` configuration file which defines the hook type /
PHP include file path / hook implementation function relationship (file path
defined above as `HOOKS_CONFIG`).

Config Example:

```ini
[redcap_hook_function]
redcap/root/relative/path/to/hook/file.php= name_of_hook_function:<pid>,<pid>
```

It is recommended that hook implementations that are project specific be placed
in a file named for the project and placed in
`<redcap-root>/hooks/projects/<project-string>`.  Hook functions should have the
same form as the original REDCap hook function name with the leading
`redcap_` replaced with `<project-string>`. For example, the **foo** project
implements the `redcap_save_record hook`.  The hook function should be named
`foo_save_record` and should be located in `<redcap-root>/hooks/projects/foo.php`.

Hook implementations that are plugin specific should be placed in a hook file
within the `<plugin-root>` directory.  Hook functions should have the same form as
the original REDCap hook function names with the leading `redcap_` replaced with
`<plugin-root>`. For example, the **bar** plugin implements the 
`redcap_save_record` hook.  The hook function should be named `bar_save_record`
and should be located in `<redcap-root>/plugins/<plugin-root>/hooks.php`.

**NOTE:** An unavoidable limitation is that all hook functions must be uniquely
named.

### VALIDATION
To validate that the hook registry is working correctly validate that a hook
implementation, which uses this code works correctly (e.g. the notification
plugin).

### MAINTAINERS
Current maintainers:
 * Michael Prittie <mprittie@kumc.edu>
