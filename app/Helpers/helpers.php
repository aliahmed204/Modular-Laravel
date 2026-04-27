<?php

if (!function_exists('module_path')) {
    /**
     * Get the path to a module.
     *
     * @param string $module
     * @param string $path
     * @return string
     */
    function module_path($module = null, $path = '')
    {
        $base = base_path('modules');
        
        if ($module) {
            return $base . DIRECTORY_SEPARATOR . $module . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
        }
        
        return $base;
    }
}
