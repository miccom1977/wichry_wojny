<?php

/*
 * KOMPONENT: error
 */

class content extends EveryComponent
{

    protected $init;
    protected $db;
    protected $path;
    protected $twig;

    public function __construct(){}

    public function showMsg()
    {
        $this -> tmplData['file'] = 'error.html';
        $this -> tmplData['variables']['title'] = comp_error1.' - '.$this -> tmplData['variables']['title'];
        
        if (isset($_SESSION['printError'])) $this -> tmplData['variables']['message'] = $_SESSION['printError'];
        else $this -> tmplData['variables']['message'] = comp_error2;
    }
}

?>