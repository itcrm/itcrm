<?php
/**
 * Auto-appended to every request when coverage is enabled.
 * Saves Xdebug coverage data as a JSON file.
 */
if (getenv('COVERAGE_ENABLED') && function_exists('xdebug_get_code_coverage')) {
    $coverage = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    if (!empty($coverage)) {
        $dir = '/tmp/coverage';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir . '/' . uniqid('cov_', true) . '.json';
        file_put_contents($file, json_encode($coverage, JSON_UNESCAPED_SLASHES));
    }
}
