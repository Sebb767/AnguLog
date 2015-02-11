var app = angular.module('AnguLog',[], function($interpolateProvider) {
        // here was a config option once ...
	});

app.controller("loginController", ['$scope','$http', '$rootScope', function($scope, $http, $rootScope)
{ 
    $scope.active = !config.logged_in;
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

app.controller("logController", ['$scope','$http', '$rootScope', '$window', 'API', '$timeout', '$sce',
    function($scope, $http, $rootScope, $window, api, $timeout, $sce)
{ 
    // Check for new errors
    $scope.refreshing = false;
    
    $scope.stopRefresh = function () {
        $scope.refreshing = false;
        $rootScope.$emit('refresh_off');
    };
    
    $scope.startRefresh = function () {
        if($scope.refreshing)
            return; // we have our interval running
            
        $scope.refreshing = true;
        $rootScope.$emit('refresh_on');
        
        if(!refreshRunning)
            $scope.refresh();
    };
    
    $scope.toggleRefresh = function () {
        if($scope.refreshing)
            $scope.stopRefresh();
        else
            $scope.startRefresh();
    };
    
    // load more div text
    $scope.lmdiv = 'Loading more ...';
    
    // wether a request is running
    refreshRunning = false; 
    
    // newest request
    $scope.newestRequest = '';
    // lowest entry
    $scope.oldestRequest = '';
    
    // some example data
    $scope.data = [];/*[
        { level: 100, line: 20, file: 'index.php', error: 'I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works. I\'m just a Info and I\'m here to show you how multiline works.', time: 12312312 },
        { level: 200, line: 22, file: 'index.php', error: 'You need to notice me, but I\'m not important.', time: 12351223423  },
        { level: 300, line: 22, file: 'index.php', error: 'I\'m a warning, better do something.', time: 13323123423 },
        { level: 400, line: 22, file: 'index.php', error: 'Oh Snap! There was an error!', time: 1234122123 },
        { level: 500, line: 12, file: 'index.php', error: 'Critical! Your App is down!', time: 12312332 }
    ];/**/
    
    // actually refresh
    $scope.refresh = function() {
        if(!$scope.refreshing)
            return; // don't refresh if we shouldn't
    
        if($scope.newestRequest === '') // initial request
        {
            api.request({ api: 'get' }, function(data) {
                $scope.data = data;
                $scope.newestRequest = data[0].id;
                $scope.refresh(); // inital refresh
                $scope.oldestRequest = data[data.length-1].id;
            });
        }
        else // normal update
        {
            api.request({ api: 'get', after: $scope.newestRequest }, function(data) {
                if(data.length > 0)
                {
                    $scope.data = data.concat($scope.data); // add data
                    $scope.newestRequest = data[0].id;
                }
                
                // timeout for new refresh
                $timeout($scope.refresh, config.refresh_time);
            });
        }
    };
    
    // pre-initialize dates for performance
    var today = null, yesterday = null,
        recheckDate = 0; // every 25th time 'date' will be refreshed
            // so that tabs opened over midnight won't bug
            // 0%25 == 0, so auto-init is built-in
    
    // format the time
    $scope.timeFormat = function(timestamp) {
        if(++recheckDate % 25 == 0)
        {
            today = moment().startOf('day');
            yesterday = moment().subtract(1, 'days');
        }
        
        var dt = moment.unix(timestamp);
        
        if (config.substituteNearDates)
        {
            var dts = dt.clone().startOf('day');
            
            if(dts.isSame(today))
                return dt.format('[Today], H:mm:ss');
            if(dts.isSame(yesterday))
                return dt.format('[Yesterday], H:mm:ss');
        }
        
        // need to recreate since .startOf deletes the hour
        return dt.format(config.dateFormat);
    };
    
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
    
    // (re)activate this controller when the user logs in
    $rootScope.$on('logged_in', function() { $scope.activate(); });
    // deactivate on log out
    $rootScope.$on('logged_out', function() { $scope.deactivate(); });
    // Refresh button
    $rootScope.$on('toggle_refresh', function() { $scope.toggleRefresh(); });
    
    // called to hide & deactivate this
    $scope.deactivate = function() {
        $scope.active = false;
        $scope.data = []; // clear data so that another user
        atEnd = false;
        $scope.newestRequest = ''; // ... won't find old logs
        $scope.oldestRequest = '';
    };
    
    // reactivate this controller
    $scope.activate = function() {
        $scope.data = []; // if a late request filled it
        $scope.active = true;
        $scope.startRefresh();
    };
    
    // return wether the scope is active (for API service)
    isActive = function() { return $scope.active };
    
    // scroll event
    $window.onscroll = function(ev) {
        // http://stackoverflow.com/questions/9439725/javascript-how-to-detect-if-browser-window-is-scrolled-to-bottom
        if ($scope.active && !loadingMore && $scope.oldestRequest != '' &&
            (((document.documentElement && document.documentElement.scrollTop) 
            || document.body.scrollTop) + window.innerHeight) + 50 >= 
            ((document.documentElement && document.documentElement.scrollHeight) || document.body.scrollHeight)) {
            loadMore();
        }
    };
    
    var loadingMore = false;
    var atEnd = false;
    loadMore = function()
    {
        if(loadingMore || atEnd)
            return; // already refreshing
        loadingMore = true;
    
        api.request({ api: 'get', bottom: $scope.oldestRequest }, function(data) {
                loadingMore = false;
                if(data.length > 0) // add data
                {
                    $scope.data = $scope.data.concat(data); // add data
                    $scope.oldestRequest = data[data.length-1].id;
                }
                else
                {
                    atEnd = true;
                    $scope.lmdiv = 'No more entries!';
                }
            }, function() {
                loadingMore = false;
                return true;
            });
    }
    
    
    // if we start out with this controller, refresh
    if(config.logged_in)
        $scope.activate();
}]);

// second controller to control navbar; mirror of logController
app.controller('logCtrlController', ['$scope', '$rootScope', '$http', 'API',
    function($scope, $rootScope, $http, api) 
{
    $scope.active = config.logged_in;
    $scope.refreshing = config.logged_in;
    
    $scope.toggleRefresh = function() {
        $rootScope.$emit('toggle_refresh');
    };
    
    $rootScope.$on('refresh_on', function() { $scope.refreshing = true; });
    $rootScope.$on('refresh_off', function() { $scope.refreshing = false; });
    
    $scope.logout = function() {
        api.request({ api: 'logout'}, function(data) {
            $rootScope.$emit('logged_out');
            $scope.active = false;
        });
    };
    
    $rootScope.$on('logged_in', function () { $scope.active = true; });
    
}]);

app.controller('statusController', ['$scope', '$rootScope', 
function($scope, $rootScope)
{ 
    // status
    healthy = true;
    $scope.status = 'Connected to the service.';
    $scope.statusCSS = function()
    {
        alert('n');
        return healthy ? 'webrequest-okay' : 'webrequest-error';
    };
}]); //*/

app.controller("configController", ['$rootScope', function($rootScope)
{ 
    // updates the config
    $rootScope.$on('logged_in', function() { config.logged_in = true; });
    $rootScope.$on('logged_out', function() { config.logged_in = false; });
}]);

app.factory('API', ['$http', '$rootScope', function API($http, $rootScope) {
	var ApiFactory = {
	    handleError: function (data, status) {
	        if(status !== 0)// = no client error
	        {
    	        if(!config.logged_in)
    	            return; // not active
    	    
                alert('Error (' + status + '): '+ data.error); // Show error message
                
                if(data.reload) // fatal error -> reload
                    $window.location.reload();
	        }
        },
	
        request: function(params, fn, failfn = null) { 
            $http({ url: '?', method: "GET", params: params }).
            success(function(data, status, headers, config) {
                if(data.success) 
                {
                    fn(data.data); // done - call callback
                }
                else ApiFactory.handleError(data, status); // handle failure
            }).error(function(data, status, headers, config) {
                if(failfn === null || failfn())
                    ApiFactory.handleError(null, 0);
            });
        },
	};
	return ApiFactory;
}]);