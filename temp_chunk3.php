<?php
class Test3 {
    private function splitBySemesterHeader(string $text, string $defaultSemester): array
    {
        $headerPattern = '/(?:^|\n)\s*(?:(\d)(?:st|nd|rd|th)\s*(?:Semester|Sem\.?)|(?:Semester|Sem\.?)\s*(\d)|(?:SEM)\s*[-–—]\s*(I{1,3}V?|IX|V?I{0,3}))\b/i';

        $matches = [];
        preg_match_all($headerPattern, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if (empty($matches)) {
            return [['semester' => $defaultSemester, 'text' => $text]];
        }

        $chunks = [];
        for ($i = 0; $i < count($matches); $i++) {
            $semNumber = $matches[$i][1] ?? $matches[$i][2] ?? null;
            $semLabel = $matches[$i][3] ?? null;

            if ($semNumber === null && $semLabel !== null) {
                $romanMap = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5', 'VI' => '6', 'VII' => '7', 'VIII' => '8'];
                $semNumber = $romanMap[strtoupper($semLabel)] ?? null;
            }
            if ($semNumber === null) continue;

            $num = (int)$semNumber;
            $suffix = match ($num) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
            $detectedSem = $num . $suffix;

            $startOffset = $matches[$i][0][1];
            $endOffset = ($i + 1 < count($matches)) ? $matches[$i + 1][0][1] : strlen($text);

            $chunks[] = [
                'semester' => $detectedSem,
                'text' => substr($text, $startOffset, $endOffset - $startOffset)
            ];
        }

        if (empty($chunks)) {
            return [['semester' => $defaultSemester, 'text' => $text]];
        }

        $firstHeaderOffset = $matches[0][0][1];
        if ($firstHeaderOffset > 0) {
            array_unshift($chunks, [
                'semester' => $defaultSemester,
                'text' => substr($text, 0, $firstHeaderOffset)
            ]);
        }

        return $chunks;
    }

}