<?php
/**
 * Created by PhpStorm.
 * User: Benoit
 * Date: 06/08/2015
 * Time: 08:48
 */

require_once (__DIR__ . '/../config/config.php');
require_once (__DIR__ . '/../filter/Filter.class.php');
require_once (__DIR__ . '/../include/Db.class.php');
require_once (__DIR__ . '/../include/SendMail.class.php');


class Core {

    public $input = array();
    public $register = array();

    public function __construct() {
        $Filter = new Filter();
        $this->input = $Filter->filterInputVars();
    }

    public function Manage() {
        $this->show('home');
    }

    private function show($page) {
        include (__DIR__ . '/../template/' . $page . '.php');
    }

}




