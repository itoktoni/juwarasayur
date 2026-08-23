<?php

namespace Modules\Production\Http\Controllers;

use App\Concerns\ControllerTrait;

abstract class Controller extends \App\Http\Controllers\Controller
{
    use ControllerTrait;

    /**
     * Nama action saat ini ('table' | 'form' | lainnya) dari backtrace.
     */
    protected function currentAction(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['function']) && preg_match('/^(get|post)/', $frame['function'])) {
                $action = strtolower(preg_replace('/^(get|post)/', '', $frame['function']));

                if (in_array($action, ['update', 'create'])) {
                    $action = 'form';
                }

                return $action !== '' ? $action : 'table';
            }
        }

        return 'table';
    }

    protected function template($file = null, $folder = null, $core = false)
    {
        $module = strtolower(str_replace('Controller', '', class_basename(get_class($this))));

        if ($folder) {
            $module = $folder;
        }

        return 'production::pages.'.$module.'.'.($file ?: $this->currentAction());
    }
}
