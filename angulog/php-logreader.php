<?php
namespace Sebb767\AnguLog;

class PhpLogReader implements ILogReader 
{
    
    private $cfg = null;
    private $log = null;
    
    public function __construct($config, $file = null)
    {
        $this->cfg = $config;
        
        if($file == null)
            $this->log = ini_get('error_log'); // fall back to error log if
        else                                    // no file is given
            $this->log = $file;
    }
    
    public function readData()
    {
        $errors = explode("\n", file_get_contents($this->log)); // get all errors
        $data = array();
        
        $matches = array();
        // parse each error by line
        foreach($errors as $e)
        {
            if(trim($e) == '')
                continue; // empty line
            
            $matches = array(); // clear matches
            // check for default php error [A-Za-z0-9/\\-+\s]+   
            $s = ' '; // for min 
            if(preg_match(";^\\[([A-Za-z0-9-:\\./\\s]+)\\]$s(PHP$s)?([A-Za-z$s]+):$s(.*?)(\\s+in$s(.*)$s"."on$s"."line$s(\\d+))?".'$;i', $e, $matches))
            {
                $level = 0;
                switch(strtolower($matches[3])) {
                    case 'notice':
                        $level = 200;
                        break;
                    case 'warning':
                        $level = 300;
                        break;
                    case 'fatal error':
                        $level = 400;
                        break;
                    default:
                        $level = 200; // default to notice
                        break;
                }
                
                //function eds($error, $level, $time, $file = null, $line = null)
                $data[] = \Sebb767\AnguLog\eds($matches[4], $level, 
                    strtotime($matches[1]), 
                    \Sebb767\AnguLog\gt($matches, 6, ''), 
                    \Sebb767\AnguLog\gt($matches, 7, ''));
            }
            else
            {
                // no standard message -> "[time] custom message"
                // will default to notice
                $br = strpos($e, ']');
                $data[] = \Sebb767\AnguLog\eds(strpos($e, $br+1), 200, strtotime(substr($e, 1, $br)));
            }
        }
        
        // reverse and return data
        return array_reverse($data);
    }
}