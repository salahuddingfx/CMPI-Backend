<?php

namespace App\Utils;

/**
 * BTEB Probidhan-2022 Semester-wise Subject Code Mapping
 * Maps subject code → semester for each department
 * Used to split combined format referred subjects into correct semesters
 */
class BtebSubjectSemesterMap
{
    // Subject code => [dept_code => semester_number]
    // Common subjects shared across all departments
    private static array $commonSubjects = [
        '21011' => ['semester' => 1, 'name' => 'Engineering Drawing'],
        '25711' => ['semester' => 1, 'name' => 'Bangla-I'],
        '25712' => ['semester' => 1, 'name' => 'English-I'],
        '25911' => ['semester' => 1, 'name' => 'Mathematics-I'],
        '25912' => ['semester' => 1, 'name' => 'Physics-I'],
        '25721' => ['semester' => 2, 'name' => 'Bangla-II'],
        '25722' => ['semester' => 2, 'name' => 'English-II'],
        '25812' => ['semester' => 2, 'name' => 'Physical Education & Life Skill Development'],
        '25921' => ['semester' => 2, 'name' => 'Mathematics-II'],
        '25922' => ['semester' => 2, 'name' => 'Physics-II'],
        '25913' => ['semester' => 2, 'name' => 'Chemistry'],
        '25811' => ['semester' => 3, 'name' => 'Social Science'],
        '25931' => ['semester' => 3, 'name' => 'Mathematics-III'],
        '25841' => ['semester' => 4, 'name' => 'Accounting'],
        '29041' => ['semester' => 4, 'name' => 'Environmental Studies'],
        '25851' => ['semester' => 5, 'name' => 'Principles of Marketing'],
        '25852' => ['semester' => 5, 'name' => 'Industrial Management'],
        '25831' => ['semester' => 6, 'name' => 'Business Communication'],
        '25853' => ['semester' => 7, 'name' => 'Innovation & Entrepreneurship'],
    ];

    // Department-specific subject code → semester mapping
    private static array $deptSubjects = [
        // ── Civil Technology (64) ──
        '64' => [
            '26411' => 1, '26711' => 1,
            '26421' => 2, '26811' => 2, '27011' => 2,
            '26431' => 3, '26432' => 3, '26433' => 3,
            '26441' => 4, '26442' => 4, '26443' => 4, '26444' => 4, '26445' => 4, '26446' => 4, '26521' => 4,
            '26451' => 5, '26452' => 5, '26453' => 5, '26454' => 5, '26455' => 5, '26456' => 5,
            '26461' => 6, '26462' => 6, '26463' => 6, '26464' => 6, '26662' => 6,
            '26471' => 7, '26472' => 7, '26473' => 7, '26474' => 7, '26475' => 7, '26476' => 7, '26477' => 7,
        ],
        // ── Computer Science & Technology (66) ──
        '66' => [
            '26611' => 1, '28511' => 1, '26711' => 1,
            '26811' => 2, '28521' => 2, '28522' => 2,
            '26831' => 3, '26633' => 3, '28531' => 3, '28532' => 3, '28533' => 3,
            '26841' => 4, '28541' => 4, '28542' => 4, '28543' => 4, '28544' => 4,
            '28551' => 5, '28552' => 5, '28553' => 5, '28554' => 5, '28555' => 5, '28556' => 5,
            '28561' => 6, '28562' => 6, '28563' => 6, '28564' => 6, '28565' => 6, '28566' => 6,
            '28571' => 7, '28572' => 7, '28573' => 7, '28574' => 7, '28575' => 7, '28576' => 7,
        ],
        // ── Electrical Technology (67) ──
        '67' => [
            '26711' => 1, '28511' => 1,
            '26811' => 2, '26721' => 2, '28521' => 2,
            '26731' => 3, '26732' => 3, '26833' => 3,
            '26741' => 4, '26742' => 4, '26743' => 4, '26845' => 4, '27044' => 4,
            '26751' => 5, '26752' => 5, '26753' => 5, '26754' => 5, '26853' => 5,
            '26761' => 6, '26762' => 6, '26763' => 6, '26764' => 6, '26863' => 6,
            '26771' => 7, '26772' => 7, '26773' => 7, '26774' => 7, '26775' => 7,
        ],
        // ── Electronics Technology (68) ──
        '68' => [
            '26711' => 1, '26811' => 1,
            '26811' => 2, '26821' => 2,
            '26831' => 3, '26832' => 3, '26833' => 3, '26834' => 3,
            '26841' => 4, '26842' => 4, '26843' => 4, '26844' => 4, '26845' => 4,
            '26851' => 5, '26852' => 5, '26853' => 5, '26854' => 5, '26855' => 5, '28567' => 5,
            '26861' => 6, '26862' => 6, '26863' => 6, '26864' => 6, '26865' => 6,
            '26871' => 7, '26872' => 7, '26873' => 7, '26874' => 7,
        ],
        // ── Telecommunications Technology (94) ──
        '94' => [
            '26711' => 1, '29411' => 1,
            '26811' => 2, '26721' => 2,
            '26731' => 3, '26821' => 3, '29431' => 3, '28511' => 3,
            '26741' => 4, '26845' => 4, '26667' => 4, '29441' => 4, '29442' => 4, '29443' => 4,
            '26742' => 5, '26751' => 5, '26752' => 5, '26853' => 5, '29451' => 5,
            '26761' => 6, '26763' => 6, '26764' => 6, '29462' => 6, '29463' => 6,
            '29471' => 7, '29472' => 7, '29473' => 7, '29474' => 7,
        ],
        // ── Marine Technology (79) ──
        '79' => [
            '26711' => 1,
            '26811' => 2,
            '27041' => 3, '27042' => 3, '27043' => 3,
            '27044' => 4, '27045' => 4,
            '27951' => 5, '27952' => 5, '27953' => 5, '27954' => 5,
            '27961' => 6, '27962' => 6, '27963' => 6,
            '27971' => 7, '27972' => 7, '27973' => 7, '27974' => 7, '27975' => 7,
        ],
    ];

