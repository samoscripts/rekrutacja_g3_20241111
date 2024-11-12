<?php

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function includeView($view)
{
    include __DIR__ . "/View/$view.html";
}


if (isAjaxRequest()) {
    if (isset($_POST) && count($_POST) > 0 || isset($_GET) && count($_GET) > 0) {
        require_once 'autoloader.php';
        $frm = new \service\Form('mysql'); // 'mysql' or 'sqlite' based on your requirement
    }
    // Handle Ajax request
} else {
    includeView('header');
    includeView('form');
    includeView('footer');
    // Handle regular request
}