<?php
class Test4 {
            ]);
        }

        return $chunks;
    }

    private function detectDeptFromSubjects(array $subjects, string $defaultDept): string
    {
        $dict = [
            '26411' => 'Civil Technology', '26421' => 'Civil Technology',
            '26431' => 'Civil Technology', '26432' => 'Civil Technology',
            '26433' => 'Civil Technology', '26441' => 'Civil Technology',
            '26442' => 'Civil Technology', '26443' => 'Civil Technology',
            '26444' => 'Civil Technology', '26445' => 'Civil Technology',
            '26446' => 'Civil Technology', '26451' => 'Civil Technology',
            '26452' => 'Civil Technology', '26453' => 'Civil Technology',
            '26454' => 'Civil Technology', '26455' => 'Civil Technology',
            '26456' => 'Civil Technology', '26461' => 'Civil Technology',
            '26462' => 'Civil Technology', '26463' => 'Civil Technology',
            '26464' => 'Civil Technology', '26471' => 'Civil Technology',
            '26472' => 'Civil Technology', '26473' => 'Civil Technology',
            '26474' => 'Civil Technology', '26481' => 'Civil Technology',
            '26521' => 'Civil Technology', '28863' => 'Civil Technology',
            '66421' => 'Civil Technology', '66431' => 'Civil Technology',
            '66432' => 'Civil Technology', '66433' => 'Civil Technology',
            '66434' => 'Civil Technology', '66441' => 'Civil Technology',
            '66442' => 'Civil Technology', '66443' => 'Civil Technology',
            '66444' => 'Civil Technology', '66445' => 'Civil Technology',
            '66451' => 'Civil Technology', '66452' => 'Civil Technology',
            '66453' => 'Civil Technology', '66454' => 'Civil Technology',
            '66455' => 'Civil Technology', '66456' => 'Civil Technology',
            '66461' => 'Civil Technology', '66462' => 'Civil Technology',
            '66463' => 'Civil Technology', '66464' => 'Civil Technology',
            '66465' => 'Civil Technology', '66466' => 'Civil Technology',
            '66471' => 'Civil Technology', '66472' => 'Civil Technology',
            '66473' => 'Civil Technology', '66474' => 'Civil Technology',
            '66475' => 'Civil Technology', '66481' => 'Civil Technology',
            '68873' => 'Civil Technology',
            '28511' => 'Computer Science & Technology',
            '28521' => 'Computer Science & Technology',
            '28522' => 'Computer Science & Technology',
            '28531' => 'Computer Science & Technology',
            '28532' => 'Computer Science & Technology',
            '28541' => 'Computer Science & Technology',
            '28542' => 'Computer Science & Technology',
            '28543' => 'Computer Science & Technology',
            '28544' => 'Computer Science & Technology',
            '28551' => 'Computer Science & Technology',
            '28552' => 'Computer Science & Technology',
            '28553' => 'Computer Science & Technology',
            '28554' => 'Computer Science & Technology',
            '28555' => 'Computer Science & Technology',
            '28556' => 'Computer Science & Technology',
            '28561' => 'Computer Science & Technology',
            '28562' => 'Computer Science & Technology',
            '28563' => 'Computer Science & Technology',
            '28564' => 'Computer Science & Technology',
            '28565' => 'Computer Science & Technology',
            '28566' => 'Computer Science & Technology',
            '28581' => 'Computer Science & Technology',
            '66611' => 'Computer Science & Technology',
            '66612' => 'Computer Science & Technology',
            '66621' => 'Computer Science & Technology',
            '66622' => 'Computer Science & Technology',
            '66623' => 'Computer Science & Technology',
            '66631' => 'Computer Science & Technology',
            '66632' => 'Computer Science & Technology',
            '66633' => 'Computer Science & Technology',
            '66634' => 'Computer Science & Technology',
            '66641' => 'Computer Science & Technology',
            '66642' => 'Computer Science & Technology',
            '66643' => 'Computer Science & Technology',
            '66644' => 'Computer Science & Technology',
            '66645' => 'Computer Science & Technology',
            '66651' => 'Computer Science & Technology',
            '66652' => 'Computer Science & Technology',
            '66653' => 'Computer Science & Technology',
            '66654' => 'Computer Science & Technology',
            '66655' => 'Computer Science & Technology',
            '68546' => 'Computer Science & Technology',
            '66661' => 'Computer Science & Technology',
            '66662' => 'Computer Science & Technology',
            '66663' => 'Computer Science & Technology',
            '66664' => 'Computer Science & Technology',
            '66665' => 'Computer Science & Technology',
            '66666' => 'Computer Science & Technology',
            '66667' => 'Computer Science & Technology',
            '66668' => 'Computer Science & Technology',
            '66671' => 'Computer Science & Technology',
            '66672' => 'Computer Science & Technology',
            '66673' => 'Computer Science & Technology',
            '66674' => 'Computer Science & Technology',
            '66675' => 'Computer Science & Technology',
            '66677' => 'Computer Science & Technology',
            '66681' => 'Computer Science & Technology',
            '26711' => 'Electrical Technology', '26712' => 'Electrical Technology',
            '26721' => 'Electrical Technology', '26722' => 'Electrical Technology',
            '26731' => 'Electrical Technology', '26732' => 'Electrical Technology',
            '26741' => 'Electrical Technology', '26742' => 'Electrical Technology',
            '26743' => 'Electrical Technology', '26751' => 'Electrical Technology',
            '26752' => 'Electrical Technology', '26753' => 'Electrical Technology',
            '26754' => 'Electrical Technology', '26761' => 'Electrical Technology',
            '26763' => 'Electrical Technology', '26811' => 'Electrical Technology',
            '26833' => 'Electrical Technology', '26842' => 'Electrical Technology',
            '26845' => 'Electrical Technology', '26853' => 'Electrical Technology',
            '66711' => 'Electrical Technology', '66712' => 'Electrical Technology',
            '66713' => 'Electrical Technology', '66721' => 'Electrical Technology',
            '66722' => 'Electrical Technology', '66731' => 'Electrical Technology',
            '66732' => 'Electrical Technology', '66733' => 'Electrical Technology',
            '66741' => 'Electrical Technology', '66742' => 'Electrical Technology',
            '66751' => 'Electrical Technology', '66752' => 'Electrical Technology',
            '66753' => 'Electrical Technology', '66761' => 'Electrical Technology',
            '66762' => 'Electrical Technology', '66763' => 'Electrical Technology',
            '66771' => 'Electrical Technology', '66772' => 'Electrical Technology',
            '66773' => 'Electrical Technology', '66774' => 'Electrical Technology',
            '66775' => 'Electrical Technology', '66781' => 'Electrical Technology',
            '66811' => 'Electrical Technology', '66845' => 'Electrical Technology',
            '66823' => 'Electrical Technology', '66842' => 'Electrical Technology',
            '66856' => 'Electrical Technology', '66863' => 'Electrical Technology',
            '66867' => 'Electrical Technology', '66868' => 'Electrical Technology',
            '66841' => 'Electronics Technology', '66843' => 'Electronics Technology',
            '66851' => 'Electronics Technology', '66852' => 'Electronics Technology',
            '66853' => 'Electronics Technology', '66854' => 'Electronics Technology',
            '66855' => 'Electronics Technology', '66861' => 'Electronics Technology',
            '66862' => 'Electronics Technology', '66864' => 'Electronics Technology',
            '66865' => 'Electronics Technology', '66871' => 'Electronics Technology',
            '66872' => 'Electronics Technology', '66873' => 'Electronics Technology',
            '66874' => 'Electronics Technology', '66881' => 'Electronics Technology',
            '68643' => 'Electronics Technology', '68661' => 'Electronics Technology',
            '67041' => 'Telecommunications Technology',
            '67051' => 'Telecommunications Technology',
            '67061' => 'Telecommunications Technology',
            '67062' => 'Telecommunications Technology',
            '67064' => 'Telecommunications Technology',
            '67071' => 'Telecommunications Technology',
            '67072' => 'Telecommunications Technology',
            '67073' => 'Telecommunications Technology',
            '67141' => 'Telecommunications Technology',
            '67151' => 'Telecommunications Technology',
            '67171' => 'Telecommunications Technology',
        ];

        $counts = [];
        foreach ($subjects as $subj) {
            $code = trim(preg_replace('/\([^)]+\)/', '', $subj) ?? '');
            $dept = $dict[$code] ?? null;
            if ($dept !== null) {
                $counts[$dept] = ($counts[$dept] ?? 0) + 1;
            }
        }

        if (empty($counts)) {
            return $defaultDept;
        }

        arsort($counts);
        return (string) array_key_first($counts);
    }
}

}