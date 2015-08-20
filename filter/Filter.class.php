<?php
/**
 * Created by PhpStorm.
 * User: Benoit
 * Date: 17/08/2015
 * Time: 11:28
 */

class Filter {

    private $InputVars = array();

    function __construct() {
        $this->InputVars = $_REQUEST;
    }

    public function filterInputVars() {
        $return = array();
        foreach ($this->InputVars as $key => $value) {
            switch ($key) {
                default:
                    $return[$key] = $value;
                    break;
            }
        }
        return $return;
    }

}