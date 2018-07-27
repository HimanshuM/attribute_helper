<?php

use Phpm\UnitTest;
use AttributeHelper\Accessor;

	class AccessorUserAllow {

		use Accessor;

		private $_a = "hello";
		private $_internal = [];

		function __construct() {
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_ALLOW);
		}

		function test($a = 10) {

			if ($a == 10) {
				return "Yes";
			}

			return $a;

		}

		function dummy($attr) {

			if (isset($this->_storage[$attr])) {
				return $this->_storage[$attr];
			}

			return null;

		}

	}

	class AccessorTestAllow extends UnitTest {

		private $attr;

		function setUp() {
			$this->attr = new AccessorUserAllow;
		}

		function testAllowNotFoundGet() {
			$this->assertNull($this->attr->length);
		}

		function testAllowNotFoundSet() {

			$this->attr->length = 100;
			$this->assertEquals(100, $this->attr->length);

		}

		function testStorageAccessibilityForNotFound() {
			$this->assertNull($this->attr->dummy("xyz"));
		}

		function testStorageAccessibility() {

			$this->attr->length = 100;
			$this->assertEquals(100, $this->attr->dummy("length"));

		}

		function tearDown() {
			$this->attr = null;
		}

	}

?>