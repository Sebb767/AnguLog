<?php
namespace Sebb767\AnguLog;

// 
// configuration
//
class config
{
    public $appname = 'myApp'; // Name der App
    public $impress = '//127.0.0.1/impressum'; // URL zum Impressum
    public $mode = 'syslog'; // which mode to use.  
    public $logpath = ''; // path to logfile if $mode == 'logfile'
    
    // This is the array for the available modes
    // To create a mode, implement a class that implements ILogInterpreter
    // The PHP Log Interpreter is done here as example
    public $modes = array(
            
        );
    
    public $sessionName = 'AL-Session-Data'; // the name to use for the session array
    
    // Function to login a user; return true when successful
    public function login($username, $password) 
    {
        return $username == 'root' && $password == '123qwery!';
    }
}

//
// code - do not change anything below here if you aren't sure what you're doing
//

define('AL_VERSION', '0.0.3'); // Version: Major.Minor.Bugfix
header('X-Powered-By', 'AnguLog '.AL_VERSION); // some self-promotion
@session_start(); // start session in case it's not done already

$config = new config(); // create config

//
// helper functions
//

// print error in json format and exit
function error($msg, $exit = true)
{
    echo json_encode(array('error' => $msg, 'success' => false));
    if($exit)
        exit;
}

// give data in json format and exit
function success($data, $exit = true)
{
    $data['success'] = true; // add succes to data
    $data['error'] = '';
    
    echo json_encode($data);
    if($exit)
        exit;
}

//
// interface for log readers
//

interface ILogReader
{
    // Read the data and return it
    public function readData($config);
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
            // angular.js sends post data in json format so that php doesn't recognize it
            $post = json_decode(file_get_contents("php://input"), true); // stupid angular!
            if(!isset($post['name']) || !isset($post['pw'])) // check for supplied data
            {
                error('You have to give username and password!');
            }
            else
            {
                if($config->login($post['name'], $post['pw'])) // call the user-supplied login function
                {
                    $_SESSION[$config->sessionName] = true;
                    success(array());
                }
                else
                {
                    error('Wrong username or password!');
                }
            }
            break;
            
        case 'logout': // log out the user
            $_SESSION[$config->sessionName] = false;
            success(array());
            break;
            
        default:
            error('Invalid API function: '.htmlentities($_GET['api']));
            break;
    }
    
    // the app should have exited with a json response by now!
    throw new Exception("API didn't exit with JSON response!\nPlease file a bug report.");
} 

//
// HTML
//

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AnguLog - A logviewer app built with angular">
    <meta name="author" content="Sebastian Kaim">

    <title>AnguLog Logviewer for <?php echo $config->appname; ?></title>

    <style>
<?php include('angulog.css'); ?>
    </style>
    <script><!-- angular js -->
<?php include('angular.min.js'); ?>
    </script>
    <script><!-- wether it is logged in --> 
var logged_in = <?php 

// Submit to the js wether the user is logged in
// could have been simpler, but I made it this way for readability
if(isset($_SESSION[$config->sessionName]) && $_SESSION[$config->sessionName])
    echo 'true';
else
    echo 'false';
    
?>;
    </script>
    <script><!-- actual js code -->
var app = angular.module('AnguLog',[], function($interpolateProvider) {
        // here was a config option once ...
	});
    
app.controller("loginController", ['$scope','$http', '$rootScope', function($scope, $http, $rootScope)
{ 
    $scope.active = !logged_in;
    $scope.error = ''; // login errors
    $scope.showerror = false; // wether to show the error field of the login mask
    var trying = false; // wether the ctrl is currently trying to login
    
    $scope.login = function() {
        if($scope.trying) return;
        $scope.trying = true;
        
        $http.post('?api=login', { name: $scope.name, pw: $scope.pw }). // try to log in
        success(function(data, status, headers, config) {
            $scope.trying = false;
            if(data.success) // logged in
            {
                $scope.deactivate();
                $rootScope.$emit('logged_in');
            }
            else
            {
                $scope.showerror = true;
                $scope.error = data.error;
            }
        }).error(function(data, status, headers, config) {
            alert("Server Error (" + status + ")!\nPlease retry.");
            $scope.trying = false;
        });
    };
    
    // reactivate this controller when the user logs out
    $rootScope.$on('logged_out', function(event, data) { $scope.activate(); });
    
    // called to hide & deactivate this
    $scope.deactivate = function() {
        $scope.pw = ''; // reset password
        $scope.showerror = false;
        $scope.active = false;
    };
    
    // reactivate this 
    $scope.activate = function() {
        $scope.active = true;
    };
}]);

