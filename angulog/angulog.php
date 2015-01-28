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

$output = true; // wether to output our HTML
$config = new config();

if($output): ?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AnguLog - A logviewer app built with angular">
    <meta name="author" content="Sebastian Kaim">

    <title>Starter Template for Bootstrap</title>

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
    padding-left: 25px;
    padding-right: 25px;
    padding-top: 15px;
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

.content {
    margin-top: 60px;
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
    $scope.active = false;
    var trying = false;
    
    $scope.login = function() {
        if($scope.trying) return;
        $scope.trying = true;
        
        $http({ url: '?api=login', method: "POST", params:{ name: $scope.name, pw: $scope.pw } }).
        success(function(data, status, headers, config) {
            $scope.trying = false;
            if(data.success)
            {
                username = data.username;
                $rootScope.$emit('logged_in');
                $scope.active = false;
            }
            else
                alert(data.error);
        }).error(function(data, status, headers, config) {
            alert("Failed to log in!\nPlease retry.");
            $scope.trying = false;
        });
    };
    
    $rootScope.$on('log_out', function(event, data) {
        $http({ url: '/c', method: "GET", params:{ api: "logout" } }).
        success(function(data, status, headers, config) {
            $scope.trying = false;
            if(data.success)
            {
                username = data.username;
                $rootScope.$emit('logged_out');
                $scope.active = true;
                hpush('Spielauswahl', '/c?logout');
            }
            else
                alert(data.error);
        }).error(function(data, status, headers, config) {
            alert("Failed to log out!\nPlease retry.");
            $scope.trying = false;
        });
    });
}]);
    </script>
    
  </head>

  <body ng-app="AnguLog">

    <nav class="navbar">
      <div class="navbar-container">
        <div class="appname"><?php echo $config->appname; ?></div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="navbar-nav" ng-controller="refreshController as rc">
            <li><a href="#">Refresh {{ rc.refreshing ? 'On' : 'Off' }}</a></li>
            <li><a href="<?php echo $config->impress; ?>">Impress</a></li>
            <li><a href="https://sebb767.de/prgramme/angulog">AnguLog Website</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="content" ng-controller="loginController" ng-show="active">

      <h2>Hello, World!</h2>

    </div><!-- /.content -->
</body></html>
<?php endif;