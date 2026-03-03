<?php

namespace App\Services\Contracts;

use Carbon\Carbon;

class PdfContractDataExtractor
{
    /**
     * @return array<string, mixed>
     */
    public function extract(string $pdfBinary): array
    {
        $text = $this->extractText($pdfBinary);

        $data = [
            'full_name' => $this->firstMatch('/und\s+Frau\/Herrn\s+(.+?)\s+als\s+Teilnehmer/ui', $text),
            'course' => $this->firstMatch('/Lehrgang\s+(.+?)(?:\n|$)/ui', $text),
            'location' => $this->firstMatch('/Ort\s+(.+?)(?:\n|$)/ui', $text),
            'measure_time' => $this->firstMatch('/Maßnahmezeit\s+(.+?)(?:\n|$)/ui', $text),
            'street' => $this->firstMatch('/Straße,\s*Hausnummer:\s*(.+?)(?:\n|$)/ui', $text),
            'telephone' => $this->firstMatch('/Tel\.:\s*(.+?)(?:\n|$)/ui', $text),
            'cost_bearer' => $this->firstMatch('/Leistungsträger\s+(.+?)(?:\n|$)/ui', $text),
        ];

        if (preg_match('/Beginn:\s*(\d{2}\.\d{2}\.\d{4})\s+Ende:\s*(\d{2}\.\d{2}\.\d{4})/u', $text, $matches) === 1) {
            $data['measure_start'] = $this->parseGermanDate($matches[1]);
            $data['measure_end'] = $this->parseGermanDate($matches[2]);
        }

        if (preg_match('/Geburtsdatum:\s*(\d{2}\.\d{2}\.\d{4})/u', $text, $matches) === 1) {
            $data['birthday'] = $this->parseGermanDate($matches[1]);
        }

        if (preg_match('/PLZ\s*,\s*Ort:\s*(\d{4,5})\s+(.+?)(?:\n|$)/ui', $text, $matches) === 1) {
            $data['zip'] = trim($matches[1]);
            $data['city_name'] = trim($matches[2]);
        }

        if (!empty($data['full_name'])) {
            [$firstname, $name] = $this->splitName($data['full_name']);
            $data['firstname'] = $firstname;
            $data['name'] = $name;
        }

        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    private function extractText(string $pdfBinary): string
    {
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfBinary, $matches);
        $chunks = [];

        foreach ($matches[1] as $rawStream) {
            $decoded = $this->decodeStream($rawStream);
            if ($decoded === null) {
                continue;
            }

            $chunks[] = $this->extractTextOperators($decoded);
        }

        if ($chunks === []) {
            $chunks[] = $pdfBinary;
        }

        return $this->normalizeText(implode("\n", $chunks));
    }

    private function decodeStream(string $rawStream): ?string
    {
        $stream = ltrim($rawStream, "\r\n");
        $stream = rtrim($stream, "\r\n");

        $inflated = @gzuncompress($stream);
        if ($inflated !== false) {
            return $inflated;
        }

        $inflated = @gzdecode($stream);
        if ($inflated !== false) {
            return $inflated;
        }

        $inflated = @gzinflate($stream);
        if ($inflated !== false) {
            return $inflated;
        }

        if (preg_match('//u', $stream) === 1) {
            return $stream;
        }

        return null;
    }

    private function extractTextOperators(string $content): string
    {
        $parts = [];

        if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj/s', $content, $tjMatches) === 1 || count($tjMatches[0]) > 0) {
            foreach ($tjMatches[0] as $token) {
                $parts[] = $this->decodeLiteralToken($token);
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $tjArrayMatches) === 1 || count($tjArrayMatches[1]) > 0) {
            foreach ($tjArrayMatches[1] as $arrayContent) {
                if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)|<([0-9A-Fa-f]+)>/s', $arrayContent, $stringTokens, PREG_SET_ORDER) === 1 || count($stringTokens) > 0) {
                    foreach ($stringTokens as $token) {
                        if (!empty($token[1])) {
                            $parts[] = $this->decodeHexString($token[1]);
                            continue;
                        }

                        $parts[] = $this->decodeLiteralToken($token[0]);
                    }
                }
                $parts[] = "\n";
            }
        }

        if (preg_match_all('/<([0-9A-Fa-f]+)>\s*Tj/s', $content, $hexMatches) === 1 || count($hexMatches[1]) > 0) {
            foreach ($hexMatches[1] as $hex) {
                $parts[] = $this->decodeHexString($hex);
            }
        }

        return implode('', $parts);
    }

    private function decodeLiteralToken(string $token): string
    {
        $trimmed = trim($token);
        if (str_starts_with($trimmed, '(')) {
            $trimmed = substr($trimmed, 1);
        }
        if (str_ends_with($trimmed, ') Tj')) {
            $trimmed = substr($trimmed, 0, -4);
        }
        if (str_ends_with($trimmed, ')')) {
            $trimmed = substr($trimmed, 0, -1);
        }

        $decoded = str_replace(
            ['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t'],
            ['(', ')', '\\', "\n", "\n", "\t"],
            $trimmed
        );

        return $decoded;
    }

    private function decodeHexString(string $hex): string
    {
        if (strlen($hex) % 2 !== 0) {
            $hex .= '0';
        }

        $binary = @hex2bin($hex);
        if ($binary === false) {
            return '';
        }

        if (str_starts_with($binary, "\xFE\xFF") || str_starts_with($binary, "\xFF\xFE")) {
            $converted = @mb_convert_encoding($binary, 'UTF-8', 'UTF-16');
            return $converted === false ? '' : $converted;
        }

        return $binary;
    }

    private function normalizeText(string $text): string
    {
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s*\n\s*/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{2,}/u', "\n", $text) ?? $text;

        return trim($text);
    }

    private function firstMatch(string $pattern, string $text): ?string
    {
        if (preg_match($pattern, $text, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    private function parseGermanDate(string $value): ?string
    {
        try {
            return Carbon::createFromFormat('d.m.Y', trim($value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function splitName(string $fullName): array
    {
        $name = trim($fullName);
        if ($name === '') {
            return [null, null];
        }

        $segments = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (!$segments) {
            return [null, null];
        }

        $firstname = array_shift($segments);
        $lastname = implode(' ', $segments);

        return [$firstname, $lastname !== '' ? $lastname : null];
    }
}
