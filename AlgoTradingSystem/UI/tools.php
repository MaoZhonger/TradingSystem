<?php
include "imports.php";

function authenticateUser()
{
    if(!isset($_COOKIE["auth"]))
    {
        header("Location: ../../AlgoTradingSystem/UI/auth.php");
        exit();
    }
}
?>