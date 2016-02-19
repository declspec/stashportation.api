<?php
require(__DIR__ . '/../app/application.php');

// Check the environment (default to one called 'development')
$env = isset($_ENV["PHP_ENV"]) ? $_ENV["PHP_ENV"] : "development";

$app = Application::bootstrap(array("stash"), $env, function($app, $controller) {
    // This function is invoked with the dependency injector so the parameters can 
    // be the name of any injectable service/constant.
    
    // Instantiate the controllers needed.
    $errorController = $controller->create("ErrorController");
    $stashController = $controller->create("StashController");
    
    // Stash routes
    $app->get("/stash/:id", array($stashController, "get"));
    $app->post("/stash/:id", array($stashController, "update"));
    $app->post("/stash", array($stashController, "create"));
    
    $app->get("/stash", array($stashController, "search"));
   
    // Error/missing route handlers.
    $app->middleware(array($errorController, "notFound"));
    $app->error(array($errorController, "serverError"));
});

$app->run();