    /**
     * Get semester number for a subject code within a department.
     * Returns null if subject not found in the department's curriculum.
     */
    public static function getSemester(string $subjectCode, string $deptCode): ?int
    {
        $code = trim(preg_replace('/\([^)]+\)/', '', $subjectCode));

        // Check department-specific subjects first
        if (isset(self::$deptSubjects[$deptCode][$code])) {
            return self::$deptSubjects[$deptCode][$code];
        }

        // Check common subjects
        if (isset(self::$commonSubjects[$code])) {
            return self::$commonSubjects[$code]['semester'];
        }

        return null;
    }

    /**
     * Get semester number from a 5-6 digit code using heuristic rules
     * when department is unknown. Uses the first 2-3 digits to guess department.
     */
    public static function getSemesterAutoDetect(string $subjectCode): ?int
    {
        $code = trim(preg_replace('/\([^)]+\)/', '', $subjectCode));

        // Common subjects
        if (isset(self::$commonSubjects[$code])) {
            return self::$commonSubjects[$code]['semester'];
        }

        // Heuristic: 264xx = Civil, 266xx = CS, 267xx = Electrical,
        // 268xx = Electronics, 270xx = Mechanical/Marine, 294xx = Telecom
        $prefix = substr($code, 0, 3);
        $fourth = $code[3] ?? '0';

        // Civil subjects: 264xx where xx = 11-77 mapped to semesters
        if ($prefix === '264' && is_numeric($fourth)) {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        // CS subjects: 285xx → sem based on 4th digit
        if (substr($code, 0, 3) === '285') {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        // Electrical subjects: 267xx → sem based on 4th digit
        if ($prefix === '267') {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        // Electronics subjects: 268xx → sem based on 4th digit
        if ($prefix === '268') {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        // Marine subjects: 270xx, 279xx
        if ($prefix === '270' || $prefix === '279') {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        // Telecom subjects: 294xx
        if ($prefix === '294') {
            $semMap = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7];
            return $semMap[$fourth] ?? null;
        }

        return null;
    }

    /**
     * Split a list of referred subject codes by semester.
     * Returns ['2nd' => ['26811'], '4th' => ['26442', '26443'], ...]
     */
    public static function splitBySemester(array $subjectCodes, string $deptCode): array
    {
        $nameToCode = [
            'Civil Technology' => '64',
            'Computer Science & Technology' => '66',
            'Electrical Technology' => '67',
            'Electronics Technology' => '68',
            'Telecommunications Technology' => '94',
            'Marine Technology' => '79',
            'Mechanical Technology' => '70',
            'Power Technology' => '71',
        ];
        $deptKey = $nameToCode[$deptCode] ?? $deptCode;

        $bySemester = [];
        foreach ($subjectCodes as $code) {
            $clean = trim(preg_replace('/\([^)]+\)/', '', $code));
            if (!preg_match('/^\d{5,6}$/', $clean)) continue;

            $sem = self::getSemester($clean, $deptKey);
            if ($sem === null) {
                $sem = self::getSemesterAutoDetect($clean);
            }
            if ($sem === null) continue;

            $suffix = match ($sem) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
            $semLabel = $sem . $suffix;
            $bySemester[$semLabel][] = $code;
        }
        return $bySemester;
    }
}
