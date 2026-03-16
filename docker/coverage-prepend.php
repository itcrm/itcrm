<?php
/**
 * Auto-prepended to every request when coverage is enabled.
 * Starts Xdebug code coverage collection.
 */
if (getenv('COVERAGE_ENABLED') && function_exists('xdebug_start_code_coverage')) {
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
}
