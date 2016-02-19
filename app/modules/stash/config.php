<?php
require(__DIR__ . '/services/stash.php');
require(__DIR__ . '/controllers/stash.php');

return function($dm) {
    $module = $dm->module("stash", array("core.api"));
    
    $module->service("StashService");
    $module->controller("StashController");
};