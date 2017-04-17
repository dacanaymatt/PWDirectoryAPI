<?php
require_once(APP_PATH."core/database_handler.php");

/**
 * Backbone for all models
 * - must be inherited by all models
 *
 * @author Vince Urag
 */
class SR_Model {

    public $db;

    /**
     * Connect to the database
     */
    public function __construct() {
        $this->db = new DatabaseHandler();
        $this->db->newConnection();
    }

     public function load($class_name) {
        if(is_file(APP_PATH."models/".$class_name.".php")) {
            $this->$class_name = new $class_name();
        } else if(is_file(APP_PATH."libraries/".$class_name.".php")) {
            $this->$class_name = new $class_name();
        }
    }
}
