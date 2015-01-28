<?php

// 
// configuration
//
class config
{
    public $appname = 'myApp'; // Name der App
    public $impress = '//127.0.0.1/impressum'; // URL zum Impressum
    public $mode = 'syslog'; // which mode to use. Avail: 'logfile', 'phplog', 'monolog' 
    public $logpath = ''; // path to logfile if $mode == 'logfile'
    
    // Function to login a user; return true when successful
    public function login($username, $password) 
    {
        return $username == 'root' && $password == '123qwery!';
    }
}

//
// code - do not change anything below here
//

define('AL_VERSION', '0.0.1'); // Version: Major.Minor.Bugfix
header('X-Powered-By', 'AnguLog '.AL_VERSION, true); // some self-promotion

$config = new config();

if(isset($_GET['API']))
{
    header('Content-Type', 'text/json', true);
    switch ($_GET['API']) 
    {
        case 'login':
            if(!isset($_GET['name']) || !isset($_GET['pw']))
            {
                echo json_export(array('error' => 'You have to give username and password!', 'success' => false));
            }
            else
            {
                if($config->login($_GET['name'], $_GET['pw']))
                {
                    echo json_export(array('error' => '', 'success' => true));
                }
                else
                {
                    echo json_export(array('error' => 'Wrong username or password!', 'success' => false));
                }
            }
            break;
    }
}
else { ?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AnguLog - A logviewer app built with angular">
    <meta name="author" content="Sebastian Kaim">

    <title>AnguLog Logviewer for <?php echo $config->appname; ?></title>

    <style>
.navbar {
    background-color: #222;
    border-color: #080808;
    top: 0;
    left: 0;
    right: 0;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    display: box;
    height: 50px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    position: fixed;
}    
.navbar-nav {
    padding-left: 0;
    margin: 0;
    list-style: none;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    list-style: none;
}
.navbar-nav > li {
    float: left;
    position: relative;
    display: block;
    background-color: #222;
    font-size: 18px;
    color: #9d9d9d;
    position: relative;
    display: block;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
.navbar-nav > li :hover {
    background-color: #080808;
}
.navbar-nav > li > a {
    color: #fff;
    text-decoration: none;
    display: block;
    padding-left: 25px;
    padding-right: 25px;
    padding-top: 15px;
    padding-bottom: 12px;
}
.appname {
    color: #9d9d9d;
    float: left;
    height: 50px;
    padding: 10px 15px;
    font-size: 24px;
    margin-left: 20px;
    font-weight: 400;
}
.powered-by {
    color: #9d9d9d;
    float: right;
    font-size: 14px;
    padding-top: 25px;
    margin-right: 20px;
}
.powered-by-al {
    font-style: italic;
}


.content {
    margin-top: 60px;
}
.signin {
    max-width: 350px;
    margin: 0 auto;
    margin-top: 80px;
}
.input {
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    margin-bottom: -1px;
    padding-left: 5px;
    width: 100%;
    height: 45px;
    font-size: 20px;
    box-sizing: border-box;
}
.input-error {
    color: #a94442;
    background-color: #f2dede;
    border-color: #a94442;
    padding-top: 10px;
    padding-left: 10px;
    padding-bottom: 5px;
    height: auto;
    font-size: 16px;
}
.input-btn {
    background-color: #286090;
    color: #FFF;
}
.signin-error {
    font-weight: 600;
}

    </style>
    <script>
<?php echo file_get_contents('angular.min.js'); ?>
    </script>
    <script>
var app = angular.module('AnguLog',[], function($interpolateProvider) {
        /*$interpolateProvider.startSymbol('#-');
        $interpolateProvider.endSymbol('-#');*/
	});
    
app.controller("loginController", ['$scope','$http', '$rootScope', function($scope, $http, $rootScope)
{ 
    $scope.active = true;
    $scope.error = '';
    $scope.showerror = false;
    var trying = false;
    
    $scope.login = function() {
        if($scope.trying) return;
        $scope.trying = true;
        
        $http({ url: '?api=login', method: "POST", params:{ name: $scope.name, pw: $scope.pw } }).
        success(function(data, status, headers, config) {
            $scope.trying = false;
            if(data.success)
            {
                $rootScope.$emit('logged_in');
                $scope.active = false;
            }
            else
            {
                $scope.showerror = true;
                $scope.error = data.error;
            }
        }).error(function(data, status, headers, config) {
            alert("Failed to log in!\nPlease retry.");
            $scope.trying = false;
        });
    };
}]);

app.controller("logController", ['$scope','$http', '$rootScope', function($scope, $http, $rootScope)
{ 
    $scope.active = false;
    
}]);
    </script>
    
  </head>

  <body ng-app="AnguLog">

    <nav class="navbar">
      <div class="navbar-container">
        <div class="appname"><?php echo $config->appname; ?></div>
        <nav id="navbar" class="">
          <ul class="navbar-nav" ng-controller="logController as lc">
            <li><a href="#">Refresh {{ rc.refreshing ? 'On' : 'Off' }}</a></li>
            <li><a href="<?php echo $config->impress; ?>">Impress</a></li>
            <li><a href="https://sebb767.de/programme/angulog" target="_blank">AnguLog Website</a></li>
          </ul>
        </nav><!--/.nav-collapse -->
      </div>
      <span class="powered-by"><span class="powered-by-al">Powered by</span> AnguLog <?php echo AL_VERSION; ?></span>
    </nav>

    <div class="content" ng-controller="logController as lc" ng-show="active">

      <h2>Hello, World!</h2>

    </div><!-- /.content -->
    
    <div class="content signin" ng-controller="loginController" ng-show="active">
      <form ng-submit="login()" ui-keypress="{13:'login($event)'}" >
        <h2 class="form-signin-heading">Please sign in</h2>
        <div type="text" class="input input-error" ng-show="showerror"><span class="signin-error">Fehler!</span> {{ error }}</div>
        <input type="text" id="username" class="input" placeholder="Username" ng-model="name" required="" autofocus="" ng-enabled="$scope.trying">
        <input type="password" id="password" class="input" placeholder="Password" ng-model="pw" required="">
        <button class="input input-btn" type="submit">Sign in</button>
      </form>
    </div>
</body></html>
<?php }