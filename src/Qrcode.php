<?php
namespace Nusantara\QRCode;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WebPWriter;
class QRCode {
    /**
     * Generate a QR Code as a string (base64 encoded) or save to a file.
     *
     * @param string $data The data to encode in the QR Code.
     * @param array $options Configuration options for the QR Code.
     * - size (int): Size of the QR code in pixels (default: 300).
     * - margin (int): Margin around the QR code (default: 10).
     * - format (string): Output format ('png', 'svg', 'webp', 'base64') (default: 'png').
     * - label (string): Optional text label below the QR code.
     * - label_font_size (int): Font size for the label (default: 16).
     * - logo_path (string): Optional path to a logo image file.
     * - logo_width (int): Width of the logo (default: 50).
     * - logo_height (int): Height of the logo (default: 50).
     * - error_correction_level (string): 'low', 'medium', 'quartile', 'high' (default: 'high').
     *
     * @return string The QR Code image data (binary for png/webp, XML for svg, base64 string for 'base64').
     * @throws \Exception
     */
    public function generate(string $data, array $options = []): string
    {
        $builder = Builder::create()
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High); // Default ke High
        
        // --- Konfigurasi Ukuran dan Margin ---
        $builder->size($options['size'] ?? 300)
                ->margin($options['margin'] ?? 10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin); // Opsional, bisa diatur

        // --- Konfigurasi Error Correction Level ---
        if (isset($options['error_correction_level'])) {
            $level = strtolower($options['error_correction_level']);
            switch ($level) {
                case 'low': $builder->errorCorrectionLevel(ErrorCorrectionLevel::Low); break;
                case 'medium': $builder->errorCorrectionLevel(ErrorCorrectionLevel::Medium); break;
                case 'quartile': $builder->errorCorrectionLevel(ErrorCorrectionLevel::Quartile); break;
                case 'high': default: $builder->errorCorrectionLevel(ErrorCorrectionLevel::High); break;
            }
        }

        // --- Konfigurasi Label ---
        if (isset($options['label']) && !empty($options['label'])) {
            $builder->label(Label::create($options['label'])
                ->setFontSize($options['label_font_size'] ?? 16)
                // ->setTextColor(new Color(255, 0, 0)) // Contoh: warna teks label
            );
        }

        // --- Konfigurasi Logo ---
        if (isset($options['logo_path']) && file_exists($options['logo_path'])) {
            $builder->logo(Logo::create($options['logo_path'])
                ->setResizeToWidth($options['logo_width'] ?? 50)
                ->setResizeToHeight($options['logo_height'] ?? null) // Atur null jika ingin mempertahankan aspek rasio
            );
        }

        // --- Pilih Writer berdasarkan Format ---
        $format = strtolower($options['format'] ?? 'png');
        switch ($format) {
            case 'svg':
                $builder->writer(new \Endroid\QrCode\Writer\SvgWriter());
                $result = $builder->generate();
                return $result->getString(); // SVG adalah XML string
            case 'webp':
                $builder->writer(new WebPWriter());
                $result = $builder->generate();
                return $result->getString(); // Binary data
            case 'base64':
                $builder->writer(new PngWriter()); // Biasanya base64 dari PNG
                $result = $builder->generate();
                return $result->getDataUri(); // Base64 Data URI
            case 'png':
            default:
                $builder->writer(new PngWriter());
                $result = $builder->generate();
                return $result->getString(); // Binary data
        }
    }

    /**
     * Save the generated QR Code to a file.
     *
     * @param string $data The data to encode.
     * @param string $filePath The full path to save the QR Code (e.g., 'path/to/qr.png').
     * @param array $options Configuration options (same as generate method).
     * @return bool True on success, false on failure.
     */
    public function save(string $data, string $filePath, array $options = []): bool
    {
        try {
            $format = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $options['format'] = $format; // Pastikan format sesuai ekstensi file
            
            $builder = Builder::create()
                ->data($data)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh());
            
            // Re-apply common options, can refactor to a private buildBuilder method
            $builder->size($options['size'] ?? 300)
                    ->margin($options['margin'] ?? 10)
                    ->roundBlockSizeMode(new RoundBlockSizeModeMargin());

            if (isset($options['error_correction_level'])) {
                $level = strtolower($options['error_correction_level']);
                switch ($level) {
                    case 'low': $builder->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow()); break;
                    case 'medium': $builder->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium()); break;
                    case 'quartile': $builder->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile()); break;
                    case 'high': default: $builder->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh()); break;
                }
            }

            if (isset($options['label']) && !empty($options['label'])) {
                $builder->label(Label::create($options['label'])
                    ->setFontSize($options['label_font_size'] ?? 16)
                );
            }

            if (isset($options['logo_path']) && file_exists($options['logo_path'])) {
                $builder->logo(Logo::create($options['logo_path'])
                    ->setResizeToWidth($options['logo_width'] ?? 50)
                    ->setResizeToHeight($options['logo_height'] ?? null)
                );
            }

            // Select writer based on target file extension
            switch ($format) {
                case 'svg': $builder->writer(new \Endroid\QrCode\Writer\SvgWriter()); break;
                case 'webp': $builder->writer(new WebPWriter()); break;
                case 'png': default: $builder->writer(new PngWriter()); break;
            }

            $result = $builder->generate();
            $result->saveToFile($filePath);
            return true;
        } catch (\Exception $e) {
            // Anda bisa log error di sini
            error_log('Error saving QR Code: ' . $e->getMessage());
            return false;
        }
    }
}