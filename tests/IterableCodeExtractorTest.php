<?php

namespace WP_CLI\I18n\Tests;

use PHPUnit_Framework_TestCase;
use WP_CLI\I18n\IterableCodeExtractor;


class Extractor_Test extends PHPUnit_Framework_TestCase {

	/** @var string A path files are located */
	private static $base;

	public function setUp() {
		/**
		 * PHP5.4 cannot set property with __DIR__ constant.
		 */
		self::$base = __DIR__ . '/data/';
	}

	public function test_can_include_files() {
		$includes = [ 'foo', 'bar', 'baz/inc*.js' ];
		$result   = IterableCodeExtractor::getFilesFromDirectory( self::$base, $includes, [], [ 'php', 'js' ] );
		$expected = static::$base . 'foo-plugin/foo-plugin.php';
		$this->assertContains( $expected, $result );
		$expected = static::$base . 'baz/includes/should_be_included.js';
		$this->assertContains( $expected, $result );
		$expected = 'hoge/should_NOT_be_included.js';
		$this->assertNotContains( $expected, $result );
	}

	public function test_can_include_empty_array() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [], [], [ 'php', 'js' ] );
		$expected_1 = static::$base . 'foo-plugin/foo-plugin.php';
		$expected_2 = static::$base . 'baz/includes/should_be_included.js';
		$this->assertContains( $expected_1, $result );
		$this->assertContains( $expected_2, $result );
	}

	public function test_can_include_wildcard() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ '*' ], [], [ 'php', 'js' ] );
		$expected_1 = static::$base . 'foo-plugin/foo-plugin.php';
		$expected_2 = static::$base . 'baz/includes/should_be_included.js';
		$this->assertContains( $expected_1, $result );
		$this->assertContains( $expected_2, $result );
	}

	public function test_can_include_subdirectories() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ 'foo/bar/*' ], [], [ 'php', 'js' ] );
		$expected_1 = static::$base . 'foo/bar/foo/bar/foo/bar/deep_directory_also_included.php';
		$expected_2 = static::$base . 'foo/bar/foofoo/included.js';
		$this->assertContains( $expected_1, $result );
		$this->assertContains( $expected_2, $result );
	}

	public function test_can_include_only_php() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ 'foo/bar/*' ], [], [ 'php' ] );
		$expected_1 = static::$base . 'foo/bar/foo/bar/foo/bar/deep_directory_also_included.php';
		$expected_2 = static::$base . 'foo/bar/foofoo/ignored.js';
		$this->assertContains( $expected_1, $result );
		$this->assertNotContains( $expected_2, $result );
	}

	public function test_can_exclude_override_wildcard() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ 'foo/bar/*' ], ['foo/bar/excluded/*'], [ 'php' ] );
		$expected_1 = static::$base . 'foo/bar/foo/bar/foo/bar/deep_directory_also_included.php';
		$expected_2 = static::$base . 'foo/bar/excluded/excluded.js';
		$this->assertContains( $expected_1, $result );
		$this->assertNotContains( $expected_2, $result );
	}

	public function test_can_exclude_override_matching_directory() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ 'foo/bar/*' ], ['excluded'], [ 'php' ] );
		$expected_1 = static::$base . 'foo/bar/foo/bar/foo/bar/deep_directory_also_included.php';
		$expected_2 = static::$base . 'foo/bar/excluded/excluded.js';
		$this->assertContains( $expected_1, $result );
		$this->assertNotContains( $expected_2, $result );
	}

	public function test_can_not_exclude_partially_directory() {
		$result     = IterableCodeExtractor::getFilesFromDirectory( self::$base, [ 'foo/bar/*' ], ['exc'], [ 'js' ] );
		$expected_1 = static::$base . 'foo/bar/foo/bar/foo/bar/deep_directory_also_included.php';
		$expected_2 = static::$base . 'foo/bar/excluded/ignored.js';
		//$this->assertContains( $expected_1, $result );
		$this->assertContains( $expected_2, $result );
	}

	public function test_can_exclude_by_wildcard() {
		$result = IterableCodeExtractor::getFilesFromDirectory( self::$base, [], [ '*' ], [ 'php', 'js' ] );
		$this->assertEmpty( $result );
	}

	public function test_can_exclude_files() {
		$excludes = [ 'hoge' ];
		$result   = IterableCodeExtractor::getFilesFromDirectory( self::$base, [], $excludes, [ 'php', 'js' ] );
		$expected = 'hoge/should_NOT_be_included.js';
		$this->assertNotContains( $expected, $result );
	}

	public function test_can_override_include_by_exclude() {
		// Overrides include option
		$includes = [ 'foo', 'bar', 'baz/inc*.js', 'hoge' ];
		$excludes = [ 'hoge' ];
		$result   = IterableCodeExtractor::getFilesFromDirectory( self::$base, $includes, $excludes, [ 'php', 'js' ] );
		$expected = 'hoge/should_NOT_be_included.js';
		$this->assertNotContains( $expected, $result );

	}
}
