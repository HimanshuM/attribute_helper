<?php

use Phpm\UnitTest;
use AttributeHelper\Accessor;

	class AccessorUserCallback {

		use Accessor;

		private $_a = "hello";
		private $_internal = [];

		function __construct() {

			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "dummy");

			$this->prependUnderscore();
			$this->readonly("a");
			$this->readonly("c", function() {
				return "Hey!";
			});

			$this->methodsAsProperties();

			$this->inaccessible("x");

		}

		function test($a = 10) {

			if ($a == 10) {
				return "Yes";
			}

			return $a;

		}

		function dummy() {

			$args = func_get_args();
			if (func_num_args() == 1) {
				return isset($this->_internal[$args[0]]) ? $this->_internal[$args[0]] : $args[0];
			}

			$this->_internal[$args[0]] = $args[1];

		}

	}

	class AccessorTestCallbacks extends UnitTest {

		private $attr;

		function setUp() {
			$this->attr = new AccessorUserCallback;
		}

		function testCallbackForNotFoundGet() {
			$this->assertEquals("length", $this->attr->length);
		}

		function testCallbackForNotFoundSet() {

			$this->attr->length = 100;
			$this->assertEquals(100, $this->attr->length);

		}

		function tearDown() {
			$this->attr = null;
		}

	}

?>