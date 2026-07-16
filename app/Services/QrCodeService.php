<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function png(string $contents, int $size = 420, int $margin = 12): string
    {
        return $this->build($contents, $size, $margin)->getString();
    }

    public function dataUri(string $contents, int $size = 420, int $margin = 12): string
    {
        return $this->build($contents, $size, $margin)->getDataUri();
    }

    private function build(string $contents, int $size, int $margin): object
    {
        $builder = new Builder(
            writer: new PngWriter,
            writerOptions: [
                PngWriter::WRITER_OPTION_COMPRESSION_LEVEL => 6,
            ],
            validateResult: false,
            data: $contents,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $builder->build();
    }
}
