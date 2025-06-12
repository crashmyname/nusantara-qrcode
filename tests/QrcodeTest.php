<?php

namespace Nusantara\QRCode\Tests;

use PHPUnit\Framework\TestCase;
use Nusantara\QRCode\QRCode;

class QRCodeGeneratorTest extends TestCase
{
    protected $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/qrcode_tests_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testGeneratePngQRCode()
    {
        $generator = new QRCode();
        $data = "https://example.com/test-png";
        $options = ['size' => 200, 'format' => 'png'];
        $qrCodeData = $generator->generate($data, $options);

        $this->assertIsString($qrCodeData);
        $this->assertStringStartsWith("\x89PNG", $qrCodeData); // Cek magic number PNG
    }

    public function testGenerateSvgQRCode()
    {
        $generator = new QRCode();
        $data = "https://example.com/test-svg";
        $options = ['size' => 200, 'format' => 'svg'];
        $qrCodeData = $generator->generate($data, $options);

        $this->assertIsString($qrCodeData);
        $this->assertStringContainsString('<svg', $qrCodeData); // Cek tag SVG
    }

    public function testGenerateBase64QRCode()
    {
        $generator = new QRCode();
        $data = "https://example.com/test-base64";
        $options = ['size' => 200, 'format' => 'base64'];
        $qrCodeData = $generator->generate($data, $options);

        $this->assertIsString($qrCodeData);
        $this->assertStringStartsWith('data:image/png;base64,', $qrCodeData); // Cek format Data URI
    }

    public function testSavePngQRCodeToFile()
    {
        $generator = new QRCode();
        $data = "https://example.com/test-save";
        $filePath = $this->tempDir . '/test_qr.png';
        $options = ['size' => 150];

        $result = $generator->save($data, $filePath, $options);

        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        $this->assertGreaterThan(0, filesize($filePath));
        $this->assertStringStartsWith("\x89PNG", file_get_contents($filePath)); // Cek magic number PNG
    }

    public function testGenerateWithLabel()
    {
        $generator = new QRCode();
        $data = "Hello World";
        $options = ['label' => 'My QR Code', 'format' => 'png', 'label_font_size' => 12];
        $qrCodeData = $generator->generate($data, $options);
        // Sulit untuk menguji label secara langsung tanpa parsing gambar,
        // Cukup pastikan tidak ada error dan QR code terbentuk.
        $this->assertIsString($qrCodeData);
    }

    public function testGenerateWithLogo()
    {
        $generator = new QRCode();
        $data = "https://example.com/logo-test";
        $logoPath = __DIR__ . '/_files/logo.png'; // Buat folder _files dan letakkan gambar logo di sana
        
        // Buat file logo dummy jika tidak ada
        if (!is_dir(__DIR__ . '/_files')) {
            mkdir(__DIR__ . '/_files');
        }
        if (!file_exists($logoPath)) {
            // Buat gambar PNG 1x1 piksel putih sebagai dummy logo
            $im = imagecreatetruecolor(1, 1);
            imagecolorallocate($im, 255, 255, 255); // White
            imagepng($im, $logoPath);
            imagedestroy($im);
        }

        $options = ['logo_path' => $logoPath, 'logo_width' => 60, 'format' => 'png'];
        $qrCodeData = $generator->generate($data, $options);
        
        $this->assertIsString($qrCodeData);
        // Bersihkan logo dummy setelah test
        unlink($logoPath);
        rmdir(__DIR__ . '/_files');
    }
}