<?php

require_once('../../includes/functions.php');
require_once "../../includes/constant.php";


session_unset();
session_destroy();
$functions->response(null, 200, MESSAGE_LOGOUT_SUCCESSFUL, null, true);
