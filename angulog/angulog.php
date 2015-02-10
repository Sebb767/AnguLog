<?php
namespace Sebb767\AnguLog;

// 
// configuration
//
class config
{
    public $appname = 'myApp'; // Name der App
    public $impress = '//127.0.0.1/impressum'; // URL zum Impressum
    public $mode = 'php-error-log'; // which mode to use.  
    public $logpath = ''; // path to logfile if $mode == 'logfile'
    public $substituteNearDates = true; // wether to replace todays and
        // yesterdays dates with the respective phrase
    public $dateFormat = 'MMMM Do YYYY, H:mm:ss'; // format for log dates
    public $initialCount = 50; // how many errors to return on the initial request
    public $loadCount = 25; // how many entries will be loaded by default when 
        // requesting older errors
    public $refreshTime = 400; // time between refreshes in ms
    
    // This is the array for the available modes
    // To create a mode, implement a class that implements ILogInterpreter
    // The PHP Log Interpreter is done here as example
    // you may include php files in your closure
    public $modes = array();
    public function __construct() // initialize our mode array
    {
        $this->modes['php-error-log'] = function($config) {
            // file is included below to ease minification
            return (new PhpLogReader($config, '/var/log/php-fpm.log')); //'/var/log/php-fpm.log'
        };
    }
        
    public $sessionName = 'AL-Session-Data'; // the name to use for the session array
    
    // Function to login a user; return true when successful
    public function login($username, $password) 
    {
        return $username == 'root' && $password == '123qwery!';
    }
    
    // check wether an user is logged in
    public function checkLogin()
    {
        $config = new config();
        return (isset($_SESSION[$config->sessionName]) && $_SESSION[$config->sessionName]);
    }
}

//
// interface for log readers (use it as reference, don't edit if you wan't this working)
//
interface ILogReader
{
    // Read the data and return it
    public function readData();
    /* You have to return arrays of the following array
     * [] => (
     *   'id' => "[timestamp]+[crc32 of error msg]",
     *   'error' => 'message of your error',
     *   'level' => [importance as int],
     *   'time'  => [errors timestamp as int],
     *   (optional) 'file' => [Filename],
     *   (optional) 'line' => [Line w/ Error]
     * )
     * take a look at the helper function 'eds'. You have to return the data
     * in inverse time order, so newest = [0], oldest = [n-1].
    **/
}

//
// code - do not change anything below here if you aren't sure what you're doing
#!minify

define('AL_VERSION', '0.0.8'); // Version: Major.Minor.Bugfix

include('php-logreader.php');
include('code.php');

//
// HTML
//

include('html.php');
