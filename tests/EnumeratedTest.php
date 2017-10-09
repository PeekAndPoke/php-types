<?php
/**
 * Created by gerk on 17.05.17 21:13
 */

namespace PeekAndPoke\Types;

use PHPUnit\Framework\TestCase;

/**
 * @author Karsten J. Gerber <kontakt@karsten-gerber.de>
 */
class EnumeratedTest extends TestCase
{
    public function testInit()
    {
        xxxUnitTestEnumThatIsNotInitializedXxx::init();

        self::assertInstanceOf(xxxUnitTestEnumThatIsNotInitializedXxx::class, xxxUnitTestEnumThatIsNotInitializedXxx::$VAL1, 'Val1 must be initialized');

        self::assertInstanceOf(xxxUnitTestEnumThatIsNotInitializedXxx::class, xxxUnitTestEnumThatIsNotInitializedXxx::$VAL2, 'Val2 must be initialized');

        self::assertInstanceOf(xxxUnitTestEnumThatIsNotInitializedXxx::class, xxxUnitTestEnumThatIsNotInitializedXxx::$VAL3, 'Val3 must be initialized');
    }

    public function testVoid()
    {
        $enum = xxxUnitTestEnumXxx::void();

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('', $enum->getValue(), 'The value of a void enum must be an empty string');
    }

    public function testFirstValue()
    {
        $enum = xxxUnitTestEnumXxx::$ONE;

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('ONE', $enum->getValue(), 'The value must be "ONE"');
        self::assertSame('ONE', (string) $enum, 'To string conversion must work');

        self::assertTrue($enum->isValid(), 'The enum must be valid');
    }

    public function testSecondValue()
    {
        $enum = xxxUnitTestEnumXxx::$Two;

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('Two', $enum->getValue(), 'The value must be "Two"');
        self::assertSame('Two', (string) $enum, 'To string conversion must work');

        self::assertTrue($enum->isValid(), 'The enum must be valid');
    }

    public function testFromForValidValue()
    {
        $enum = xxxUnitTestEnumXxx::from('Two');

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('Two', $enum->getValue(), 'The value must be "Two"');

        self::assertTrue($enum->isValid(), 'The enum must be valid');
    }

    public function testFromForInvalidValue()
    {
        $enum = xxxUnitTestEnumXxx::from('UnKnOwN');

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('UnKnOwN', $enum->getValue(), 'The value must be "UnKnOwN"');
        self::assertSame('UnKnOwN', (string) $enum, 'To string conversion must work');

        self::assertFalse($enum->isValid(), 'The enum must NOT be valid');
    }

    public function testFromStringableObject()
    {
        $enum = xxxUnitTestEnumXxx::from(xxxUnitTestEnumXxx::$ONE);

        self::assertInstanceOf(xxxUnitTestEnumXxx::class, $enum, 'Must be an instance the enum');

        self::assertSame('ONE', $enum->getValue(), 'The value must be "ONE"');
        self::assertSame('ONE', (string) $enum, 'To string conversion must work');

        self::assertTrue($enum->isValid(), 'The enum must be valid');
    }

    public function testEnumerateValues()
    {
        self::assertSame(
            ['ONE', 'Two'],
            xxxUnitTestEnumXxx::enumerateValues(),
            'Enumerating the values must work'
        );
    }

    public function testFromOmnipotence()
    {
        self::assertSame(xxxUnitTestEnumXxx::$ONE, xxxUnitTestEnumXxx::from('ONE'), 'from() must return the same instance');
    }

    public function testMultipleEnums()
    {
        self::assertInstanceOf(xxxUnitTestEnumXxx::class, xxxUnitTestEnumXxx::$ONE, 'The type must be correct');

        self::assertInstanceOf(xxxUnitTestEnum2Xxx::class, xxxUnitTestEnum2Xxx::$ONE, 'The type must be correct');
    }

    public function testMultipleEnumsComparison()
    {
        self::assertEquals(xxxUnitTestEnumXxx::$ONE->getValue(), xxxUnitTestEnum2Xxx::$ONE->getValue(), 'The string values must be equal');

        self::assertNotSame(xxxUnitTestEnumXxx::$ONE, xxxUnitTestEnum2Xxx::$ONE, 'The enums must not be the same');
    }
}

/**
 * @internal
 */
class xxxUnitTestEnumXxx extends Enumerated
{
    /** @var xxxUnitTestEnumXxx */
    public static $ONE;

    /** @var xxxUnitTestEnumXxx */
    public static $Two;
}

xxxUnitTestEnumXxx::init();

class xxxUnitTestEnum2Xxx extends Enumerated
{
    /** @var xxxUnitTestEnumXxx */
    public static $ONE;

    /** @var xxxUnitTestEnumXxx */
    public static $Two;
}

xxxUnitTestEnum2Xxx::init();

/**
 * @internal
 */
class xxxUnitTestEnumThatIsNotInitializedXxx extends Enumerated
{
    public static $VAL1;
    public static $VAL2;
    public static $VAL3;
}
