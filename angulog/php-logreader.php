<?php

class PhpLogReader implements \Sebb767\AnguLog\ILogReader {
    public function readData()
    {
        $path = ini_get('error_log'); // get the error log path
        $errors = explode("\n", file_get_contents($path));
        $data = array();
    }
}