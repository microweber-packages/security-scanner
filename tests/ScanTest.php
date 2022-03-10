<?php

class ScanTest extends \PHPUnit\Framework\TestCase
{
    public function testStringScan()
    {
        $scan = (new \MicroweberPackages\SecurityScanner\Scanner)
            ->scanFile(__DIR__ . DIRECTORY_SEPARATOR . 'strange-file2.example');

        $this->assertTrue($scan['error']);
        $this->assertNotEmpty($scan['warnings']);
    }

    public function testFileScan()
    {
        $scan = (new \MicroweberPackages\SecurityScanner\Scanner)
            ->scanFile(__DIR__ . DIRECTORY_SEPARATOR . 'strange-file.example');


        var_dump($scan);
        $this->assertTrue($scan['error']);
        $this->assertNotEmpty($scan['warnings']);
    }
}
