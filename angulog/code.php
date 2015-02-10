<?php
// This file contains the main code

namespace Sebb767\AnguLog;

header('X-Powered-By', 'AnguLog '.AL_VERSION); // some self-promotion
@session_start(); // start session in case it's not done already

$config = new config(); // create config

//
// helper functions 
//

// print error in json format and exit
function error($msg, $exit = true, $reload = false)
{
    echo json_encode(array('error' => $msg, 'success' => false, 'reload' => $reload));
    if($exit)
        exit;
}

// give data in json format and exit
function success($data = null, $exit = true)
{
    echo json_encode(array(
            'success' => true,
            'error' => '',
            'reload' => false,
            'data' => $data
        ));
    if($exit)
        exit;
}

// function to create an error data set
function eds($error, $level, $time, $file = null, $line = null)
{
    return array(
            'id' => $time.'+'.hash('crc32', $error), // create id
            'error' => $error,
            'level' => $level,
            'time' => $time,
            'file' => $file,
            'line' => $line
        );
}

// function to get an array element or return a default value
function gt(&$array, $index, $default = null)
{
    if(isset($array[$index])) // check wether elem exists
        return $array[$index];
        
    return $default; // return default
}

// function to initialize and execute log reader
function readLogData()
{
    // create cfg
    $cfg = new config();
    
    // create the logreader; read+return the data
    return $cfg->modes[$cfg->mode]($cfg)->readData();
}

// convert id [timestamp]+[crc32] to array 
function idToData($id, $cmp = null)
{
    return array(
            substr($id, 0, -9), // timestamp
            substr($id, -8)
        );
    
}

// compares an $id to an id-string
function idCmp($id, $cmp)
{
    return $id[0] == substr($cmp, 0, -9)
        && $id[1] == substr($cmp, -8);
}

// converts an array of values to a js object
function arrayToJS($array)
{
    $ret = '{';
    foreach ($array as $name => $value)
    {
        $ret .= $name.':\''.addslashes($value).'\',';
    }
    return substr($ret, 0, -1). // remove last ,
        '}';
}

//
// API
//

if(isset($_GET['api'])) // wether there is an API function called
{
    header('Content-Type', 'text/json'); // API will >always< output json and exit in this closure
    switch ($_GET['api']) 
    {
        case 'login': // log in to the user interface
            //for($i = 0; $i < 1e7; $i++) echo '';
            // angular.js sends post data in json format so that php doesn't recognize it
            $post = json_decode(file_get_contents('php:/'.'/input'), true); // stupid angular! ; . is for minification
            if(!isset($post['name']) || !isset($post['pw'])) // check for supplied data
            {
                error('You have to give username and password!');
            }
            else
            {
                if($config->login($post['name'], $post['pw'])) // call the user-supplied login function
                {
                    $_SESSION[$config->sessionName] = true;
                    success();
                }
                else
                {
                    error('Wrong username or password!');
                }
            }
            break;
            
        case 'logout': // log out the user
            $_SESSION[$config->sessionName] = false;
            success();
            break;
            
        case 'get': // query data
            if(!$config->checkLogin()) 
                error('You need to be logged in for this!', true, true);
            
            if($bt = gt($_GET, 'bottom', false)) // get older entries 
                // (older than ?bottom)
            {   
                $bt = idToData($bt);
                $data = readLogData();
                $c = count($data);
                for($i = 0; $i < $c; $i++)
                {
                    if(idCmp($bt, $data[$i]['id'])) // found our bottom element
                    {
                        success(array_slice($data, ++$i, $config->loadCount));
                    }
                }
                error('No such error! (-> '.$bt[0].'+'.$bt[1].')');
            }
            
            if($bt = gt($_GET, 'after', false)) // get new entries after ?after
            {   
                $bt = idToData($bt);
                $data = readLogData();
                $c = count($data);
                for($i = 0; $i < $c; $i++)
                {
                    if(idCmp($bt, $data[$i]['id'])) // found the last element the client has
                    {   
                        success(array_slice($data, 0, $i)); // return newer entries
                    }
                }
                error('No such error! (-> '.$bt[0].'+'.$bt[1].')');
            }
            
            // return last 'initialCount' errors (initial request)
            success(array_slice(readLogData(), 0, $config->initialCount));
            break;
        
        case 'version':
            success(AL_VERSION);
            break;
            
        default:
            error('Invalid API function: '.htmlentities($_GET['api']));
            break;
    }
    
    // the app should have exited with a json response by now!
    throw new Exception("API didn't exit with JSON response!\nPlease file a bug report.");
} 