<?php

namespace App\Services\PrintCheck;

use Smalot\PdfParser\Parser;

/**
 * Extrae metadatos del PDF usando smalot/pdfparser (PHP puro, sin dependencias binarias).
 * Proporciona datos de contexto para mejorar el prompt enviado a la IA.
 */
class PdfMetadataService
{
    public function __construct(private readonly Parser $parser) {}

    /**
     * @return array{
     *   page_count: int,
     *   width_pt: float,
     *   height_pt: float,
     *   width_mm: float,
     *   height_mm: float,
     *   fonts: list<string>,
     *   fonts_embedded: bool,
     *   color_spaces: list<string>,
     *   has_cmyk: bool,
     *   pdf_version: string,
     * }
     */
    public function extract(string $pdfPath): array
    {
        try {
            $pdf = $this->parser->parseFile($pdfPath);

            $details = $pdf->getDetails();
            $pages   = $pdf->getPages();
            $pageCount = count($pages);

            // Dimensiones de la primera página (en puntos, 1pt = 0.352778mm)
            $widthPt  = 0.0;
            $heightPt = 0.0;

            if ($pageCount > 0) {
                $pageDetails = $pages[0]->getDetails();
                if (isset($pageDetails['MediaBox'])) {
                    $box      = $pageDetails['MediaBox'];
                    $widthPt  = (float) ($box[2] ?? 0);
                    $heightPt = (float) ($box[3] ?? 0);
                }
            }

            // Fonts (recopiladas por página)
            $fontNames = [];
            $allEmbedded = true;
            foreach ($pages as $page) {
                foreach ($page->getFonts() as $font) {
                    $name = $font->getName();
                    if ($name) {
                        $fontNames[] = $name;
                    }
                    // smalot/pdfparser no detecta embedding directamente;
                    // si no hay BaseFont en el diccionario asumimos no embebida
                    if (!$font->get('BaseFont')) {
                        $allEmbedded = false;
                    }
                }
            }

            $fontNames = array_unique(array_filter($fontNames));

            // Detección básica de espacio de color desde el texto del PDF
            $rawText    = $pdf->getText();
            $colorSpaces = $this->detectColorSpaces($rawText, $details);

            return [
                'page_count'     => $pageCount,
                'width_pt'       => $widthPt,
                'height_pt'      => $heightPt,
                'width_mm'       => round($widthPt * 0.352778, 2),
                'height_mm'      => round($heightPt * 0.352778, 2),
                'fonts'          => array_values($fontNames),
                'fonts_embedded' => $allEmbedded,
                'color_spaces'   => $colorSpaces,
                'has_cmyk'       => in_array('DeviceCMYK', $colorSpaces, true),
                'pdf_version'    => $details['PDF Version'] ?? 'unknown',
            ];
        } catch (\Throwable $e) {
            // Si el parser falla (PDF dañado o encriptado), devolvemos valores vacíos
            return [
                'page_count'     => 0,
                'width_pt'       => 0.0,
                'height_pt'      => 0.0,
                'width_mm'       => 0.0,
                'height_mm'      => 0.0,
                'fonts'          => [],
                'fonts_embedded' => false,
                'color_spaces'   => [],
                'has_cmyk'       => false,
                'pdf_version'    => 'unknown',
                'parse_error'    => $e->getMessage(),
            ];
        }
    }

    /** Detecta nombres de espacio de color presentes en el PDF. */
    private function detectColorSpaces(string $rawText, array $details): array
    {
        $spaces = [];
        $keywords = ['DeviceCMYK', 'DeviceRGB', 'DeviceGray', 'ICCBased', 'Separation', 'DeviceN'];
        foreach ($keywords as $kw) {
            if (str_contains($rawText, $kw) || str_contains(implode(' ', $details), $kw)) {
                $spaces[] = $kw;
            }
        }
        return $spaces;
    }
}
