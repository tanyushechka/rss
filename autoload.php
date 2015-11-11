<?php
require_once __DIR__ . '/vendor/autoload.php';

function myAutoLoader($class) {
	if (false !== stripos($class, '\\')) {
		$classNameParts = explode('\\', $class);
		if ('App' == $classNameParts[0]) {
			unset($classNameParts[0]);
			$filename = __DIR__ . '\\' . implode('\\', $classNameParts) . '.php';

			if (file_exists($filename)) {
				require_once $filename;
				return true;
			}
		}
	}
	return false;
}

spl_autoload_register('myAutoLoader', true, true);