<?php

class PhpLogReader implements \Sebb767\AnguLog\ILogReader {
    public function readData()
    {
        $path = ini_get('error_log'); // get the error log path
        $errors = explode("\n", file_get_contents($path)); // get all errors
        $data = array();
        
        $matches = array();
        // parse each error by line
        foreach($errors as $e)
        {
            //function eds($error, $level, $time, $file = null, $line = null)
            // check for default php error
            if(preg_match(';^\[([A-Za-z0-9\-:\./]+)\] PHP (\w+): (.*) in ([A-Za-z0-9/\\\-+\s]+) on line (\d+);i', $e, &$matches))
            {
                $level = 0;
                switch($matches[1]) {
                    case 'Notice':
                        return 200;
                        break;
                    case 'Warning':
                        return 300;
                        break;
                    case 'Fatal error':
                        return 400;
                        break;
                    default:
                        return 200; // default to notice
                        break;
                }
                $data[] = eds($matches[2], $level, strtotime($matches[0]), $matches[3], $matches[4]);
            }
            else
            {
                // no standard message -> "[time] custom message"
                // will default to notice
                $br = strpos($e, ']');
                $data[] = eds(strpos(e, $br+1), 200, strtotime(substr($e, 1, $br)));
            }
        }
        
        return $data;
    }
}