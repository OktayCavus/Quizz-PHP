<?php

require_once('../../includes/functions.php');

session_unset();
session_destroy();
response(null, 200, "Çıkış başarılı", null, true);
