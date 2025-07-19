<?php
session_start();
session_unset();
session_destroy();

if (headers_sent()) {
    echo "Les headers ont déjà été envoyés, impossible de faire une redirection.";
    exit;
}

header("Location: ../index.php");
exit;
?>
