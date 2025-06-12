<?php

namespace Nusantara\QRCode\Tests;

use PHPUnit\Framework\TestCase;
use Nusantara\QRCode\QRCode;

class QRCodeTest extends TestCase
{
    public function testGenerateQRReturnsDataUri()
    {
        $qr = new QRCode();
        $dataUri = $qr->generate("Hello World");

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }

    public function testGenerateAndSaveQRFile()
    {
        $qr = new QRCode();
        $filename = __DIR__ . '/test.png';

        $qr->generate("Test", ['file' => $filename]);
        $this->assertFileExists($filename);

        unlink($filename); // Cleanup
    }
}