<?php

use Phpm\UnitTest;
use AttributeHelper\Accessor;

	class AccessorUser {

		use Accessor;

		private $_a = "hello";

		function __construct() {

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

		function reassign($a = null) {

			if (empty($a)) {
				return $this->_a;
			}

			$this->_a = $a;

		}

	}

	class AccessorTest extends UnitTest {

		private $attr;

		function setUp() {
			$this->attr = new AccessorUser;
		}

		function testGetReadonly() {
			$this->assertEquals("hello", $this->attr->a);
		}

		function testAssignReadOnly() {

			try {
				$this->attr->a = 10;
			} catch (Exception $e) {
				$this->expectExceptionMessage("accessible", $e->getMessage());
			}

		}

		function testAssignNonExistent() {

			try {
				$a = $this->attr->b;
			} catch (Exception $e) {
				$this->expectExceptionMessage("invalid", $e->getMessage());
			}

		}

		function testAccessClosureAttribute() {
			$this->assertEquals("Hey!", $this->attr->c);
		}

		function testInvokeMethodAsProperty() {
			$this->assertEquals("Yes", $this->attr->test);
		}

		function testInvokePropertyMethodAsMethod() {
			$this->assertEquals(14, $this->attr->test(14));
		}

		function testAccessInaccessible() {

			try {
				echo $this->attr->x;
			} catch (Exception $e) {
				$this->expectExceptionMessage("accessible", $e->getMessage());
			}

		}

		function testSetMethodLikeProperty() {

			$this->attr->reassign = 10;
			$this->assertEquals(10, $this->attr->reassign);

		}

		function tearDown() {
			$this->attr = null;
		}

	}

?>