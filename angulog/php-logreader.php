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
        $count = count($errors);
        for($i = 0; $i < $count; $i++)
        {
            $e = trim($errors[$i]);
            
            if(empty($e)) continue; // empty line
            
            $current_count = count($data);
            
            $matches = array(); // clear matches
            // check for default php error [A-Za-z0-9/\\-+\s]+   
            $s = '\\s+'; // whitespace identifier 
            if(preg_match(";^\\[([A-Za-z0-9-:\\./\\s]+)\\]$s(PHP$s)?([A-Za-z$s]+):$s(.*?)(\\s+in$s(.*)$s"."on$s"."line$s(\\d+))?".'$;i', $e, $matches))
            {
                $level = 0;
                switch(trim(strtolower($matches[3]))) {
                    case 'notice':
                        $level = 200;
                        break;
                    case 'warning':
                        $level = 300;
                        break;
                    case 'parse error':
                        $level = 400;
                        break;
                    case 'fatal error':
                        $level = 500;
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
            elseif(preg_match(";^\\[([A-Za-z0-9-:\\./\\s]+)\\]{$s}PHP{$s}(Stack{$s}trace\:)".'$;i', $e, $matches) && $current_count != 0) // php stacktrace?
            {
                $data[$current_count-1]['error'] .= "\n".$matches[2];
                $data[$current_count-1]['stack-trace'] = true;
            } /* */
            elseif(preg_match(";^\\[([A-Za-z0-9-:\\./\\s]+)\\]{$s}PHP{$s}(Stack{$s}trace\:)".'$;i', $e, $matches) && $current_count != 0) // php stacktrace?
            {
                $data[$current_count-1]['error'] .= "\n".$matches[2];
                $data[$current_count-1]['stack-trace'] = true;
            } 
            elseif(preg_match(";^\\[([A-Za-z0-9-:\\./\\s]+)\\]{$s}PHP$s(\\d+\\.{$s}.+)".'$;i', $e, $matches) 
                && $current_count != 0 && isset($data[$current_count-1]['stack-trace']))
            {
                $data[$current_count-1]['error'] .= "\n".$matches[2];
            }
            else
            {
                // no standard message -> "[time] custom message"
                // will default to notice
                $br = strpos($e, ']');
                if($br && $time = strtotime(substr($e, 1, $br-1))) // valid date
                {
                    $data[] = \Sebb767\AnguLog\eds(substr($e, $br+1), 200, $time);
                }
                else // invalid date / line -> add to previous
                {
                    $data[count($data)-1]['error'] .= "\n".$e;
                }
            }
        }
        
        // reverse and return data
        return array_reverse($data);
    }
}