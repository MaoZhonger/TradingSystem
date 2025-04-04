<?php
include "imports.php";

function authenticateUser()
{
    if(!isset($_COOKIE["auth"]))
    {
        header("Location: auth.php");
        exit();
    }
}
?>