<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Test doubles that allow the module's hooks/service layers to run outside FA.
 *
 * - _()          : identity gettext stub (FA translation function)
 * - hooks        : stub of FA core's hooks base class
 * - hook_invoke_all() : settable stub used by ContactResolver
 * - db_* / TB_PREF    : stubs of FA's native database layer
 *
 * Every definition is guarded so it never clashes with a real FA runtime.
 */

if (!function_exists('_')) {
    /**
     * gettext identity stub used by FA outside a running web request.
     *
     * @param string $string
     * @return string
     */
    function _($string)
    {
        return $string;
    }
}

if (!class_exists('hooks', false)) {
    /**
     * Minimal stub of FA core's hooks base class so module hooks_* classes
     * can be instantiated in unit tests without the full FA bootstrap.
     */
    class hooks
    {
        public $module_name = '';
        public $version = '';

        public function install_tabs($app) {}
        public function install_options($app) {}
        public function install_access() { return array(array(), array()); }
        public function activate_extension($company, $check_only = true) { return true; }
    }
}

if (!function_exists('hook_invoke_all')) {
    $GLOBALS['KSF_HOOK_HANDLER'] = null;
    /**
     * Settable stub of FA's hook_invoke_all(). A test installs a callable via
     * $GLOBALS['KSF_HOOK_HANDLER'] to simulate a listener answering a hook.
     *
     * @param string $hook
     * @param array  $payload Modified by reference
     * @return void
     */
    function hook_invoke_all($hook, &$payload)
    {
        $handler = $GLOBALS['KSF_HOOK_HANDLER'] ?? null;
        if (is_callable($handler)) {
            $handler($hook, $payload);
        }
    }
}

if (!defined('TB_PREF')) {
    define('TB_PREF', '0_');
}

if (!function_exists('db_query')) {
    $GLOBALS['KSF_DB_LAST_SQL']         = null;
    $GLOBALS['KSF_DB_RESULT']           = false;
    $GLOBALS['KSF_DB_ROWS']             = array();
    $GLOBALS['KSF_DB_INSERT_ID']        = 1;
    $GLOBALS['KSF_DB_RESULT_SETS']      = array();
    $GLOBALS['KSF_DB_RESULT_POS']       = array();
    $GLOBALS['KSF_DB_NEXT_RESULT_ID']   = 0;

    /**
     * Stub of FA's db_query(). When KSF_DB_RESULT is true, returns a unique
     * result handle backed by KSF_DB_ROWS; otherwise returns false. Records
     * the last SQL in KSF_DB_LAST_SQL. Each call gets an isolated result set,
     * so queries never interfere with each other's row cursor.
     *
     * @param string $sql
     * @param string $errMsg
     * @return string|false
     */
    function db_query($sql, $errMsg = '')
    {
        $GLOBALS['KSF_DB_LAST_SQL'] = $sql;
        if ($GLOBALS['KSF_DB_RESULT'] !== true) {
            return false;
        }
        $resultId = (string) (++$GLOBALS['KSF_DB_NEXT_RESULT_ID']);
        $GLOBALS['KSF_DB_RESULT_SETS'][$resultId] = $GLOBALS['KSF_DB_ROWS'];
        $GLOBALS['KSF_DB_RESULT_POS'][$resultId]  = 0;
        $GLOBALS['KSF_DB_ROWS'] = array();
        return $resultId;
    }

    /**
     * Stub of FA's db_fetch_assoc(). Iterates the rows bound to the result
     * handle returned by db_query(); returns false at the end of the set.
     *
     * @param mixed $result
     * @return array|false
     */
    function db_fetch_assoc($result)
    {
        if (!is_string($result)
            || !isset($GLOBALS['KSF_DB_RESULT_SETS'][$result])
        ) {
            return false;
        }
        $rows = &$GLOBALS['KSF_DB_RESULT_SETS'][$result];
        $pos  = &$GLOBALS['KSF_DB_RESULT_POS'][$result];
        if ($pos < count($rows)) {
            return $rows[$pos++];
        }
        return false;
    }

    /**
     * Stub of FA's db_insert_id().
     *
     * @return int
     */
    function db_insert_id()
    {
        return (int) ($GLOBALS['KSF_DB_INSERT_ID'] ?? 1);
    }

    /**
     * Stub of FA's db_escape(). Returns the value wrapped in single quotes,
     * mirroring FA's real db_escape().
     *
     * @param string $value
     * @return string
     */
    function db_escape($value)
    {
        return "'" . addslashes((string) $value) . "'";
    }
}
