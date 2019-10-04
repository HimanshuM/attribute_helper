<?php

namespace AttributeHelper;

use Exception;

define ("ACCESSOR_NOT_FOUND_EXCEPTION", 1);
define ("ACCESSOR_NOT_FOUND_CALLBACK", 2);
define ("ACCESSOR_NOT_FOUND_ALLOW", 3);

	trait Accessor {

		private $_readonly = [];
		private $_accesssible = [];
		private $_inaccessible = [];
		private $_issetOverride = false;
		private $_methodsAsProperties = null;
		private $_notFoundResponse = [ACCESSOR_NOT_FOUND_EXCEPTION, null];
		private $_underscorePrepended = false;
		private $_strictMode = true;

		protected $_storage = [];

		protected function accessible() {
			$this->_buildProperty(func_get_args(), "_accesssible");
		}

		private function _buildProperty($args, $type) {

			if (count($args) == 2 && is_a($args[1], "Closure")) {

				$this->$type[$args[0]] = $args[1];
				return;

			}

			foreach ($args as $arg) {

				// Format: [<as_name>, <original_name>]
				if (is_array($arg)) {

					if (is_a($arg[1], "Closure")) {
						$this->$type[$arg[0]] = $arg[1];
					}
					else {
						$this->$type[$arg[0]] = $arg[1];
					}

				}
				else {

					// Format: <as_name>
					if ($this->_underscorePrepended) {
						$this->$type[$arg] = "_".$arg;
					}
					else {
						$this->$type[$arg] = $arg;
					}

				}

			}

		}

		protected function disableStrictAccessibility($enable = false) {
			$this->_strictMode = $enable;
		}

		function __get($attr) {

			if (in_array($attr, $this->_inaccessible)) {
				throw new Exception("Property '$attr' of class ".get_class($this)." in not accessible.", 1);
			}

			if (isset($this->_accesssible[$attr])) {
				return $this->_getProperty($this->_accesssible[$attr]);
			}
			else if (isset($this->_readonly[$attr])) {
				return $this->_getProperty($this->_readonly[$attr]);
			}
			else if (is_array($this->_methodsAsProperties) && (empty($this->_methodsAsProperties) || in_array($attr, $this->_methodsAsProperties))) {

				if (method_exists($this, $attr)) {
					return $this->$attr();
				}

			}
			else if (!$this->_strictMode) {

				if (property_exists($this, $attr)) {
					return $this->$attr;
				}
				else if ($this->_underscorePrepended) {

					$attr = "_".$attr;
					if (property_exists($this, $attr)) {
						return $this->$attr;
					}

				}

			}

			if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_EXCEPTION) {
				throw new Exception("Invalid property '$attr' of class ".get_class($this).".", 1);
			}
			else if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_CALLBACK) {

				$callback = $this->_notFoundResponse[1];
				if (is_a($callback, "Closure")) {
					return $callback($attr);
				}
				else {
					return $this->$callback($attr);
				}

			}
			else if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_ALLOW) {

				if (isset($this->_storage[$attr])) {
					return $this->_storage[$attr];
				}

				return null;

			}

			throw new Exception("Invalid state exception...", 1);

		}

		private function _getProperty($attr) {

			if (is_a($attr, "Closure")) {
				return $attr();
			}
			else if (property_exists($this, $attr)) {
				return $this->$attr;
			}
			else if (method_exists($this, $attr)) {
				return $this->$attr();
			}

			return $this->$attr();

		}

		protected function inaccessible() {
			$this->_inaccessible = array_merge($this->_inaccessible, func_get_args());
		}

		function __isset($name) {

			if (!empty($this->_issetOverride)) {

				if (is_a($this->_issetOverride, "Closure")) {
					return ($this->_issetOverride->bindTo($this))();
				}

				$override = $this->_issetOverride;

				return $this->$override($name);

			}

			return ($this->_strictMode && (isset($this->_readonly[$name]) || isset($this->_accesssible[$name]))) || (!$this->_strictMode && property_exists($this, (($this->_underscorePrepended ? "_" : "").$name))) || isset($this->_storage[$name]);

		}

		protected function issetOverride($override) {

			if (!is_a($override, "Closure") && !is_callable([$this, $override])) {
				throw new InvalidArgumentTypeException("Accessor::issetOverride", 1, ["Closure", "method"]);
			}

			$this->_issetOverride = $override;

		}

		protected function methodsAsProperties() {

			$methods = func_get_args();
			if (empty($methods) || $methods[0] != null) {

				if ($this->_methodsAsProperties == null) {
					$this->_methodsAsProperties = $methods;
				}
				else {
					$this->_methodsAsProperties = array_merge($this->_methodsAsProperties, $methods);
				}

			}

		}

		protected function notFoundResponse($flag = ACCESSOR_NOT_FOUND_EXCEPTION, $callback = null) {

			if ($flag < 1 || $flag > 3) {
				throw new Exception("Invalid flag for AttributeHelper\\Accessor::notFoundResponse().", 1);
			}

			$this->_notFoundResponse = [$flag, $callback];

		}

		protected function prependUnderscore() {
			$this->_underscorePrepended = true;
		}

		protected function readonly() {
			$this->_buildProperty(func_get_args(), "_readonly");
		}

		function __set($attr, $value) {

			if (in_array($attr, $this->_inaccessible) || isset($this->_readonly[$attr])) {
				throw new Exception("Property '$attr' of class ".get_class($this)." is not accessible.", 1);
			}

			if (isset($this->_accesssible[$attr])) {
				return $this->_setProperty($this->_accesssible[$attr], $value);
			}
			else if (is_array($this->_methodsAsProperties) && (empty($this->_methodsAsProperties) || in_array($attr, $this->_methodsAsProperties))) {

				if (method_exists($this, $attr)) {
					return $this->$attr($value);
				}

			}
			else if (!$this->_strictMode) {

				if (property_exists($this, $attr)) {
					return $this->$attr = $value;
				}
				else if ($this->_underscorePrepended) {

					$attr = "_".$attr;
					if (property_exists($this, $attr)) {
						return $this->$attr = $value;
					}

				}

			}

			if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_EXCEPTION) {
				throw new Exception("Invalid property '$attr' of class ".get_class($this).".", 1);
			}
			else if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_CALLBACK) {

				$callback = $this->_notFoundResponse[1];
				if (is_a($callback, "Closure")) {
					$callback($attr, $value);
				}
				else {
					$this->$callback($attr, $value);
				}

			}
			else if ($this->_notFoundResponse[0] == ACCESSOR_NOT_FOUND_ALLOW) {
				$this->_storage[$attr] = $value;
			}

		}

		private function _setProperty($attr, $value) {

			if (property_exists($this, $attr)) {
				$this->$attr = $value;
			}
			else if (is_a($attr, "Closure")) {
				$attr($value);
			}
			else {
				$this->$attr($value);
			}

		}

	}

?>