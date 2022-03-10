<?php

class ScanTest extends \PHPUnit\Framework\TestCase
{
    public function testFileScan()
    {
        $scan = (new \MicroweberPackages\SecurityScanner\Scanner)
            ->file(__DIR__ . DIRECTORY_SEPARATOR . 'strange-file.php')
            ->run();

        $this->assertTrue($scan['error']);
        $this->assertNotEmpty($scan['warnings']);
    }
}
