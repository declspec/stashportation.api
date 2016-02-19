<?php
require(__DIR__ . '/controllers/error.php');

return function($dm) {
    $module = $dm->module("core.web", array("core"));
    
    $module->controller("ErrorController");
};