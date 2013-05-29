<?php
/// Copyright (c) 2004-2013, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// Singleton implementation.
if(version_compare(PHP_VERSION, '5.3.0','>=')) { // >= 5.3 Singleton implementation 
	class Singleton {
		// If your model support higher than PHP 5.3, you do not implement getInstance method.
		// However, models for prior to PHP 5.3 should have getInstance method. 
		// See below (<5.3 Compatible singleton implementation) 
		private static $instances;

		public function __construct() {
			$c = get_class($this);
			if(isset(self::$instances[$c])) {
				throw new Exception('You can not create more than one copy of a singleton.');
			} else {
				self::$instances[$c] = $this;
			}
		}
		public static function _getInstance($p = null) {
			$c = get_called_class();
			if (!isset(self::$instances[$c])) {
				$args = func_get_args();
				$reflection_object = new ReflectionClass($c);
				self::$instances[$c] = $reflection_object->newInstanceArgs($args);
			}
			return self::$instances[$c];
		}
		public static function getInstance() {
			return self::_getInstance();
		}
		public function __clone() {
			throw new Exception('You can not clone a singleton.');
		}
	}
} else { //  < 5.3 Compatible Singleton implementation.
	class Singleton {
		private static $instances = array();

		protected function __construct() {
		}

		final protected static function _getInstance($className) {
			if (!array_key_exists($className, self::$instances)) {
				self::$instances[$className] = new $className();
			}
			return self::$instances[$className];
		}

		/*
		// If your model support prior to PHP 5.3, you should implement this method to the final class. 
		// (An example is below.)
		// This is mainly because "late static bindings" is supported after PHP 5.3.

		public static function getInstance() {
			return self::_getInstance(__CLASS__);
		}
		*/
		public static function getInstance(){}
	}
}
