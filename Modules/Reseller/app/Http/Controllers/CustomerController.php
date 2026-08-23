<?php

namespace Modules\Reseller\Http\Controllers;

use Modules\So\Http\Controllers\CustomerController as SoCustomerController;

class CustomerController extends SoCustomerController
{
    protected function template($file = null, $folder = null, $core = false)
    {
        $action = 'table';

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['function']) && preg_match('/^(get|post)/', $frame['function'])) {
                $action = strtolower(preg_replace('/^(get|post)/', '', $frame['function']));
                break;
            }
        }

        if (in_array($action, ['update', 'create'])) {
            $action = 'form';
        }

        if ($file) {
            $action = $file;
        }

        return 'reseller::pages.customer.'.$action;
    }
}
