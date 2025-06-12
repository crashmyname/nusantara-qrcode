<?php
namespace Nusantara;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Writer\PngWriter;

class QRCode
{
    public function generate(
        string $data,
        string $filePath = null,
        int $size = 300,
        string $label = ''
    ): string {
        $builder = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

        if ($label !== '') {
            $builder->labelText($label)
                ->labelFont(new NotoSans(16))
                ->labelAlignment(LabelAlignment::Center);
        }

        $result = $builder->build();

        if ($filePath !== null) {
            $result->saveToFile($filePath);
            return $filePath;
        }

        return $result->getDataUri();
    }
}