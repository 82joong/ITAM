<?php
if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package     CodeIgniter
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 * @link        http://codeigniter.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @author        ExpressionEngine Dev Team
 * @category    Loader
 * @link        http://codeigniter.com/user_guide/libraries/loader.html
 */
class MY_Loader extends CI_Loader
{
    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param    mixed    $params        Database configuration options
     * @param    bool    $return     Whether to return the database object
     * @param    bool    $query_builder    Whether to enable Query Builder
     *                    (overrides the configuration setting)
     *
     * @return    object|bool    Database object if $return is set to TRUE,
     *                    FALSE on failure, CI_Loader instance in any other case
     */

    public function database($params = '', $return = false, $query_builder = null)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if ($return === false&& $query_builder === null && isset($CI->db) && is_object($CI->db) && ! empty($CI->db->conn_id)) {
            return false;
        }

        
        $inc_paths = array(
            APPPATH.'database/'.config_item('subclass_prefix').'DB.php',
            BASEPATH.'database/DB.php'
        );


        foreach ($inc_paths as $inc_path) {
            if (is_file($inc_path) == true) {
                require_once($inc_path);
                break;
            }
        }



        if ($return === true) {
            return DB($params, $query_builder);
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $CI->db = '';

        // Load the DB class
        $CI->db =& DB($params, $query_builder);
        return $this;
    }


    public function model($model, $name = '', $db_conn = false)
    {
        return $this->modelLoader($model, $name, $db_conn, 'model');
    }

    public function business($model, $name = '', $db_conn = false)
    {
        return $this->modelLoader($model, $name, $db_conn, 'business');
    }

    private function modelLoader($model, $name = '', $db_conn = false, $type = 'model')
    {

        if (empty($model)) {
            //throw new Exception('model value is empty.');
            return parent::model($model, $name, $db_conn);
        } elseif (is_array($model)) {
            foreach ($model as $key => $value) {
                if (is_int($key)) {
                    parent::model($this->editModelPath($value, $type), '', $db_conn);
                } else {
                    parent::model($this->editModelPath($key, $type), $value, $db_conn);
                }
            }
            return $this;
        }
        return parent::model($this->editModelPath($model, $type), $name, $db_conn);
    }

    private function editModelPath($path, $mod_or_map)
    {

        $path = trim($path);
        $path = strtolower($path);
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        if ($mod_or_map == 'model') {
            $prefix = 'solution/';
            $postfix = '_model';
        }else {
            $prefix = 'business/';
            $postfix = '_business';
        }

        if (strpos($path, $prefix) !== 0) {
            $path = $prefix.$path;
        }
        if (strrpos($path, $postfix) !== strlen($path)-strlen($postfix)) {
            $path = $path.$postfix;
        }
        return $path;
    }
}
/* End of file Loader.php */
/* Location: ./system/core/Loader.php */
