<?php
include "imports.php";

class TableBuilder
{
    public $html;

    function __construct(&$arr)
    {
        $this->html = "<table>\n";
        $headers = array_keys($arr[array_key_first($arr)]);
        $this->addHeaders($headers);

        foreach($arr as $row)
        {
            $this->addRow($row);
        }
        
    }

    function addHeaders($headers)
    {
        $string = "<tr>\n";
        foreach($headers as $header)
        {
            $string .= "<th>".$header."</th>\n";
        }
        $string .= "</tr>\n";
        $this->html .= $string;
    }

    function addRow(&$row)
    {
        $string = "<tr>\n";
        foreach($row as $field)
        {
            $string .= "<td>".$field."</td>\n";
        }
        $string .= "</tr>\n";
        $this->html .= $string;
    }

    function displayTable()
    {
        $this->html .= "</table>";
        echo $this->html;
    }
}

?>