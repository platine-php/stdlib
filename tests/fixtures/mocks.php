<?php

declare(strict_types=1);

namespace Platine\Stdlib\Helper;

use Platine\Test\Fixture\Stdlib\ComposerAutoloadObject;

$mock_array_rand_to_1 = false;
$mock_shuffle_to_1 = false;
$mock_mt_srand_to_void = false;
$mock_spl_autoload_functions_to_empty = false;
$mock_spl_autoload_functions_to_array = false;
$mock_file_exists_to_false = false;
$mock_file_exists_to_true = false;
$mock_file_get_contents_to_false = false;
$mock_file_get_contents_to_foo = false;
$mock_json_decode_to_empty = false;
$mock_json_decode_to_array = false;
$mock_php_sapi_name_to_cli = false;
$mock_php_sapi_name_to_phpdbg = false;
$mock_php_sapi_name_to_foo = false;
$mock_php_stripos_to_cygwin = false;
$mock_php_stripos_to_win = false;
$mock_php_stripos_to_mac = false;
$mock_php_stripos_to_foo = false;
$mock_realpath_to_false = false;
$mock_realpath_to_foo = false;
$mock_str_split_to_false = false;
$mock_random_int = false;
$mock_md5_to_param = false;
$mock_chr_to_param = false;
$mock_base64_encode_to_param = false;
$mock_bin2hex_to_param = false;
$mock_ctype_alpha_to_true = false;
$mock_random_bytes = false;
$mock_is_object_to_false = false;
$mock_mt_rand_to_zero = false;

function mt_rand(int $min, int $max): int
{
    global $mock_mt_rand_to_zero;
    if ($mock_mt_rand_to_zero) {
        return 0;
    }


    return \mt_rand($min, $max);
}

function is_object($val)
{
    global $mock_is_object_to_false;
    if ($mock_is_object_to_false) {
        return false;
    }


    return \is_object($val);
}

function base64_encode(string $val)
{
    global $mock_base64_encode_to_param;
    if ($mock_base64_encode_to_param) {
        return $val;
    }


    return \base64_encode($val);
}

function bin2hex(string $val)
{
    global $mock_bin2hex_to_param;
    if ($mock_bin2hex_to_param) {
        return $val;
    }


    return \bin2hex($val);
}

function random_bytes(int $val)
{
    global $mock_random_bytes;
    if ($mock_random_bytes) {
        return $val % 2 == 0 ? 'foo' : 'bar';
    }


    return \random_bytes($val);
}

function ctype_alpha($val)
{
    global $mock_ctype_alpha_to_true;
    if ($mock_ctype_alpha_to_true) {
        return true;
    }


    return \ctype_alpha($val);
}

function chr($val)
{
    global $mock_chr_to_param;
    if ($mock_chr_to_param) {
        return $val;
    }


    return \chr($val);
}


function md5($val)
{
    global $mock_md5_to_param;
    if ($mock_md5_to_param) {
        return $val;
    }


    return \md5($val);
}

function random_int(int $min, int $max)
{
    global $mock_random_int;
    if ($mock_random_int) {
        return $max % 3 == 0 ? 1 : ($max % 2 == 0 ? 2 : 0);
    }


    return \random_int($min, $max);
}

function str_split(string $name, int $length = 1)
{
    global $mock_str_split_to_false;
    if ($mock_str_split_to_false) {
        return false;
    }


    return \str_split($name, $length);
}

function realpath(string $name)
{
    global $mock_realpath_to_false,
     $mock_realpath_to_foo;
    if ($mock_realpath_to_false) {
        return false;
    }

    if ($mock_realpath_to_foo) {
        return 'foo';
    }

    return \realpath($name);
}

function stripos(string $haystack, string $needle, int $offset = 0)
{
     global $mock_php_stripos_to_cygwin,
     $mock_php_stripos_to_win,
     $mock_php_stripos_to_mac,
     $mock_php_stripos_to_foo;
    if ($mock_php_stripos_to_cygwin) {
        return 0;
    }

    if ($mock_php_stripos_to_win) {
        return 0;
    }

    if ($mock_php_stripos_to_mac) {
        return 0;
    }

    if ($mock_php_stripos_to_foo) {
        return false;
    }

    return \stripos($haystack, $needle, $offset);
}

function php_sapi_name()
{
    global $mock_php_sapi_name_to_cli,
     $mock_php_sapi_name_to_phpdbg,
     $mock_php_sapi_name_to_foo;
    if ($mock_php_sapi_name_to_cli) {
        return 'cli';
    }

    if ($mock_php_sapi_name_to_phpdbg) {
        return 'phpdbg';
    }

    if ($mock_php_sapi_name_to_foo) {
        return 'foo';
    }

    return \php_sapi_name();
}

function json_decode(string $name, $assoc, int $depth = 512, int $flags = 0)
{
    global $mock_json_decode_to_empty,
     $mock_json_decode_to_array;
    if ($mock_json_decode_to_empty) {
        return [];
    }

    if ($mock_json_decode_to_array) {
        return ['packages' => [
            [
                'name' => 'foo',
                'type' => 'library',
            ],
        ]];
    }

    return \json_decode($name, $assoc, $depth, $flags);
}

function file_get_contents(string $name)
{
    global $mock_file_get_contents_to_false,
     $mock_file_get_contents_to_foo;
    if ($mock_file_get_contents_to_false) {
        return false;
    }

    if ($mock_file_get_contents_to_foo) {
        return 'foo';
    }

    return \file_get_contents($name);
}

function file_exists(string $name)
{
    global $mock_file_exists_to_false,
     $mock_file_exists_to_true;
    if ($mock_file_exists_to_false) {
        return false;
    }

    if ($mock_file_exists_to_true) {
        return true;
    }

    return \file_exists($name);
}

function spl_autoload_functions()
{
    global $mock_spl_autoload_functions_to_empty,
     $mock_spl_autoload_functions_to_array;
    if ($mock_spl_autoload_functions_to_empty) {
        return [];
    }

    if ($mock_spl_autoload_functions_to_array) {
        return [[new ComposerAutoloadObject()]];
    }

    return \spl_autoload_functions();
}

function array_rand(array $a, $num = 1)
{
    global $mock_array_rand_to_1;
    if ($mock_array_rand_to_1) {
        return 1;
    }

    return \array_rand($a, $num);
}

function shuffle(array &$a)
{
    global $mock_shuffle_to_1;
    if ($mock_shuffle_to_1) {
        $a = [4];
    }

    return \shuffle($a);
}

function mt_srand(int $a = 0)
{
    global $mock_mt_srand_to_void;
    if ($mock_mt_srand_to_void) {
        return 1;
    }

    return \mt_srand($a);
}
