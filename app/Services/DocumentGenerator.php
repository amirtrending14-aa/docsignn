<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class DocumentGenerator
{
    public function generate(array $structure, string $baseName): array
    {
        $paths = [
            'docx' => null,
            'pdf' => null,
        ];

        try {
            $paths['docx'] = $this->generateDocx($structure, $baseName);
        } catch (\Throwable $e) {
            Log::error('Docx generation failed', [
                'error' => $e->getMessage(),
                'structure' => $structure,
            ]);
        }

        try {
            $paths['pdf'] = $this->generatePdf($structure, $baseName);
        } catch (\Throwable $e) {
            Log::error('Pdf generation failed', [
                'error' => $e->getMessage(),
                'structure' => $structure,
            ]);
        }

        return $paths;
    }

    private function generateDocx(array $structure, string $baseName): ?string
    {
        if (!class_exists(PhpWord::class)) {
            Log::warning('PhpWord не установлен');
            return null;
        }

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => 1418,
            'marginRight' => 1418,
            'marginTop' => 1134,
            'marginBottom' => 1134,
        ]);

        $title = $structure['title'] ?? 'Документ';
        $section->addTitle($title, 1);
        $section->addTextBreak(1);

        if (!empty($structure['header']) && is_array($structure['header'])) {
            foreach ($structure['header'] as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $section->addText(
                        $line,
                        ['name' => 'Times New Roman', 'size' => 11, 'bold' => true],
                        ['alignment' => 'left', 'spaceAfter' => 60]
                    );
                }
            }
            $section->addTextBreak(1);
        }

        if (!empty($structure['body']) && is_array($structure['body'])) {
            foreach ($structure['body'] as $paragraph) {
                if (is_string($paragraph)) {
                    if (trim($paragraph) === '') {
                        $section->addTextBreak(1);
                    } else {
                        $section->addText(
                            $paragraph,
                            ['name' => 'Times New Roman', 'size' => 11],
                            ['alignment' => 'both', 'spaceAfter' => 120]
                        );
                    }
                }
            }
        }

        if (!empty($structure['signature']) && is_array($structure['signature'])) {
            $section->addTextBreak(2);
            foreach ($structure['signature'] as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $section->addText(
                        $line,
                        ['name' => 'Times New Roman', 'size' => 11],
                        ['alignment' => 'right', 'spaceAfter' => 60]
                    );
                }
            }
        }

        $relativePath = 'documents' . DIRECTORY_SEPARATOR . 'ai-chat' . DIRECTORY_SEPARATOR . $baseName . '.docx';
        $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . $relativePath);
        
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        Log::info('Docx создан', ['path' => $fullPath]);
        return $fullPath;
    }

    private function generatePdf(array $structure, string $baseName): ?string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            Log::warning('DomPDF не установлен');
            return null;
        }

        $html = $this->buildHtml($structure);

        $relativePath = 'documents' . DIRECTORY_SEPARATOR . 'ai-chat' . DIRECTORY_SEPARATOR . $baseName . '.pdf';
        $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . $relativePath);
        
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('isFontSubsettingEnabled', false);
        
        $pdf->save($fullPath);

        Log::info('Pdf создан', ['path' => $fullPath]);
        return $fullPath;
    }

    private function buildHtml(array $structure): string
    {
        // ✅ КРИТИЧЕСКИ ВАЖНО: @font-face + правильный font-family + meta charset UTF-8
        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@font-face {
    font-family: "DejaVu Sans";
    font-style: normal;
    font-weight: normal;
    src: url("vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf");
}
@font-face {
    font-family: "DejaVu Sans";
    font-style: normal;
    font-weight: bold;
    src: url("vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf");
}
@font-face {
    font-family: "DejaVu Sans";
    font-style: italic;
    font-weight: normal;
    src: url("vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Oblique.ttf");
}
@font-face {
    font-family: "DejaVu Sans";
    font-style: italic;
    font-weight: bold;
    src: url("vendor/dompdf/dompdf/lib/fonts/DejaVuSans-BoldOblique.ttf");
}

@page { 
    margin: 40px; 
}

* {
    font-family: "DejaVu Sans", sans-serif !important;
}

body { 
    font-family: "DejaVu Sans", sans-serif !important;
    font-size: 11pt; 
    line-height: 1.6; 
    margin: 40px;
}

h1 { 
    text-align: center; 
    font-size: 16pt; 
    margin-bottom: 20px;
    font-family: "DejaVu Sans", sans-serif !important;
}

.header { margin-bottom: 20px; }
.header p { 
    margin: 3px 0; 
    font-weight: bold;
    font-family: "DejaVu Sans", sans-serif !important;
}

.body { text-align: justify; }
.body p { 
    margin: 8px 0;
    font-family: "DejaVu Sans", sans-serif !important;
}

.signature { text-align: right; margin-top: 30px; }
.signature p { 
    margin: 3px 0;
    font-family: "DejaVu Sans", sans-serif !important;
}
</style>
</head>
<body>';

        $title = htmlspecialchars($structure['title'] ?? 'Документ', ENT_QUOTES, 'UTF-8');
        $html .= "<h1>{$title}</h1>";

        if (!empty($structure['header'])) {
            $html .= '<div class="header">';
            foreach ($structure['header'] as $line) {
                $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            $html .= '</div>';
        }

        if (!empty($structure['body'])) {
            $html .= '<div class="body">';
            foreach ($structure['body'] as $paragraph) {
                if (trim($paragraph) === '') {
                    $html .= '<p>&nbsp;</p>';
                } else {
                    $html .= '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) . '</p>';
                }
            }
            $html .= '</div>';
        }

        if (!empty($structure['signature'])) {
            $html .= '<div class="signature">';
            foreach ($structure['signature'] as $line) {
                $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            $html .= '</div>';
        }

        $html .= '</body></html>';
        return $html;
    }
}