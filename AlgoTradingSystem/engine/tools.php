<?php
include "imports.php";

function columnSort(&$arr, $column)
    {
        uasort($arr, function($a, $b) use ($column) 
        {
            if ($a == $b) 
                return 0;  
            return ($a[$column] > $b[$column]) ? -1 : 1;
        }
        );
    }

?>