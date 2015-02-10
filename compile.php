#!/usr/bin/env php
<?php
// compile.php
// minifies angulog
// it's not THAT dynamic, but it's a helper - what did you expect?

// version
if($argc < 2)
    die('Usage: '.$argv[0]." [version] [-n]\n");
    
define('AL_VERSION', $argv[1]);

define('MIN_PHP', !(isset($argv[2]) && trim($argv[2]) == '-n'));
if(!MIN_PHP)
    echo "Not minifying php.\n";

// helper to minify html
function minifyHTML($html) {
    $replace = array(
                    '/<!--[^\[](.*?)[^\]]-->/s' => '',
                    "/\r/"                      => '',
                    "/\n/"                      => ' ',
                    "/\t/"                      => '',
                    "/ +/"                      => ' ',
                    "/> +</"                    => '><',
                    "/\" +>/"                   => '">',
                );
    return preg_replace(array_keys($replace), array_values($replace), $html);
}

// helper to minify css
function minifyCSS($css) {
    $replace = array(
                    ";/\\*(.|[\r\n])*?\\*/;"    => '',
                    "/\r/"                      => '',
                    "/\n/"                      => '',
                    "/\t/"                      => '',
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
                    "/ +:/"                     => ':',
                    "/: +/"                     => ':',
                );
    return preg_replace(array_keys($replace), array_values($replace), $css);
}

// helper to minify php
function minifyPHP($code) {
    if(!MIN_PHP)
        return $code;
    
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
                    "/ +=>/"                    => '=>',
                    "/: +/"                     => ':',
                    "/ +:/"                     => ':',
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

// add  version
$out .= "define('AL_VERSION', '".AL_VERSION."');";

// we don't minify php that heavy (what for? that 2, 3 kb on a server download?), so we
// just add our code here with replaced whitespaces etc
$out .= readPHP($path.'php-logreader.php'); // default log reader
$out .= readPHP($path.'code.php'); // the code
$out .= '?>'; // add trailing end

// now we need our html
$html = file_get_contents($path.'html.php');

$includes = array();// \? >
preg_match_all('#<\?php +include\(\'([A-Za-z0-9\-\./]+)\'\); +\?>#i', $html, $includes);

$count = count($includes[0]);
$js = '';
$css = '';
for($i = 0; $i < $count; $i++)
{
    if(substr($includes[1][$i], -3, 3) == 'css') // css
    {
        $css .= minifyCSS(file_get_contents($path.$includes[1][$i]));
        $html = str_replace($includes[0][$i], '', $html);
        echo "Reading ".$includes[1][$i]."\n";
    }
    else // js
    {
        if(substr($includes[1][$i], -6, 3) != 'min') // script.[min].js
        {
            echo "Minifying ".$includes[1][$i]."\n";
            $js .= exec('ng-annotate -a "'.$path.$includes[1][$i].'" | uglifyjs -m');
        }
        else
        {
            echo "Reading ".$includes[1][$i]."\n";
            $js .= file_get_contents($path.$includes[1][$i]);
        }
        $html = str_replace($includes[0][$i], '', $html);
    }
}

// minify html now that js/css is out
$html = minifyHTML($html);

// insert css
$html = str_replace('/*-#!css-*/', $css, $html);
// ... and js + insert
$out .= str_replace('/*-#!js-*/', $js, $html);
unset($html);

// write out
$file = 'compiled/angulog-'.AL_VERSION.'.php';
echo 'Writing to '.$file."\n";
file_put_contents($file, $out);
