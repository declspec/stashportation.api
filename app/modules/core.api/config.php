<?php
require(__DIR__ . '/services/api.php');
require(__DIR__ . '/controllers/error.php');

return function($dm) {
    $module = $dm->module("core.api", array("core"));
    
    $module->service("ApiService");
    $module->controller("ErrorController");
};