app.controller("logController", ['$scope','$http', '$rootScope', function($scope, $http, $rootScope)
{ 
    // Check for new errors
    $scope.refreshing = true;
    
    $scope.stopRefresh = function () {
        $scope.refreshing = false;
    };
    
    $scope.startRefresh = function () {
        if($scope.refreshing)
            return; // we have our interval running
            
        $scope.refreshing = true;
        //todo: start refresh interval
    };
    
    $scope.toggleRefresh = function () {
        if($scope.refreshing)
            $scope.stopRefresh();
        else
            $scope.startRefresh();
    };
    

    $scope.data = [
        { level: 100, line: 20, file: 'index.php', error: 'I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works.', time: 12312312 },
        { level: 200, line: 22, file: 'index.php', error: 'You need to notice me, but I\'m not important.', time: 123123 },
        { level: 300, line: 22, file: 'index.php', error: 'I\'m a warning, better do something.', time: 123123 },
        { level: 400, line: 22, file: 'index.php', error: 'Oh Snap! There was an error!', time: 123123 },
        { level: 500, line: 12, file: 'index.php', error: 'Critical! Your App is down!', time: 12312332 }
    ];
    
    $scope.active = logged_in;
    
    // returns a CSS class for an error level
    $scope.levelToCSS = function (level) {
        if(level < 200)
            return 'error-debug'; // gray
        if(level < 300)
            return 'error-notify'; // blue
        if(level < 400)
            return 'error-warning'; // orange
        if(level < 500)
            return 'error-error'; // red
        return 'error-emergency'; // red-black
    };
    
    $scope.logout = function() {
        $http.get('?api=logout'). // try to log in
        success(function(data, status, headers, config) {
            $rootScope.$emit('logged_out');
        }).error(function(data, status, headers, config) {
            alert("Server Error (" + status + ")!\nPlease retry.");
        });
    };
    
    // (re)activate this controller when the user logs in
    $rootScope.$on('logged_in', function(event, data) { $scope.activate(); });
    
    // this is a fix; calling deactivate from the http response doesn't work
    $rootScope.$on('logged_out', function(event, data) { $scope.deactivate(); });
    
    // called to hide & deactivate this
    $scope.deactivate = function() {
        $scope.active = false;
    };
    
    // reactivate this controller
    $scope.activate = function() {
        $scope.active = true;
    };
}]);
    </script>
    
  </head>

  <body ng-app="AnguLog">

    <nav class="navbar">
      <div class="navbar-container">
        <div class="appname"><?php echo $config->appname; ?></div>
        <nav id="navbar" class="">
          <ul class="navbar-nav" ng-controller="logController as lc">
            <li ng-show="active"><a ng-click="toggleRefresh()">Refresh {{ refreshing ? 'On' : 'Off' }}</a></li>
            <li><a href="<?php echo $config->impress; ?>">Impress</a></li>
            <li ng-hide="active"><a href="https://sebb767.de/programme/angulog" target="_blank">AnguLog Website</a></li>
            <li ng-show="active"><a ng-click="logout()">Log out</a></li>
          </ul>
        </nav><!--/.nav-collapse -->
      </div>
      <span class="powered-by"><span class="powered-by-al">Powered by</span> AnguLog <?php echo AL_VERSION; ?></span>
    </nav>

    <div class="content" ng-controller="logController" ng-show="active">

      <div ng-repeat="item in data" ng-class="['error-container', levelToCSS(item.level) ]">
        <div class="error-box">{{ item.error }}</div>
        <div class="error-details" ng-show="(item.file !== undefined && item.file != '')">
            In <span class="error-file">{{ item.file }}</span>
                <span ng-show="(item.line !== undefined && item.line != '')"> 
                    on <span class="error-line">line {{ item.line }}</span>
                </span>.
        </div>
      </div>

    </div><!-- /.content -->
    
    <div class="content signin" ng-controller="loginController" ng-show="active">
      <form ng-submit="login()" ui-keypress="{13:'login($event)'}" >
        <h2 class="form-signin-heading">Please sign in</h2>
        <div type="text" class="input input-error" ng-show="showerror"><span class="signin-error">Error!</span> {{ error }}</div>
        <input type="text" id="username" class="input" placeholder="Username" ng-model="name" required="" autofocus="" ng-enabled="$scope.trying">
        <input type="password" id="password" class="input" placeholder="Password" ng-model="pw" required="">
        <button class="input input-btn" type="submit">Sign in</button>
      </form>
    </div>
</body></html>