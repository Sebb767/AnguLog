<!DOCTYPE html><!-- This is the HTML output of angulog. It's outsourced to allow minification. -->
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AnguLog - A logviewer app built with angular">
    <meta name="author" content="Sebastian Kaim">

    <title>AnguLog Logviewer for <?php echo $config->appname; ?></title>

    <!-- inline style sheet -->
    <style>
<?php include('angulog.css'); ?>/*-#!css-*/
    </style>
    
    <script>var config=<?php
echo \Sebb767\AnguLog\arrayToJS(array(
    'refresh_time' => $config->refreshTime,
    'logged_in' => $config->checkLogin(),
    'substituteNearDates' => $config->substituteNearDates ? '1' : '',
    'dateFormat' => $config->dateFormat, 
));
    ?>;
/*-#!js-*/
<?php include('external/angular.min.js'); ?>
<?php include('external/moment.min.js'); ?>

/* actual js code */
<?php include('angulog.min.js'); ?>
    </script>
    
  </head>

  <body ng-app="AnguLog">

    <nav class="navbar">
      <div class="navbar-container">
        <div class="appname"><?php echo $config->appname; ?></div>
        <nav id="navbar" class="">
          <ul class="navbar-nav" ng-controller="logCtrlController as lc">
            <li ng-show="active"><a ng-click="toggleRefresh()">Refresh {{ refreshing ? 'On' : 'Off' }}</a></li>
            <li><a href="<?php echo $config->impress; ?>">Impress</a></li>
            <li ng-hide="active"><a href="https://github.com/Sebb767/AnguLog" target="_blank">AnguLog Website</a></li>
            <li ng-show="active"><a ng-click="logout()">Log out</a></li>
          </ul>
        </nav><!--/.nav-collapse -->
      </div>
      <span class="powered-by">
        <span class="powered-by-al">Powered by</span>&nbsp;
        <a class="al-version" href="https://github.com/Sebb767/AnguLog" target="_blank">AnguLog <?php echo AL_VERSION; ?></a>
      </span>
    </nav>

    <div class="content-error" ng-controller="logController" ng-show="active">

      <div ng-repeat="item in data" ng-class="['error-container', levelToCSS(item.level) ]">
        <div class="error-box">{{ item.error }}</div>
        <div class="error-details">
            <span ng-show="(item.file !== undefined && item.file != null && item.file != '')">
                In <span class="error-file">{{ ::item.file }}</span>
                    <span ng-show="(item.line !== undefined && item.line != null && item.line != '')"> 
                        on <span class="error-line">line {{ ::item.line }}</span>
                    </span>.
            </span>
            <span ng-hide="(item.file !== undefined && item.file != null && item.file != '')" 
                class="error-no-data">
                No data available</span>
            <span class="error-time">{{ timeFormat(item.time) }}</span>
        </div>
      </div>
      
      <br>
      
      <div class="ld-more">{{ lmdiv }}</div>
      
    </div>
    
    <div class="content signin" ng-controller="loginController" ng-show="active" ng-class="['content', 'signin', trying ? 'signin-onwait' : 'signin-normal']">
      <form class="signin-form" ng-submit="login()" ui-keypress="{13:'login($event)'}" >
        <h2 class="form-signin-heading">Please sign in</h2>
        <div type="text" class="input input-error" ng-show="showerror"><span class="signin-error">Error!</span> {{ error }}</div>
        <input type="text" id="username" class="input" placeholder="Username" ng-model="name" required="" autofocus="" ng-disabled="trying">
        <input type="password" id="password" class="input" placeholder="Password" ng-model="pw" required="" ng-disabled="trying">
        <button class="input input-btn" type="submit" ng-disabled="trying">Sign in</button>
      </form>
    </div>
</body></html>