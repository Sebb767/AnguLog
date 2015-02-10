#!/usr/bin/env php
<?php
// compile.php
// minifies angulog
// it's not THAT dynamic, but it's a helper - what did you expect?

// version
if($argc < 1)
    die('Usage: '.$argv[0].' [version]');
    
define('AL_VERSION', $argv[1]);

// helper to minify html
function minifyHTML($html) {
    $replace = array(
                    '/<!--[^\[](.*?)[^\]]-->/s' => '',
                    "/\n([\S])/"                => '$1',
                    "/\r/"                      => '',
                    "/\n/"                      => '',
                    "/\t/"                      => '',
                    "/ +/"                      => ' ',
                    "/> +</"                    => '><',
                    "/\" +>/"                   => '">',
                );
    return preg_replace(array_keys($replace), array_values($replace), $html);
}

// helper to minify php
function minifyPHP($code) {
    $replace = array(
                    ";//.*?\n;"                 => "",
                    ";/\\*(.|[\r\n])*?\\*/;"    => '',
                    "/\r/"                      => '',
                    "/\n/"                      => '',
                    "/\t/"                      => ' ',
                    "/ +/"                      => ' ',
                    "/; +/"                     => ';',
                    "/ +;/"                     => ';',
                    "/\\} +/"                   => '}',
                    "/\\] +/"                   => ']',
                    "/\\) +/"                   => ')',
                    "/\\{ +/"                   => '{',
                    "/\\[ +/"                   => '[',
                    "/\\( +/"                   => '(',
                    "/ +\\}/"                   => '}',
                    "/ +\\]/"                   => ']',
                    "/ +\\)/"                   => ')',
                    "/ +\\{/"                   => '{',
                    "/ +\\[/"                   => '[',
                    "/ +\\(/"                   => '(',
                    "/, +/"                     => ',',
                    "/ +,/"                     => ',',
                    "/= +/"                     => '=',
                    "/ +=/"                     => '=',
                    "/=> +/"                    => '=>',
                    "/ +=>/"                    => '=>',//*/
                    "/:/"                     => ':',
                    "/:/"                     => ':',
                );
    return preg_replace(array_keys($replace), array_values($replace), $code);
}

function stripNamespace($code, $namespace = 'Sebb767\\\\AnguLog') {
    return preg_replace('!namespace +'.$namespace.';!i', '', $code);
}

// reads the path, strips <?php and namespace, minifies
function readPHP($path, $strip_php = true) {
    $code = file_get_contents($path);
    if($strip_php)
        $code = substr($code, 5); // strip <?php
    $code = stripNamespace($code);
    return minifyPHP($code);
}

$path = './angulog/';

$out = file_get_contents($path.'angulog.php');
$out = substr($out, 0, strpos($out, '#!minify'))."//\n"; // strip includes

// we don't minify php that heavy (what for? that 2, 3 kb on a server download?), so we
// just add our code here with replaced whitespaces etc
$out .= readPHP($path.'code.php'); // the code
$out .= readPHP($path.'php-logreader.php'); // default log reader

// now we need our html
$html = file_get_contents($path.'html.php');

$includes = array();
preg_match('#<\\?php +include\\(\'([A-Za-z0-9\-\./]+)\'\\) +\\?>#ig', $html, $includes);

echo $includes;
