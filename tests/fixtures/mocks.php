<?php

declare(strict_types=1);

namespace Platine\Console\Util;

$mock_explode_to_false = false;

function explode(string $separator, string $string)
{
    global $mock_explode_to_false;
    if ($mock_explode_to_false) {
        return false;
    }

    return \explode($separator, $string);
}


namespace Platine\Console\Command;

$mock_function_exists_to_true = false;
$mock_function_exists_to_false = false;
$mock_is_resource_to_false = false;
$mock_is_resource_to_true = false;
$mock_proc_terminate_to_true = false;
$mock_proc_terminate_to_false = false;
$mock_proc_close_to_zero = false;
$mock_proc_open_to_res = false;
$mock_proc_open_to_false = false;
$mock_microtime_to_1 = false;
$mock_stream_get_contents_to_foo = false;
$mock_stream_get_contents_to_false = false;
$mock_strtoupper_to_WIN = false;
$mock_fwrite_to_int = false;
$mock_fclose_to_true = false;
$mock_proc_get_status_to_false = false;
$mock_proc_get_status_to_array = false;
$mock_proc_get_status_to_array_running_false = false;
$mock_usleep_to_void = false;
$mock_stream_set_blocking_to_true = false;


function stream_set_blocking($val, $bool)
{
    global $mock_stream_set_blocking_to_true;

    if ($mock_stream_set_blocking_to_true) {
        return true;
    }

    return \stream_set_blocking($val, $bool);
}

function usleep($val)
{
    global $mock_usleep_to_void;

    if ($mock_usleep_to_void) {
        return;
    }

    return \usleep($val);
}

function fclose($val)
{
    global $mock_fclose_to_true;

    if ($mock_fclose_to_true) {
        return true;
    }

    return \fclose($val);
}


function proc_get_status($val)
{
    global $mock_proc_get_status_to_false,
            $mock_proc_get_status_to_array,
            $mock_proc_get_status_to_array_running_false;

    if ($mock_proc_get_status_to_false) {
        return false;
    }

    if ($mock_proc_get_status_to_array) {
        return [
            'command' => 'cmd',
            'pid' => 1829,
            'running' => $mock_proc_get_status_to_array_running_false
                        ? false
                        : true,
            'signaled' => true,
            'stopped' => false,
            'exitcode' => 0,
            'termsig' => 034,
            'stopsig' => 134,
        ];
    }

    return \proc_get_status($val);
}

function fwrite($val, $content)
{
    global $mock_fwrite_to_int;

    if ($mock_fwrite_to_int) {
        return 192;
    }

    return \fwrite($val, $content);
}

function strtoupper($val)
{
    global $mock_strtoupper_to_WIN;

    if ($mock_strtoupper_to_WIN) {
        return 'WIN';
    }

    return \strtoupper($val);
}

function stream_get_contents($val)
{
    global $mock_stream_get_contents_to_foo,
            $mock_stream_get_contents_to_false;

    if ($mock_stream_get_contents_to_false) {
        return false;
    }

    if ($mock_stream_get_contents_to_foo) {
        return 'foo';
    }

    return \stream_get_contents($val);
}

function microtime($val)
{
    global $mock_microtime_to_1;
    if ($mock_microtime_to_1) {
        return 1.0;
    }

    return \microtime($val);
}

function proc_open(
    $cmd,
    array $descriptorspec,
    array &$pipes,
    string $cwd = null,
    array $env = null,
    array $other_options = null
) {
    global $mock_proc_open_to_res,
            $mock_proc_open_to_false;
    if ($mock_proc_open_to_false) {
        return false;
    }

    if ($mock_proc_open_to_res) {
        return;
    }

    return \proc_open(
        $cmd,
        $descriptorspec,
        $pipes,
        $cwd,
        $env,
        $other_options
    );
}

function proc_close($val)
{
    global $mock_proc_close_to_zero;
    if ($mock_proc_close_to_zero) {
        return 0;
    }

    return \proc_close($val);
}

function proc_terminate($val)
{
    global $mock_proc_terminate_to_false,
            $mock_proc_terminate_to_true;
    if ($mock_proc_terminate_to_true) {
        return true;
    }

    if ($mock_proc_terminate_to_false) {
        return false;
    }

    return \proc_terminate($val);
}

function function_exists($val)
{
    global $mock_function_exists_to_true,
            $mock_function_exists_to_false;
    if ($mock_function_exists_to_true) {
        return true;
    }

    if ($mock_function_exists_to_false) {
        return false;
    }

    return \function_exists($val);
}

function is_resource($val)
{
    global $mock_is_resource_to_true,
            $mock_is_resource_to_false;
    if ($mock_is_resource_to_true) {
        return true;
    }

    if ($mock_is_resource_to_false) {
        return false;
    }

    return \is_resource($val);
}

namespace Platine\Console\Input;

$mock_strtoupper_to_WIN = false;
$mock_shell_exec_to_null = false;
$mock_shell_exec_to_foo = false;
$mock_stream_select_to_one = false;

function stream_select(array &$read, array &$write, array &$except, int $tv_sec)
{
    global $mock_stream_select_to_one;
    if ($mock_stream_select_to_one) {
        return 1;
    }

    return \stream_select($read, $write, $except, $tv_sec);
}

function strtoupper(string $string)
{
    global $mock_strtoupper_to_WIN;
    if ($mock_strtoupper_to_WIN) {
        return 'WIN';
    }

    return \strtoupper($string);
}

function shell_exec(string $string)
{
    global $mock_shell_exec_to_null,
           $mock_shell_exec_to_foo;
    if ($mock_shell_exec_to_null) {
        return null;
    }

    if ($mock_shell_exec_to_foo) {
        return 'foo';
    }

    return \shell_exec($string);
}
