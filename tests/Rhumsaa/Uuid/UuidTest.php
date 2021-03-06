<?php
namespace Rhumsaa\Uuid;

require_once 'functions.php';

class UuidTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Uuid::$timeOfDayTest = null;
        Uuid::$force32Bit = false;
        Uuid::$forceNoBigNumber = false;
        Uuid::$forceNoOpensslRandomPseudoBytes = false;
        Uuid::$ignoreSystemNode = false;
    }

    /**
     * If the system is 32-bit, this will mark a test as skipped
     */
    protected function skip64BitTest()
    {
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped(
                'Skipping test that can run only on a 64-bit build of PHP.'
            );
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromString
     * @covers Rhumsaa\Uuid\Uuid::__construct
     */
    public function testFromString()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->toString());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromString
     */
    public function testFromStringWithCurlyBraces()
    {
        $uuid = Uuid::fromString('{ff6f8cb0-c57d-11e1-9b21-0800200c9a66}');
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->toString());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromString
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid UUID string:
     */
    public function testFromStringWithInvalidUuidString()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21');
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromString
     */
    public function testFromStringWithUrn()
    {
        $uuid = Uuid::fromString('urn:uuid:ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->toString());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getBytes
     */
    public function testGetBytes()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(16, strlen($uuid->getBytes()));
        $this->assertEquals('/2+MsMV9EeGbIQgAIAyaZg==', base64_encode($uuid->getBytes()));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqHiAndReserved
     */
    public function testGetClockSeqHiAndReserved()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(155, $uuid->getClockSeqHiAndReserved());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqHiAndReservedHex
     */
    public function testGetClockSeqHiAndReservedHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('9b', $uuid->getClockSeqHiAndReservedHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqLow
     */
    public function testGetClockSeqLow()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(33, $uuid->getClockSeqLow());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqLowHex
     */
    public function testGetClockSeqLowHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('21', $uuid->getClockSeqLowHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSequence
     */
    public function testGetClockSequence()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(6945, $uuid->getClockSequence());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getClockSequenceHex
     */
    public function testGetClockSequenceHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('1b21', $uuid->getClockSequenceHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getDateTime
     */
    public function testGetDateTime()
    {
        // Check a recent date
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Wed, 04 Jul 2012 02:14:34 +0000', $uuid->getDateTime()->format('r'));

        // Check an old date
        $uuid = Uuid::fromString('0901e600-0154-1000-9b21-0800200c9a66');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Sun, 16 Oct 1582 16:34:04 +0000', $uuid->getDateTime()->format('r'));

        // Check a future date
        $uuid = Uuid::fromString('ff9785f6-ffff-1fff-9669-00007ffffffe');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Mon, 31 Mar 5236 21:21:00 +0000', $uuid->getDateTime()->format('r'));

        // Check the oldest date
        $uuid = Uuid::fromString('00000000-0000-1000-9669-00007ffffffe');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Sat, 15 Oct 1582 00:00:00 +0000', $uuid->getDateTime()->format('r'));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getDateTime
     */
    public function testGetDateTime32Bit()
    {
        Uuid::$force32Bit = true;

        // Check a recent date
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Wed, 04 Jul 2012 02:14:34 +0000', $uuid->getDateTime()->format('r'));

        // Check an old date
        $uuid = Uuid::fromString('0901e600-0154-1000-9b21-0800200c9a66');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Sun, 16 Oct 1582 16:34:04 +0000', $uuid->getDateTime()->format('r'));

        // Check a future date
        $uuid = Uuid::fromString('ff9785f6-ffff-1fff-9669-00007ffffffe');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Mon, 31 Mar 5236 21:21:00 +0000', $uuid->getDateTime()->format('r'));

        // Check the oldest date
        $uuid = Uuid::fromString('00000000-0000-1000-9669-00007ffffffe');
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals('Sat, 15 Oct 1582 00:00:00 +0000', $uuid->getDateTime()->format('r'));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getDateTime
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetDateTimeThrownException()
    {
        Uuid::$force32Bit = true;
        Uuid::$forceNoBigNumber = true;

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $date = $uuid->getDateTime();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getDateTime
     * @covers Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedException Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedExceptionMessage Not a time-based UUID
     */
    public function testGetDateTimeFromNonVersion1Uuid()
    {
        // Using a version 4 UUID to test
        $uuid = Uuid::fromString('bf17b594-41f2-474f-bf70-4c90220f75de');
        $date = $uuid->getDateTime();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getFields
     */
    public function testGetFields()
    {
        $this->skip64BitTest();

        $fields = array(
            'time_low' => 4285500592,
            'time_mid' => 50557,
            'time_hi_and_version' => 4577,
            'clock_seq_hi_and_reserved' => 155,
            'clock_seq_low' => 33,
            'node' => 8796630719078,
        );

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        $this->assertEquals($fields, $uuid->getFields());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getFields
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetFields32Bit()
    {
        Uuid::$force32Bit = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $fields = $uuid->getFields();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getFieldsHex
     */
    public function testGetFieldsHex()
    {
        $fields = array(
            'time_low' => 'ff6f8cb0',
            'time_mid' => 'c57d',
            'time_hi_and_version' => '11e1',
            'clock_seq_hi_and_reserved' => '9b',
            'clock_seq_low' => '21',
            'node' => '0800200c9a66',
        );

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        $this->assertEquals($fields, $uuid->getFieldsHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getLeastSignificantBits
     */
    public function testGetLeastSignificantBits()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('Moontoast\Math\BigNumber', $uuid->getLeastSignificantBits());
        $this->assertEquals('11178224546741000806', $uuid->getLeastSignificantBits()->getValue());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getLeastSignificantBits
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetLeastSignificantBitsException()
    {
        Uuid::$forceNoBigNumber = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $bn = $uuid->getLeastSignificantBits();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getLeastSignificantBitsHex
     */
    public function testGetLeastSignificantBitsHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('9b210800200c9a66', $uuid->getLeastSignificantBitsHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getMostSignificantBits
     */
    public function testGetMostSignificantBits()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf('Moontoast\Math\BigNumber', $uuid->getMostSignificantBits());
        $this->assertEquals('18406084892941947361', $uuid->getMostSignificantBits()->getValue());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getMostSignificantBits
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetMostSignificantBitsException()
    {
        Uuid::$forceNoBigNumber = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $bn = $uuid->getMostSignificantBits();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getMostSignificantBitsHex
     */
    public function testGetMostSignificantBitsHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('ff6f8cb0c57d11e1', $uuid->getMostSignificantBitsHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getNode
     */
    public function testGetNode()
    {
        $this->skip64BitTest();

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(8796630719078, $uuid->getNode());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getNode
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetNode32Bit()
    {
        Uuid::$force32Bit = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $node = $uuid->getNode();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getNodeHex
     */
    public function testGetNodeHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('0800200c9a66', $uuid->getNodeHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeHiAndVersion
     */
    public function testGetTimeHiAndVersion()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(4577, $uuid->getTimeHiAndVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeHiAndVersionHex
     */
    public function testGetTimeHiAndVersionHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('11e1', $uuid->getTimeHiAndVersionHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeLow
     */
    public function testGetTimeLow()
    {
        $this->skip64BitTest();

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(4285500592, $uuid->getTimeLow());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeLow
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetTimeLow32Bit()
    {
        Uuid::$force32Bit = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $timeLow = $uuid->getTimeLow();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeLowHex
     */
    public function testGetTimeLowHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('ff6f8cb0', $uuid->getTimeLowHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeMid
     */
    public function testGetTimeMid()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(50557, $uuid->getTimeMid());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimeMidHex
     */
    public function testGetTimeMidHex()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('c57d', $uuid->getTimeMidHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimestamp
     */
    public function testGetTimestamp()
    {
        $this->skip64BitTest();

        // Check for a recent date
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(135606608744910000, $uuid->getTimestamp());

        // Check for an old date
        $uuid = Uuid::fromString('0901e600-0154-1000-9b21-0800200c9a66');
        $this->assertEquals(1460440000000, $uuid->getTimestamp());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimestampHex
     */
    public function testGetTimestampHex()
    {
        // Check for a recent date
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('1e1c57dff6f8cb0', $uuid->getTimestampHex());

        // Check for an old date
        $uuid = Uuid::fromString('0901e600-0154-1000-9b21-0800200c9a66');
        $this->assertEquals('00001540901e600', $uuid->getTimestampHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimestamp
     * @covers Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedException Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedExceptionMessage Not a time-based UUID
     */
    public function testGetTimestampFromNonVersion1Uuid()
    {
        // Using a version 4 UUID to test
        $uuid = Uuid::fromString('bf17b594-41f2-474f-bf70-4c90220f75de');
        $ts = $uuid->getTimestamp();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimestampHex
     * @covers Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedException Rhumsaa\Uuid\Exception\UnsupportedOperationException
     * @expectedExceptionMessage Not a time-based UUID
     */
    public function testGetTimestampHexFromNonVersion1Uuid()
    {
        // Using a version 4 UUID to test
        $uuid = Uuid::fromString('bf17b594-41f2-474f-bf70-4c90220f75de');
        $ts = $uuid->getTimestampHex();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getTimestamp
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testGetTimestamp32Bit()
    {
        Uuid::$force32Bit = true;
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $ts = $uuid->getTimestamp();
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getUrn
     */
    public function testGetUrn()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('urn:uuid:ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->getUrn());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVariant
     */
    public function testGetVariantForReservedNcs()
    {
        $uuid1 = Uuid::fromString('ff6f8cb0-c57d-11e1-0b21-0800200c9a66');
        $this->assertEquals(0, $uuid1->getVariant());

        $uuid2 = Uuid::fromString('ff6f8cb0-c57d-11e1-1b21-0800200c9a66');
        $this->assertEquals(0, $uuid2->getVariant());

        $uuid3 = Uuid::fromString('ff6f8cb0-c57d-11e1-2b21-0800200c9a66');
        $this->assertEquals(0, $uuid3->getVariant());

        $uuid4 = Uuid::fromString('ff6f8cb0-c57d-11e1-3b21-0800200c9a66');
        $this->assertEquals(0, $uuid4->getVariant());

        $uuid5 = Uuid::fromString('ff6f8cb0-c57d-11e1-4b21-0800200c9a66');
        $this->assertEquals(0, $uuid5->getVariant());

        $uuid6 = Uuid::fromString('ff6f8cb0-c57d-11e1-5b21-0800200c9a66');
        $this->assertEquals(0, $uuid6->getVariant());

        $uuid7 = Uuid::fromString('ff6f8cb0-c57d-11e1-6b21-0800200c9a66');
        $this->assertEquals(0, $uuid7->getVariant());

        $uuid8 = Uuid::fromString('ff6f8cb0-c57d-11e1-7b21-0800200c9a66');
        $this->assertEquals(0, $uuid8->getVariant());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVariant
     */
    public function testGetVariantForRfc4122()
    {
        $uuid1 = Uuid::fromString('ff6f8cb0-c57d-11e1-8b21-0800200c9a66');
        $this->assertEquals(2, $uuid1->getVariant());

        $uuid2 = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(2, $uuid2->getVariant());

        $uuid3 = Uuid::fromString('ff6f8cb0-c57d-11e1-ab21-0800200c9a66');
        $this->assertEquals(2, $uuid3->getVariant());

        $uuid4 = Uuid::fromString('ff6f8cb0-c57d-11e1-bb21-0800200c9a66');
        $this->assertEquals(2, $uuid4->getVariant());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVariant
     */
    public function testGetVariantForReservedMicrosoft()
    {
        $uuid1 = Uuid::fromString('ff6f8cb0-c57d-11e1-cb21-0800200c9a66');
        $this->assertEquals(6, $uuid1->getVariant());

        $uuid2 = Uuid::fromString('ff6f8cb0-c57d-11e1-db21-0800200c9a66');
        $this->assertEquals(6, $uuid2->getVariant());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVariant
     */
    public function testGetVariantForReservedFuture()
    {
        $uuid1 = Uuid::fromString('ff6f8cb0-c57d-11e1-eb21-0800200c9a66');
        $this->assertEquals(7, $uuid1->getVariant());

        $uuid2 = Uuid::fromString('ff6f8cb0-c57d-11e1-fb21-0800200c9a66');
        $this->assertEquals(7, $uuid2->getVariant());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testGetVersionForVersion1()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals(1, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testGetVersionForVersion2()
    {
        $uuid = Uuid::fromString('6fa459ea-ee8a-2ca4-894e-db77e160355e');
        $this->assertEquals(2, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testGetVersionForVersion3()
    {
        $uuid = Uuid::fromString('6fa459ea-ee8a-3ca4-894e-db77e160355e');
        $this->assertEquals(3, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testGetVersionForVersion4()
    {
        $uuid = Uuid::fromString('6fabf0bc-603a-42f2-925b-d9f779bd0032');
        $this->assertEquals(4, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testGetVersionForVersion5()
    {
        $uuid = Uuid::fromString('886313e1-3b8a-5372-9b90-0c9aee199e5d');
        $this->assertEquals(5, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::toString
     * @covers Rhumsaa\Uuid\Uuid::__toString
     */
    public function testToString()
    {
        // Check with a recent date
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->toString());
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', sprintf('%s', $uuid));

        // Check with an old date
        $uuid = Uuid::fromString('0901e600-0154-1000-9b21-0800200c9a66');
        $this->assertEquals('0901e600-0154-1000-9b21-0800200c9a66', $uuid->toString());
        $this->assertEquals('0901e600-0154-1000-9b21-0800200c9a66', sprintf('%s', $uuid));
    }

    /**
     * This calls php_uname() in getNodeFromSystem. The first time it is
     * called, it returns "WIN." Each additional times, it returns the
     * normal system php_uname().
     *
     * See the bottom of this test file to see where we are overriding
     * php_uname() for the purpose of this test.
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::getNodeFromSystem
     */
    public function testUuid1CoverageForWindows()
    {
        $uuid = Uuid::uuid1();
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::getNodeFromSystem
     */
    public function testUuid1()
    {
        $uuid = Uuid::uuid1();
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     */
    public function testUuid1WithNodeAndClockSequence()
    {
        $this->skip64BitTest();

        $uuid = Uuid::uuid1(0x0800200c9a66, 0x1669);
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
        $this->assertEquals(5737, $uuid->getClockSequence());
        $this->assertEquals(8796630719078, $uuid->getNode());
        $this->assertEquals('9669-0800200c9a66', substr($uuid->toString(), 19));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     */
    public function testUuid1WithHexadecimalNode()
    {
        $uuid = Uuid::uuid1('7160355e');

        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
        $this->assertEquals('00007160355e', $uuid->getNodeHex());

        if (PHP_INT_SIZE == 8) {
            $this->assertEquals(1902130526, $uuid->getNode());
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     */
    public function testUuid1WithMixedCaseHexadecimalNode()
    {
        $uuid = Uuid::uuid1('71B0aD5e');

        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
        $this->assertEquals('000071b0ad5e', $uuid->getNodeHex());

        if (PHP_INT_SIZE == 8) {
            $this->assertEquals(1907404126, $uuid->getNode());
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     */
    public function testUuid1WithNodeAndClockSequence32Bit()
    {
        $uuid = Uuid::uuid1(0x7fffffff, 0x1669);
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
        $this->assertEquals(5737, $uuid->getClockSequence());
        $this->assertEquals('00007fffffff', $uuid->getNodeHex());
        $this->assertEquals('9669-00007fffffff', substr($uuid->toString(), 19));

        if (PHP_INT_SIZE == 8) {
            $this->assertEquals(2147483647, $uuid->getNode());
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid node value
     */
    public function testUuid1WithOutOfBoundsNode()
    {
        $uuid = Uuid::uuid1(9223372036854775808);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid node value
     */
    public function testUuid1WithNonHexadecimalNode()
    {
        $uuid = Uuid::uuid1('db77e160355g');
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid node value
     */
    public function testUuid1WithNon48bitNumber()
    {
        $uuid = Uuid::uuid1('db77e160355ef');
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     */
    public function testUuid1WithRandomNode()
    {
        Uuid::$ignoreSystemNode = true;

        $uuid = Uuid::uuid1();
        $this->assertInstanceOf('\Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertInstanceOf('\DateTime', $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
    }

    /**
     * The "python.org" UUID is a known entity, so we're testing that this
     * library generates a matching UUID for the same name.
     * @see http://docs.python.org/library/uuid.html
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid3
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid3WithNamespaceAsUuidObject()
    {
        $nsUuid = Uuid::fromString(Uuid::NAMESPACE_DNS);
        $uuid = Uuid::uuid3($nsUuid, 'python.org');
        $this->assertEquals('6fa459ea-ee8a-3ca4-894e-db77e160355e', $uuid->toString());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(3, $uuid->getVersion());
    }

    /**
     * The "python.org" UUID is a known entity, so we're testing that this
     * library generates a matching UUID for the same name.
     * @see http://docs.python.org/library/uuid.html
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid3
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid3WithNamespaceAsUuidString()
    {
        $uuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, 'python.org');
        $this->assertEquals('6fa459ea-ee8a-3ca4-894e-db77e160355e', $uuid->toString());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(3, $uuid->getVersion());
    }

    /**
     * Tests more known version-3 UUIDs
     *
     * Taken from the Python UUID tests in
     * http://hg.python.org/cpython/file/2f4c4db9aee5/Lib/test/test_uuid.py
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid3
     */
    public function testUuid3WithKnownUuids()
    {
        $uuids = array(
            '6fa459ea-ee8a-3ca4-894e-db77e160355e' => Uuid::uuid3(Uuid::NAMESPACE_DNS, 'python.org'),
            '9fe8e8c4-aaa8-32a9-a55c-4535a88b748d' => Uuid::uuid3(Uuid::NAMESPACE_URL, 'http://python.org/'),
            'dd1a1cef-13d5-368a-ad82-eca71acd4cd1' => Uuid::uuid3(Uuid::NAMESPACE_OID, '1.3.6.1'),
            '658d3002-db6b-3040-a1d1-8ddd7d189a4d' => Uuid::uuid3(Uuid::NAMESPACE_X500, 'c=ca'),
        );

        foreach ($uuids as $ustr => $uobj) {
            $this->assertEquals(Uuid::RFC_4122, $uobj->getVariant());
            $this->assertEquals(3, $uobj->getVersion());
            $this->assertEquals(Uuid::fromString($ustr), $uobj);
            $this->assertEquals((string) $uobj, $ustr);
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid4
     * @covers Rhumsaa\Uuid\Uuid::generateBytes
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid4()
    {
        $uuid = Uuid::uuid4();
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(4, $uuid->getVersion());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid4
     * @covers Rhumsaa\Uuid\Uuid::generateBytes
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid4WithoutOpensslRandomPseudoBytes()
    {
        Uuid::$forceNoOpensslRandomPseudoBytes = true;
        $uuid = Uuid::uuid4();
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $uuid);
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(4, $uuid->getVersion());
    }

    /**
     * The "python.org" UUID is a known entity, so we're testing that this
     * library generates a matching UUID for the same name.
     * @see http://docs.python.org/library/uuid.html
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid5
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid5WithNamespaceAsUuidObject()
    {
        $nsUuid = Uuid::fromString(Uuid::NAMESPACE_DNS);
        $uuid = Uuid::uuid5($nsUuid, 'python.org');
        $this->assertEquals('886313e1-3b8a-5372-9b90-0c9aee199e5d', $uuid->toString());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(5, $uuid->getVersion());
    }

    /**
     * The "python.org" UUID is a known entity, so we're testing that this
     * library generates a matching UUID for the same name.
     * @see http://docs.python.org/library/uuid.html
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid5
     * @covers Rhumsaa\Uuid\Uuid::uuidFromHashedName
     */
    public function testUuid5WithNamespaceAsUuidString()
    {
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, 'python.org');
        $this->assertEquals('886313e1-3b8a-5372-9b90-0c9aee199e5d', $uuid->toString());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(5, $uuid->getVersion());
    }

    /**
     * Tests more known version-5 UUIDs
     *
     * Taken from the Python UUID tests in
     * http://hg.python.org/cpython/file/2f4c4db9aee5/Lib/test/test_uuid.py
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid5
     */
    public function testUuid5WithKnownUuids()
    {
        $uuids = array(
            '886313e1-3b8a-5372-9b90-0c9aee199e5d' => Uuid::uuid5(Uuid::NAMESPACE_DNS, 'python.org'),
            '4c565f0d-3f5a-5890-b41b-20cf47701c5e' => Uuid::uuid5(Uuid::NAMESPACE_URL, 'http://python.org/'),
            '1447fa61-5277-5fef-a9b3-fbc6e44f4af3' => Uuid::uuid5(Uuid::NAMESPACE_OID, '1.3.6.1'),
            'cc957dd1-a972-5349-98cd-874190002798' => Uuid::uuid5(Uuid::NAMESPACE_X500, 'c=ca'),
        );

        foreach ($uuids as $ustr => $uobj) {
            $this->assertEquals(Uuid::RFC_4122, $uobj->getVariant());
            $this->assertEquals(5, $uobj->getVersion());
            $this->assertEquals(Uuid::fromString($ustr), $uobj);
            $this->assertEquals((string) $uobj, $ustr);
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::compareTo
     */
    public function testCompareTo()
    {
        // $uuid1 and $uuid2 are identical
        $uuid1 = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $uuid2 = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        // The next three UUIDs are used for comparing msb and lsb in
        // the compareTo() method

        // msb are less than $uuid4, lsb are greater than $uuid5
        $uuid3 = Uuid::fromString('44cca71e-d13d-11e1-a959-c8bcc8a476f4');

        // msb are greater than $uuid3, lsb are equal to those in $uuid3
        $uuid4 = Uuid::fromString('44cca71e-d13d-11e2-a959-c8bcc8a476f4');

        // msb are equal to those in $uuid3, lsb are less than in $uuid3
        $uuid5 = Uuid::fromString('44cca71e-d13d-11e1-a959-c8bcc8a476f3');

        $this->assertEquals(0, $uuid1->compareTo($uuid2));
        $this->assertEquals(0, $uuid2->compareTo($uuid1));
        $this->assertEquals(-1, $uuid3->compareTo($uuid4));
        $this->assertEquals(1, $uuid4->compareTo($uuid3));
        $this->assertEquals(-1, $uuid5->compareTo($uuid3));
        $this->assertEquals(1, $uuid3->compareto($uuid5));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::equals
     */
    public function testEquals()
    {
        $uuid1 = Uuid::uuid5(Uuid::NAMESPACE_DNS, 'python.org');
        $uuid2 = Uuid::uuid5(Uuid::NAMESPACE_DNS, 'python.org');
        $uuid3 = Uuid::uuid5(Uuid::NAMESPACE_DNS, 'php.net');

        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertFalse($uuid1->equals($uuid3));
        $this->assertFalse($uuid1->equals(null));
        $this->assertFalse($uuid1->equals(new \stdClass()));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTime()
    {
        $timeOfDay = array(
            'sec' => 1348845514,
            'usec' => 277885,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        // For usec = 277885
        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c4dbe7e2-097f-11e2-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('c4dbe7e2', $uuidA->getTimeLowHex());
        $this->assertEquals('097f', $uuidA->getTimeMidHex());
        $this->assertEquals('11e2', $uuidA->getTimeHiAndVersionHex());

        // For usec = 0
        Uuid::$timeOfDayTest['usec'] = 0;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c4b18100-097f-11e2-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('c4b18100', $uuidB->getTimeLowHex());
        $this->assertEquals('097f', $uuidB->getTimeMidHex());
        $this->assertEquals('11e2', $uuidB->getTimeHiAndVersionHex());

        // For usec = 999999
        Uuid::$timeOfDayTest['usec'] = 999999;
        $uuidC = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c54a1776-097f-11e2-9669-00007ffffffe', (string) $uuidC);
        $this->assertEquals('c54a1776', $uuidC->getTimeLowHex());
        $this->assertEquals('097f', $uuidC->getTimeMidHex());
        $this->assertEquals('11e2', $uuidC->getTimeHiAndVersionHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTimeForce32BitPath()
    {
        Uuid::$force32Bit = true;

        $timeOfDay = array(
            'sec' => 1348845514,
            'usec' => 277885,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        // For usec = 277885
        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c4dbe7e2-097f-11e2-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('c4dbe7e2', $uuidA->getTimeLowHex());
        $this->assertEquals('097f', $uuidA->getTimeMidHex());
        $this->assertEquals('11e2', $uuidA->getTimeHiAndVersionHex());

        // For usec = 0
        Uuid::$timeOfDayTest['usec'] = 0;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c4b18100-097f-11e2-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('c4b18100', $uuidB->getTimeLowHex());
        $this->assertEquals('097f', $uuidB->getTimeMidHex());
        $this->assertEquals('11e2', $uuidB->getTimeHiAndVersionHex());

        // For usec = 999999
        Uuid::$timeOfDayTest['usec'] = 999999;
        $uuidC = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('c54a1776-097f-11e2-9669-00007ffffffe', (string) $uuidC);
        $this->assertEquals('c54a1776', $uuidC->getTimeLowHex());
        $this->assertEquals('097f', $uuidC->getTimeMidHex());
        $this->assertEquals('11e2', $uuidC->getTimeHiAndVersionHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTimeUpperLowerBounds64Bit()
    {
        $this->skip64BitTest();

        // Mon, 31 Mar 5236 21:20:59 +0000
        $timeOfDay = array(
            'sec' => 103072857659,
            'usec' => 999999,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('ff9785f6-ffff-1fff-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('ff9785f6', $uuidA->getTimeLowHex());
        $this->assertEquals('ffff', $uuidA->getTimeMidHex());
        $this->assertEquals('1fff', $uuidA->getTimeHiAndVersionHex());

        // Sat, 15 Oct 1582 00:00:00 +0000
        $timeOfDay = array(
            'sec' => -12219292800,
            'usec' => 0,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('00000000-0000-1000-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('00000000', $uuidB->getTimeLowHex());
        $this->assertEquals('0000', $uuidB->getTimeMidHex());
        $this->assertEquals('1000', $uuidB->getTimeHiAndVersionHex());
    }

    /**
     * This test ensures that the UUIDs generated by the 32-bit path match
     * those generated by the 64-bit path, given the same 64-bit time values.
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTimeUpperLowerBounds64BitThrough32BitPath()
    {
        $this->skip64BitTest();

        Uuid::$force32Bit = true;

        // Mon, 31 Mar 5236 21:20:59 +0000
        $timeOfDay = array(
            'sec' => 103072857659,
            'usec' => 999999,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('ff9785f6-ffff-1fff-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('ff9785f6', $uuidA->getTimeLowHex());
        $this->assertEquals('ffff', $uuidA->getTimeMidHex());
        $this->assertEquals('1fff', $uuidA->getTimeHiAndVersionHex());

        // Sat, 15 Oct 1582 00:00:00 +0000
        $timeOfDay = array(
            'sec' => -12219292800,
            'usec' => 0,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('00000000-0000-1000-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('00000000', $uuidB->getTimeLowHex());
        $this->assertEquals('0000', $uuidB->getTimeMidHex());
        $this->assertEquals('1000', $uuidB->getTimeHiAndVersionHex());
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTimeUpperLowerBounds32Bit()
    {
        Uuid::$force32Bit = true;

        // Tue, 19 Jan 2038 03:14:07 +0000
        $timeOfDay = array(
            'sec' => 2147483647,
            'usec' => 999999,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('13813ff6-6912-11fe-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('13813ff6', $uuidA->getTimeLowHex());
        $this->assertEquals('6912', $uuidA->getTimeMidHex());
        $this->assertEquals('11fe', $uuidA->getTimeHiAndVersionHex());

        // Fri, 13 Dec 1901 20:45:53 +0000
        $timeOfDay = array(
            'sec' => -2147483647,
            'usec' => 0,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('1419d680-d292-1165-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('1419d680', $uuidB->getTimeLowHex());
        $this->assertEquals('d292', $uuidB->getTimeMidHex());
        $this->assertEquals('1165', $uuidB->getTimeHiAndVersionHex());
    }

    /**
     * This test ensures that the UUIDs generated by the 64-bit path match
     * those generated by the 32-bit path, given the same 32-bit time values.
     *
     * @covers Rhumsaa\Uuid\Uuid::uuid1
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     */
    public function testCalculateUuidTimeUpperLowerBounds32BitThrough64BitPath()
    {
        $this->skip64BitTest();

        // Tue, 19 Jan 2038 03:14:07 +0000
        $timeOfDay = array(
            'sec' => 2147483647,
            'usec' => 999999,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidA = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('13813ff6-6912-11fe-9669-00007ffffffe', (string) $uuidA);
        $this->assertEquals('13813ff6', $uuidA->getTimeLowHex());
        $this->assertEquals('6912', $uuidA->getTimeMidHex());
        $this->assertEquals('11fe', $uuidA->getTimeHiAndVersionHex());

        // Fri, 13 Dec 1901 20:45:53 +0000
        $timeOfDay = array(
            'sec' => -2147483647,
            'usec' => 0,
            'minuteswest' => 0,
            'dsttime' => 0,
        );

        Uuid::$timeOfDayTest = $timeOfDay;
        $uuidB = Uuid::uuid1(0x00007ffffffe, 0x1669);

        $this->assertEquals('1419d680-d292-1165-9669-00007ffffffe', (string) $uuidB);
        $this->assertEquals('1419d680', $uuidB->getTimeLowHex());
        $this->assertEquals('d292', $uuidB->getTimeMidHex());
        $this->assertEquals('1165', $uuidB->getTimeHiAndVersionHex());
    }

    /**
     * Iterates over a 3600-second period and tests to ensure that, for each
     * second in the period, the 32-bit and 64-bit versions of the UUID match
     */
    public function test32BitMatch64BitForOneHourPeriod()
    {
        $this->skip64BitTest();

        $currentTime = strtotime('Tue, 11 Dec 2012 00:00:00 +0000');
        $endTime = $currentTime + 3600;

        while ($currentTime <= $endTime) {

            foreach (array(0, 50000, 250000, 500000, 750000, 999999) as $usec) {

                $timeOfDay = array(
                    'sec' => $currentTime,
                    'usec' => $usec,
                    'minuteswest' => 0,
                    'dsttime' => 0,
                );

                Uuid::$timeOfDayTest = $timeOfDay;

                Uuid::$force32Bit = true;
                $uuid32 = Uuid::uuid1(0x00007ffffffe, 0x1669);

                Uuid::$force32Bit = false;
                $uuid64 = Uuid::uuid1(0x00007ffffffe, 0x1669);

                $this->assertTrue(
                    $uuid32->equals($uuid64),
                    'Breaks at ' . gmdate('r', $currentTime) . "; 32-bit: {$uuid32->toString()}, 64-bit: {$uuid64->toString()}"
                );

                // Assert that the time matches
                $testTime = round($currentTime + ($usec / 1000000));
                $this->assertEquals($testTime, $uuid64->getDateTime()->getTimestamp());
                $this->assertEquals($testTime, $uuid32->getDateTime()->getTimestamp());
            }

            $currentTime++;
        }
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::calculateUuidTime
     * @expectedException Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException
     */
    public function testCalculateUuidTimeThrownException()
    {
        Uuid::$force32Bit = true;
        Uuid::$forceNoBigNumber = true;

        $uuid = Uuid::uuid1(0x00007ffffffe, 0x1669);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::hasBigNumber
     */
    public function testHasBigNumber()
    {
        $hasBigNumber = new \ReflectionMethod(
            'Rhumsaa\Uuid\Uuid', 'hasBigNumber'
        );
        $hasBigNumber->setAccessible(true);

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        $this->assertTrue($hasBigNumber->invoke($uuid));

        Uuid::$forceNoBigNumber = true;
        $this->assertFalse($hasBigNumber->invoke($uuid));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::hasOpensslRandomPseudoBytes
     */
    public function testHasOpensslRandomPseudoBytes()
    {
        $hasOpensslRandomPseudoBytes = new \ReflectionMethod(
            'Rhumsaa\Uuid\Uuid', 'hasOpensslRandomPseudoBytes'
        );
        $hasOpensslRandomPseudoBytes->setAccessible(true);

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        $this->assertTrue($hasOpensslRandomPseudoBytes->invoke($uuid));

        Uuid::$forceNoOpensslRandomPseudoBytes = true;
        $this->assertFalse($hasOpensslRandomPseudoBytes->invoke($uuid));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::is64BitSystem
     */
    public function testIs64BitSystem()
    {
        $is64BitSystem = new \ReflectionMethod(
            'Rhumsaa\Uuid\Uuid', 'is64BitSystem'
        );
        $is64BitSystem->setAccessible(true);

        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');

        if (PHP_INT_SIZE == 8) {
            $this->assertTrue($is64BitSystem->invoke($uuid));
        } else {
            $this->assertFalse($is64BitSystem->invoke($uuid));
        }

        Uuid::$force32Bit = true;
        $this->assertFalse($is64BitSystem->invoke($uuid));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodVersion1()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodVersion2()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57d-21e1-9b21-0800200c9a66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodVersion3()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57d-31e1-9b21-0800200c9a66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodVersion4()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57d-41e1-9b21-0800200c9a66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodVersion5()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57d-51e1-9b21-0800200c9a66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidGoodUpperCase()
    {
        $valid = Uuid::isValid('FF6F8CB0-C57D-11E1-9B21-0800200C9A66');
        $this->assertTrue($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidBadHex()
    {
        $valid = Uuid::isValid('zf6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertFalse($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidTooShort1()
    {
        $valid = Uuid::isValid('3f6f8cb0-c57d-11e1-9b21-0800200c9a6');
        $this->assertFalse($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidTooShort2()
    {
        $valid = Uuid::isValid('af6f8cb-c57d-11e1-9b21-0800200c9a66');
        $this->assertFalse($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidNoDashes()
    {
        $valid = Uuid::isValid('af6f8cb0c57d11e19b210800200c9a66');
        $this->assertFalse($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testIsValidTooLong()
    {
        $valid = Uuid::isValid('ff6f8cb0-c57da-51e1-9b21-0800200c9a66');
        $this->assertFalse($valid);
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::isValid
     */
    public function testUsingNilAsValidUuid()
    {
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', Uuid::uuid3(Uuid::NIL, 'randomtext'));
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', Uuid::uuid5(Uuid::NIL, 'randomtext'));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromBytes
     */
    public function testFromBytes()
    {
        $uuid = Uuid::fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $bytes = $uuid->getBytes();

        $fromBytesUuid = Uuid::fromBytes($bytes);

        $this->assertTrue($uuid->equals($fromBytesUuid));
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromBytes
     * @expectedException InvalidArgumentException
     */
    public function testFromBytesArgumentTooShort()
    {
        Uuid::fromBytes('thisisveryshort');
    }

    /**
     * @covers Rhumsaa\Uuid\Uuid::fromBytes
     * @expectedException InvalidArgumentException
     */
    public function testFromBytesArgumentTooLong()
    {
        Uuid::fromBytes('thisisabittoolong');
    }

    /**
     * This test ensures that Rhumsaa\Uuid passes the same test cases
     * as the Python UUID library.
     *
     * Taken from the Python UUID tests in
     * http://hg.python.org/cpython/file/2f4c4db9aee5/Lib/test/test_uuid.py
     *
     * @covers Rhumsaa\Uuid\Uuid::fromString
     * @covers Rhumsaa\Uuid\Uuid::fromBytes
     * @covers Rhumsaa\Uuid\Uuid::toString
     * @covers Rhumsaa\Uuid\Uuid::getBytes
     * @covers Rhumsaa\Uuid\Uuid::getFieldsHex
     * @covers Rhumsaa\Uuid\Uuid::getTimeLowHex
     * @covers Rhumsaa\Uuid\Uuid::getTimeMidHex
     * @covers Rhumsaa\Uuid\Uuid::getTimeHiAndVersionHex
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqHiAndReservedHex
     * @covers Rhumsaa\Uuid\Uuid::getClockSeqLowHex
     * @covers Rhumsaa\Uuid\Uuid::getNodeHex
     * @covers Rhumsaa\Uuid\Uuid::getUrn
     * @covers Rhumsaa\Uuid\Uuid::getTimestampHex
     * @covers Rhumsaa\Uuid\Uuid::getClockSequenceHex
     * @covers Rhumsaa\Uuid\Uuid::getVariant
     * @covers Rhumsaa\Uuid\Uuid::getVersion
     */
    public function testUuidPassesPythonTests()
    {
        // This array is taken directly from the Python tests, more or less
        $tests = array(
            array(
                'string' => '00000000-0000-0000-0000-000000000000',
                'curly' => '{00000000-0000-0000-0000-000000000000}',
                'hex' => '00000000000000000000000000000000',
                'bytes' => 'AAAAAAAAAAAAAAAAAAAAAA==',
                'fields' => array(
                    'time_low' => '0',
                    'time_mid' => '0',
                    'time_hi_and_version' => '0',
                    'clock_seq_hi_and_reserved' => '0',
                    'clock_seq_low' => '0',
                    'node' => '0',
                ),
                'urn' => 'urn:uuid:00000000-0000-0000-0000-000000000000',
                'time' => '0',
                'clock_seq' => '0000',
                'variant' => Uuid::RESERVED_NCS,
                'version' => null,
            ),
            array(
                'string' => '00010203-0405-0607-0809-0a0b0c0d0e0f',
                'curly' => '{00010203-0405-0607-0809-0a0b0c0d0e0f}',
                'hex' => '000102030405060708090a0b0c0d0e0f',
                'bytes' => 'AAECAwQFBgcICQoLDA0ODw==',
                'fields' => array(
                    'time_low' => '10203',
                    'time_mid' => '405',
                    'time_hi_and_version' => '607',
                    'clock_seq_hi_and_reserved' => '8',
                    'clock_seq_low' => '9',
                    'node' => '0a0b0c0d0e0f',
                ),
                'urn' => 'urn:uuid:00010203-0405-0607-0809-0a0b0c0d0e0f',
                'time' => '607040500010203',
                'clock_seq' => '0809',
                'variant' => Uuid::RESERVED_NCS,
                'version' => null,
            ),
            array(
                'string' => '02d9e6d5-9467-382e-8f9b-9300a64ac3cd',
                'curly' => '{02d9e6d5-9467-382e-8f9b-9300a64ac3cd}',
                'hex' => '02d9e6d59467382e8f9b9300a64ac3cd',
                'bytes' => 'Atnm1ZRnOC6Pm5MApkrDzQ==',
                'fields' => array(
                    'time_low' => '02d9e6d5',
                    'time_mid' => '9467',
                    'time_hi_and_version' => '382e',
                    'clock_seq_hi_and_reserved' => '8f',
                    'clock_seq_low' => '9b',
                    'node' => '9300a64ac3cd',
                ),
                'urn' => 'urn:uuid:02d9e6d5-9467-382e-8f9b-9300a64ac3cd',
                'time' => '82e946702d9e6d5',
                'clock_seq' => '0f9b',
                'variant' => Uuid::RFC_4122,
                'version' => 3,
            ),
            array(
                'string' => '12345678-1234-5678-1234-567812345678',
                'curly' => '{12345678-1234-5678-1234-567812345678}',
                'hex' => '12345678123456781234567812345678',
                'bytes' => 'EjRWeBI0VngSNFZ4EjRWeA==',
                'fields' => array(
                    'time_low' => '12345678',
                    'time_mid' => '1234',
                    'time_hi_and_version' => '5678',
                    'clock_seq_hi_and_reserved' => '12',
                    'clock_seq_low' => '34',
                    'node' => '567812345678',
                ),
                'urn' => 'urn:uuid:12345678-1234-5678-1234-567812345678',
                'time' => '678123412345678',
                'clock_seq' => '1234',
                'variant' => Uuid::RESERVED_NCS,
                'version' => null,
            ),
            array(
                'string' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                'curly' => '{6ba7b810-9dad-11d1-80b4-00c04fd430c8}',
                'hex' => '6ba7b8109dad11d180b400c04fd430c8',
                'bytes' => 'a6e4EJ2tEdGAtADAT9QwyA==',
                'fields' => array(
                    'time_low' => '6ba7b810',
                    'time_mid' => '9dad',
                    'time_hi_and_version' => '11d1',
                    'clock_seq_hi_and_reserved' => '80',
                    'clock_seq_low' => 'b4',
                    'node' => '00c04fd430c8',
                ),
                'urn' => 'urn:uuid:6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                'time' => '1d19dad6ba7b810',
                'clock_seq' => '00b4',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => '6ba7b811-9dad-11d1-80b4-00c04fd430c8',
                'curly' => '{6ba7b811-9dad-11d1-80b4-00c04fd430c8}',
                'hex' => '6ba7b8119dad11d180b400c04fd430c8',
                'bytes' => 'a6e4EZ2tEdGAtADAT9QwyA==',
                'fields' => array(
                    'time_low' => '6ba7b811',
                    'time_mid' => '9dad',
                    'time_hi_and_version' => '11d1',
                    'clock_seq_hi_and_reserved' => '80',
                    'clock_seq_low' => 'b4',
                    'node' => '00c04fd430c8',
                ),
                'urn' => 'urn:uuid:6ba7b811-9dad-11d1-80b4-00c04fd430c8',
                'time' => '1d19dad6ba7b811',
                'clock_seq' => '00b4',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => '6ba7b812-9dad-11d1-80b4-00c04fd430c8',
                'curly' => '{6ba7b812-9dad-11d1-80b4-00c04fd430c8}',
                'hex' => '6ba7b8129dad11d180b400c04fd430c8',
                'bytes' => 'a6e4Ep2tEdGAtADAT9QwyA==',
                'fields' => array(
                    'time_low' => '6ba7b812',
                    'time_mid' => '9dad',
                    'time_hi_and_version' => '11d1',
                    'clock_seq_hi_and_reserved' => '80',
                    'clock_seq_low' => 'b4',
                    'node' => '00c04fd430c8',
                ),
                'urn' => 'urn:uuid:6ba7b812-9dad-11d1-80b4-00c04fd430c8',
                'time' => '1d19dad6ba7b812',
                'clock_seq' => '00b4',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => '6ba7b814-9dad-11d1-80b4-00c04fd430c8',
                'curly' => '{6ba7b814-9dad-11d1-80b4-00c04fd430c8}',
                'hex' => '6ba7b8149dad11d180b400c04fd430c8',
                'bytes' => 'a6e4FJ2tEdGAtADAT9QwyA==',
                'fields' => array(
                    'time_low' => '6ba7b814',
                    'time_mid' => '9dad',
                    'time_hi_and_version' => '11d1',
                    'clock_seq_hi_and_reserved' => '80',
                    'clock_seq_low' => 'b4',
                    'node' => '00c04fd430c8',
                ),
                'urn' => 'urn:uuid:6ba7b814-9dad-11d1-80b4-00c04fd430c8',
                'time' => '1d19dad6ba7b814',
                'clock_seq' => '00b4',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => '7d444840-9dc0-11d1-b245-5ffdce74fad2',
                'curly' => '{7d444840-9dc0-11d1-b245-5ffdce74fad2}',
                'hex' => '7d4448409dc011d1b2455ffdce74fad2',
                'bytes' => 'fURIQJ3AEdGyRV/9znT60g==',
                'fields' => array(
                    'time_low' => '7d444840',
                    'time_mid' => '9dc0',
                    'time_hi_and_version' => '11d1',
                    'clock_seq_hi_and_reserved' => 'b2',
                    'clock_seq_low' => '45',
                    'node' => '5ffdce74fad2',
                ),
                'urn' => 'urn:uuid:7d444840-9dc0-11d1-b245-5ffdce74fad2',
                'time' => '1d19dc07d444840',
                'clock_seq' => '3245',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => 'e902893a-9d22-3c7e-a7b8-d6e313b71d9f',
                'curly' => '{e902893a-9d22-3c7e-a7b8-d6e313b71d9f}',
                'hex' => 'e902893a9d223c7ea7b8d6e313b71d9f',
                'bytes' => '6QKJOp0iPH6nuNbjE7cdnw==',
                'fields' => array(
                    'time_low' => 'e902893a',
                    'time_mid' => '9d22',
                    'time_hi_and_version' => '3c7e',
                    'clock_seq_hi_and_reserved' => 'a7',
                    'clock_seq_low' => 'b8',
                    'node' => 'd6e313b71d9f',
                ),
                'urn' => 'urn:uuid:e902893a-9d22-3c7e-a7b8-d6e313b71d9f',
                'time' => 'c7e9d22e902893a',
                'clock_seq' => '27b8',
                'variant' => Uuid::RFC_4122,
                'version' => 3,
            ),
            array(
                'string' => 'eb424026-6f54-4ef8-a4d0-bb658a1fc6cf',
                'curly' => '{eb424026-6f54-4ef8-a4d0-bb658a1fc6cf}',
                'hex' => 'eb4240266f544ef8a4d0bb658a1fc6cf',
                'bytes' => '60JAJm9UTvik0Ltlih/Gzw==',
                'fields' => array(
                    'time_low' => 'eb424026',
                    'time_mid' => '6f54',
                    'time_hi_and_version' => '4ef8',
                    'clock_seq_hi_and_reserved' => 'a4',
                    'clock_seq_low' => 'd0',
                    'node' => 'bb658a1fc6cf',
                ),
                'urn' => 'urn:uuid:eb424026-6f54-4ef8-a4d0-bb658a1fc6cf',
                'time' => 'ef86f54eb424026',
                'clock_seq' => '24d0',
                'variant' => Uuid::RFC_4122,
                'version' => 4,
            ),
            array(
                'string' => 'f81d4fae-7dec-11d0-a765-00a0c91e6bf6',
                'curly' => '{f81d4fae-7dec-11d0-a765-00a0c91e6bf6}',
                'hex' => 'f81d4fae7dec11d0a76500a0c91e6bf6',
                'bytes' => '+B1Prn3sEdCnZQCgyR5r9g==',
                'fields' => array(
                    'time_low' => 'f81d4fae',
                    'time_mid' => '7dec',
                    'time_hi_and_version' => '11d0',
                    'clock_seq_hi_and_reserved' => 'a7',
                    'clock_seq_low' => '65',
                    'node' => '00a0c91e6bf6',
                ),
                'urn' => 'urn:uuid:f81d4fae-7dec-11d0-a765-00a0c91e6bf6',
                'time' => '1d07decf81d4fae',
                'clock_seq' => '2765',
                'variant' => Uuid::RFC_4122,
                'version' => 1,
            ),
            array(
                'string' => 'fffefdfc-fffe-fffe-fffe-fffefdfcfbfa',
                'curly' => '{fffefdfc-fffe-fffe-fffe-fffefdfcfbfa}',
                'hex' => 'fffefdfcfffefffefffefffefdfcfbfa',
                'bytes' => '//79/P/+//7//v/+/fz7+g==',
                'fields' => array(
                    'time_low' => 'fffefdfc',
                    'time_mid' => 'fffe',
                    'time_hi_and_version' => 'fffe',
                    'clock_seq_hi_and_reserved' => 'ff',
                    'clock_seq_low' => 'fe',
                    'node' => 'fffefdfcfbfa',
                ),
                'urn' => 'urn:uuid:fffefdfc-fffe-fffe-fffe-fffefdfcfbfa',
                'time' => 'ffefffefffefdfc',
                'clock_seq' => '3ffe',
                'variant' => Uuid::RESERVED_FUTURE,
                'version' => null,
            ),
            array(
                'string' => 'ffffffff-ffff-ffff-ffff-ffffffffffff',
                'curly' => '{ffffffff-ffff-ffff-ffff-ffffffffffff}',
                'hex' => 'ffffffffffffffffffffffffffffffff',
                'bytes' => '/////////////////////w==',
                'fields' => array(
                    'time_low' => 'ffffffff',
                    'time_mid' => 'ffff',
                    'time_hi_and_version' => 'ffff',
                    'clock_seq_hi_and_reserved' => 'ff',
                    'clock_seq_low' => 'ff',
                    'node' => 'ffffffffffff',
                ),
                'urn' => 'urn:uuid:ffffffff-ffff-ffff-ffff-ffffffffffff',
                'time' => 'fffffffffffffff',
                'clock_seq' => '3fff',
                'variant' => Uuid::RESERVED_FUTURE,
                'version' => null,
            ),
        );

        foreach ($tests as $test) {
            $uuids = array(
                Uuid::fromString($test['string']),
                Uuid::fromString($test['curly']),
                Uuid::fromString($test['hex']),
                Uuid::fromBytes(base64_decode($test['bytes'])),
                Uuid::fromString($test['urn']),
            );
            foreach ($uuids as $uuid) {
                $this->assertEquals($test['string'], (string) $uuid);
                $this->assertEquals(base64_decode($test['bytes']), $uuid->getBytes());
                $this->assertEquals($test['fields'], $uuid->getFieldsHex());
                $this->assertEquals($test['fields']['time_low'], $uuid->getTimeLowHex());
                $this->assertEquals($test['fields']['time_mid'], $uuid->getTimeMidHex());
                $this->assertEquals($test['fields']['time_hi_and_version'], $uuid->getTimeHiAndVersionHex());
                $this->assertEquals($test['fields']['clock_seq_hi_and_reserved'], $uuid->getClockSeqHiAndReservedHex());
                $this->assertEquals($test['fields']['clock_seq_low'], $uuid->getClockSeqLowHex());
                $this->assertEquals($test['fields']['node'], $uuid->getNodeHex());
                $this->assertEquals($test['urn'], $uuid->getUrn());
                if ($uuid->getVersion() == 1) {
                    $this->assertEquals($test['time'], $uuid->getTimestampHex());
                }
                $this->assertEquals($test['clock_seq'], $uuid->getClockSequenceHex());
                $this->assertEquals($test['variant'], $uuid->getVariant());
                $this->assertEquals($test['version'], $uuid->getVersion());
            }
        }
    }
}
