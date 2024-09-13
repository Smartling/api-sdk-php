<?php

namespace Smartling\Tests\Unit;

use Composer\Semver\Constraint\Bound;
use Composer\Semver\VersionParser;
use Smartling\Polyfills;

class PolyfillsTest extends ApiTestAbstract
{
    /**
     * @var Bound $requiredPhpLowerBound
     */
    private $requiredPhpLowerBound;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requiredPhpLowerBound = (new VersionParser())->parseConstraints(json_decode(
            file_get_contents(__DIR__ . '/../../composer.json'), true, 4, JSON_THROW_ON_ERROR
        )['require']['php'])->getLowerBound();
    }

    public function testArrayIsListPolyfill()
    {
        $this->assertMinimumPhpVersion('8.1', Polyfills::class, 'array_is_list');
        foreach ([
                     [],
                     ['a', 'b', 'c'],
                     array_unique(['a', 'b', 'c']),
                     array_unique([0 => 'a', 1 => 'b', 2 => 'c']),
                     array_unique(array_values(array_unique(['a', 'b', 'b', 'c']))),
                 ] as $array) {
            if (function_exists('array_is_list')) {
                $this->assertTrue(array_is_list($array));
            }
            $this->assertTrue(Polyfills::array_is_list($array));
        }
        foreach ([
                     array_unique(['a', 'b', 'b', 'c']),
                     [1 => 'a'],
                 ] as $array) {
            if (function_exists('array_is_list')) {
                $this->assertFalse(array_is_list($array));
            }
            $this->assertFalse(Polyfills::array_is_list($array));
        }
    }

    private function assertMinimumPhpVersion(string $version, string $class, string $function)
    {
        if ($this->requiredPhpLowerBound->compareTo(new Bound($version, true), '>')) {
            $this->fail("$class::$function() must be removed, minimum required php version is > $version");
        }
    }
}
