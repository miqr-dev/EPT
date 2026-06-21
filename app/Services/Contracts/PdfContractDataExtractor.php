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
            'full_name' => $this->firstMatch('/(?:und\s+)?Frau\/Herrn?\s+(.+?)(?:\s+als\s+Teilnehmer|\n|$)/ui', $text),
            'sex' => $this->determineSex($text),
            'course' => $this->firstMatch('/Lehrgang\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
            'location' => $this->firstMatch('/Ort\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
            'measure_time' => $this->firstMatch('/Ma(?:ß|ss)nahmezeit\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
            'street' => $this->firstMatch('/Stra(?:ß|ss)e,\s*Hausnummer\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
            'telephone' => $this->firstMatch('/Tel\.?\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
            'cost_bearer' => $this->firstMatch('/Leistungstr(?:ä|ae)ger\s*:?[ \t]*(.+?)(?:\n|$)/ui', $text),
        ];

        if (preg_match('/Beginn\s*:?[ \t]*(\d{2}\.\d{2}\.\d{4})\s+Ende\s*:?[ \t]*(\d{2}\.\d{2}\.\d{4})/u', $text, $matches) === 1) {
            $data['measure_start'] = $this->parseGermanDate($matches[1]);
            $data['measure_end'] = $this->parseGermanDate($matches[2]);
        }

        if (preg_match('/Geburtsdatum\s*:?[ \t]*(\d{2}\.\d{2}\.\d{4})/u', $text, $matches) === 1) {
            $data['birthday'] = $this->parseGermanDate($matches[1]);
        }

        if (preg_match('/PLZ\s*,\s*Ort\s*:?[ \t]*(\d{4,5})\s+(.+?)(?:\n|$)/ui', $text, $matches) === 1) {
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

        foreach (['gzuncompress', 'gzdecode', 'gzinflate'] as $decoder) {
            $inflated = @$decoder($stream);
            if ($inflated !== false) {
                return $inflated;
            }
        }

        $ascii85 = $this->decodeAscii85($stream);
        if ($ascii85 !== null) {
            foreach (['gzuncompress', 'gzdecode', 'gzinflate'] as $decoder) {
                $inflated = @$decoder($ascii85);
                if ($inflated !== false) {
                    return $inflated;
                }
            }
            return $ascii85;
        }

        if (preg_match('//u', $stream) === 1) {
            return $stream;
        }

        return @mb_convert_encoding($stream, 'UTF-8', 'Windows-1252');
    }

    private function decodeAscii85(string $stream): ?string
    {
        $trimmed = trim($stream);
        if (!str_contains($trimmed, '~>')) {
            return null;
        }

        $trimmed = preg_replace('/^<~/', '', $trimmed) ?? $trimmed;
        $trimmed = preg_replace('/~>$/', '', $trimmed) ?? $trimmed;
        $trimmed = preg_replace('/\s+/', '', $trimmed) ?? $trimmed;

        if ($trimmed === '') {
            return null;
        }

        $out = '';
        $group = '';

        foreach (str_split($trimmed) as $char) {
            if ($char === 'z' && $group === '') {
                $out .= "\x00\x00\x00\x00";
                continue;
            }

            $group .= $char;
            if (strlen($group) < 5) {
                continue;
            }

            $value = 0;
            foreach (str_split($group) as $c) {
                $ord = ord($c);
                if ($ord < 33 || $ord > 117) {
                    return null;
                }
                $value = $value * 85 + ($ord - 33);
            }

            $out .= pack('N', $value);
            $group = '';
        }

        if ($group !== '') {
            $padding = 5 - strlen($group);
            $group = str_pad($group, 5, 'u');
            $value = 0;

            foreach (str_split($group) as $c) {
                $ord = ord($c);
                if ($ord < 33 || $ord > 117) {
                    return null;
                }
                $value = $value * 85 + ($ord - 33);
            }

            $out .= substr(pack('N', $value), 0, 4 - $padding);
        }

        return $out;
    }

    private function extractTextOperators(string $content): string
    {
        $parts = [];

        if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj/s', $content, $tjMatches) > 0) {
            foreach ($tjMatches[0] as $token) {
                $parts[] = $this->decodeLiteralToken($token);
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $tjArrayMatches) > 0) {
            foreach ($tjArrayMatches[1] as $arrayContent) {
                if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)|<([0-9A-Fa-f]+)>/s', $arrayContent, $stringTokens, PREG_SET_ORDER) > 0) {
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

        if (preg_match_all('/<([0-9A-Fa-f]+)>\s*Tj/s', $content, $hexMatches) > 0) {
            foreach ($hexMatches[1] as $hex) {
                $parts[] = $this->decodeHexString($hex);
            }
        }

        if ($parts === [] && preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $content, $allLiterals) > 0) {
            foreach ($allLiterals[0] as $literal) {
                $parts[] = $this->decodeLiteralToken($literal);
                $parts[] = "\n";
            }
        }

        return implode('', $parts);
    }

    private function decodeLiteralToken(string $token): string
    {
        $trimmed = trim($token);
        $trimmed = preg_replace('/\)\s*Tj$/', ')', $trimmed) ?? $trimmed;

        if (str_starts_with($trimmed, '(')) {
            $trimmed = substr($trimmed, 1);
        }
        if (str_ends_with($trimmed, ')')) {
            $trimmed = substr($trimmed, 0, -1);
        }

        $decoded = preg_replace_callback('/\\([0-7]{1,3})/', function (array $m): string {
            return chr(octdec($m[1]));
        }, $trimmed) ?? $trimmed;

        $decoded = str_replace(
            ['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t', '\\f'],
            ['(', ')', '\\', "\n", "\n", "\t", ''],
            $decoded
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

        if (preg_match('//u', $binary) === 1) {
            return $binary;
        }

        return @mb_convert_encoding($binary, 'UTF-8', 'Windows-1252') ?: '';
    }


    private function determineSex(string $text): ?string
    {
        if (preg_match('/\bund\s+Frau\b/ui', $text) === 1 || preg_match('/\bTeilnehmerin\b/ui', $text) === 1) {
            return 'w';
        }

        if (preg_match('/\bund\s+Herrn?\b/ui', $text) === 1 || preg_match('/\bTeilnehmer\b/ui', $text) === 1) {
            return 'm';
        }

        return null;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t\x{00A0}]+/u', ' ', $text) ?? $text;
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
