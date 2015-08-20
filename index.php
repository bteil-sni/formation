<?php

require_once (__DIR__ . '/core/Core.class.php');

try  {
    $Core = new Core();
    $Core->Manage();
}
catch(Exception $e) {
    die(nl2br(Tools_GetExceptionTrace($e)));
}