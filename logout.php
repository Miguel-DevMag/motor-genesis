<?php
session_start();
// destrói sessão e redireciona para login
session_unset();
session_destroy();
header("Location: login.php");
exit;
