<?php
namespace CfdiUtilsTests\QuickReader;

use CfdiUtils\QuickReader\QuickReader;

use PHPUnit\Framework\TestCase;

class QuickReaderTest extends TestCase
{
    public function testMinimalInstance()
    {
        $tree = new QuickReader('foo');

        $this->assertSame('foo', (string) $tree);

        $this->assertCount(0, $tree());
    }

    public function testConstructorWithAttributes()
    {
        $attributes = [
            'one' => '1',
            'two' => '2',
        ];

        $foo = new QuickReader('foo', $attributes, []);

        $this->assertSame('1', $foo['one']);
        $this->assertSame('2', $foo['two']);
    }

    public function testConstructorWithChildren()
    {
        $children = [
            $bar = new QuickReader('bar'),
            $baz = new QuickReader('baz'),
        ];
        $tree = new QuickReader('foo', [], $children);

        $this->assertSame($children, $tree());
        $this->assertSame($bar, $tree->bar);
        $this->assertSame($baz, $tree->baz);
    }

    public function testGetNotExistentAttribute()
    {
        $foo = new QuickReader('foo');

        $this->assertFalse(isset($foo['bar']));
        $this->assertSame('', $foo['bar']);
        $this->assertFalse(isset($foo['bar']));
    }

    public function testAccessNonExistentPropertyReturnsANewChildWithPropertyName()
    {
        $foo = new QuickReader('foo');

        $this->assertFalse(isset($foo->bar));

        $this->assertInstanceOf(QuickReader::class, $foo->bar);
        $this->assertFalse(isset($foo->bar));
        $this->assertCount(0, $foo(), 'Calling a non existent property DOES NOT append a new child');
    }

    public function testAccessInvokeReturnsAnArray()
    {
        $foo = new QuickReader('foo');
        $this->assertInternalType('array', $foo());

        $xee = $foo->bar->xee;
        $this->assertInternalType('array', $xee('zee'));
        $this->assertInternalType('array', $xee->__invoke('zee'));
        $this->assertInternalType('array', ($foo->bar->xee)('zee'));
    }

    public function testAccessInvokeReturnsAnArrayOfChildrenWithTheArgumentName()
    {
        $manyBaz = [
            $firstBaz = new QuickReader('baz'),
            new QuickReader('baz'),
            new QuickReader('baz'),
        ];

        $manyChildren = array_merge($manyBaz, [
            new QuickReader('xee'),
        ]);

        $foo = new QuickReader('foo', [], $manyChildren);
        $this->assertCount(4, $foo(), 'Assert that contains 4 children');

        $this->assertSame($firstBaz, $foo->baz, 'Assert that the first child is the same as the property access');

        $obtainedBaz = $foo('baz');
        $this->assertSame($manyBaz, $obtainedBaz, 'Assert that all elements where retrieved');
        $this->assertCount(3, $obtainedBaz, 'Assert that contains only 3 baz children');
    }

    public function testPropertyGetWithDifferentCaseStillWorks()
    {
        $bar = new QuickReader('bar');
        $foo = new QuickReader('foo', [], [$bar]);

        $this->assertSame($bar, $foo->bar);
        $this->assertTrue(isset($foo->bar));

        $this->assertSame($bar, $foo->Bar);
        $this->assertTrue(isset($foo->Bar));

        $this->assertSame($bar, $foo->BAR);
        $this->assertTrue(isset($foo->BAR));

        $this->assertSame($bar, $foo->bAR);
        $this->assertTrue(isset($foo->bAR));
    }

    public function testAttributeGetWithDifferentCaseStillWorks()
    {
        $foo = new QuickReader('foo', ['bar' => 'México']);

        $this->assertSame('México', $foo['bar']);
        $this->assertTrue(isset($foo['bar']));

        $this->assertSame('México', $foo['Bar']);
        $this->assertTrue(isset($foo['Bar']));

        $this->assertSame('México', $foo['BAR']);
        $this->assertTrue(isset($foo['BAR']));
    }

    public function testInvokeWithDifferentChildNamesCase()
    {
        $fooA = new QuickReader('foo');
        $fooB = new QuickReader('Foo');
        $fooC = new QuickReader('FOO');

        $root = new QuickReader('root', [], [$fooA, $fooB, $fooC]);

        $this->assertCount(3, $root('fOO'));
    }
}
