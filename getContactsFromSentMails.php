#!/usr/bin/env php
<?php 
class crlf_filter extends php_user_filter
{
       function filter($in, $out, &$consumed, $closing)
       {
           while ($bucket = stream_bucket_make_writeable($in)) {
               // make sure the line endings aren't already CRLF
               $bucket->data = preg_replace("/(?<!\r)\n/", "\r\n", $bucket->data);
               $consumed += $bucket->datalen;
               stream_bucket_append($out, $bucket);
           }
           return PSFS_PASS_ON;
       }
}
stream_filter_register('crlf', 'crlf_filter');

function findToHeader(array $array) :?string {
    foreach($array as $row) {
        $row = trim($row);
        if (substr($row, 0, 4) == "To: " && strstr($row, 'mailto:') !== false) {
            return str_replace('mailto:', '', $row);
        }
    }
    return null;
}

function trimName(string $str) :string {
    $s = str_replace("'", '', trim($str));
    $s = str_replace('=B3', 'ł', $s);
    $s = str_replace('Å', 'ń', $s);
    $s = str_replace('³', 'ł', $s);
    $s = str_replace('ñ', 'ń', $s);
    $s = str_replace('±', 'ą', $s);
    $s = str_replace('æ', 'ć', $s);
    $s = str_replace('Ã', 'ó', $s);
    $s = str_replace('', '', $s);
    return mb_convert_encoding($s, "UTF-8"); 
}
function trimMail(string $str) :string {
    return trim(explode('>', $str)[0]);
}

function isEmail($str) :bool {
    return filter_var($str, FILTER_VALIDATE_EMAIL);
}

function conditionalAdd($item, &$array) :void {
    if ($item->mail == "" || !isEmail($item->mail)) {
        return;
    } 
    if (isset($array[$item->mail])) {
        if ($array[$item->mail] == "") {
            $array[$item->mail] = $item->name;    
        }
        if (isEmail($array[$item->mail]) && !isEmail($item->name)) {
            $array[$item->mail] = $item->name;    
        }
    } else {
        $array[$item->mail] = $item->name;
    }
}

function addMissingNames(&$people)
{
    foreach ($people as $mail => $name) {
        if (empty($name)) {
            $people[$mail] = $mail;
        }
    }
}


function mapFields($data) {
    return [
        'Tytuł' => $data['Title'] ?? '',
        'Imię' => $data['Given name'] ?? '',
        'Drugie imię' => '',
        'Nazwisko' => $data['Surname'] ?? '',
        'Sufiks' => '',
        'Firma' => '',
        'Dział' => '',
        'Stanowisko' => '',
        'Adres służbowy - ulica' => '',
        'Adres służbowy - ulica 2' => '',
        'Adres służbowy - ulica 3' => '',
        'Adres służbowy - miejscowość' => '',
        'Adres służbowy - województwo' => '',
        'Adres służbowy - kod pocztowy' => '',
        'Adres służbowy - kraj/region' => '',
        'Adres domowy - ulica' => '',
        'Adres domowy - ulica (2)' => '',
        'Adres domowy - ulica (3)' => '',
        'Adres domowy - miejscowość' => '',
        'Adres domowy - województwo' => '',
        'Adres domowy - kod pocztowy' => '',
        'Adres domowy - kraj/region' => '',
        'Inny adres - ulica' => '',
        'Inny adres - ulica 2' => '',
        'Inny adres - ulica 3' => '',
        'Inny adres - miejscowość' => '',
        'Inny adres - województwo' => '',
        'Inny adres - kod pocztowy' => '',
        'Inny adres - kraj/region' => '',
        'Telefon asystenta' => '',
        'Faks służbowy' => '',
        'Telefon służbowy' => '',
        'Telefon służbowy 2' => '',
        'Wywołanie zwrotne' => '',
        'Telefon w samochodzie' => '',
        'Główny telefon do firmy' => '',
        'Faks domowy' => '',
        'Telefon domowy' => '',
        'Telefon domowy 2' => '',
        'ISDN' => '',
        'Telefon komórkowy' => $data['Mobile phone number'] ?? '',
        'Inny faks' => '',
        'Inny telefon' => '',
        'Pager' => '',
        'Telefon podstawowy' => '',
        'Radiotelefon' => '',
        'Telefon' => '',
        'TTY/TDD' => '',
        'Teleks' => '',
        'Adres e-mail' => $data['Email address 1'] ?? '',
        'Typ poczty e-mail' => '',
        'Nazwa wyświetlana e-mail' => '',
        'Adres e-mail 2' => '',
        'Rodzaj e-mail 2' => '',
        'Nazwa wyświetlana e-mail 2' => '',
        'Adres e-mail 3' => '',
        'Rodzaj e-mail 3' => '',
        'Nazwa wyświetlana e-mail 3' => '',
        'Charakter' => '',
        'Domowa skrzynka pocztowa' => '',
        'Dzieci' => '',
        'Hobby' => '',
        'Imię i nazwisko asystenta' => '',
        'Informacje rozliczeniowe' => '',
        'Inicjały' => $data['Initials'] ?? '',
        'Inna skrzynka pocztowa' => '',
        'Internetowe informacje wolny/zajęty' => '',
        'Język' => '',
        'Kategorie' => '',
        'Konto' => '',
        'Lokalizacja' => '',
        'Lokalizacja biura' => '',
        'Menedżer' => '',
        'Notatki' => '',
        'Numer ewidencyjny w organizacji' => '',
        'Osoba polecająca' => '',
        'PESEL' => '',
        'Płeć' => '',
        'Priorytet' => '',
        'Prywatne' => '',
        'Przebieg' => '',
        'Rocznica' => '',
        'Serwer katalogowy' => '',
        'Słowa kluczowe' => '',
        'Służbowa skrzynka pocztowa' => '',
        'Strona sieci Web' => '',
        'Urodziny' => '',
        'Użytkownik 1' => '',
        'Użytkownik 2' => '',
        'Użytkownik 3' => '',
        'Użytkownik 4' => '',
        'Współmałżonek' => '',
        'Zawód' => ''
    ];
}

if (!isset($argv[1])) {
    die("Path not specified".PHP_EOL);
}

$path = $argv[1];
$files = array_values(preg_grep('/^([^.])/', scandir($path)));
$people = [];

foreach ($files as $mail) {
    $fileArray = file($path.$mail);
    $str = mb_decode_mimeheader(findToHeader($fileArray));
    if ($str != null) {
        $data = explode(":", $str)[1];
        $exploded = explode("<", $data);
        if (2 == substr_count($str, '<')) {
            unset($exploded[2]);
        }
        if (2 != count($exploded)) {
            $exploded = explode("\(", $data);
        }
        if (2 != count($exploded)) {
            throw new Error("Not supported format. String: $str".PHP_EOL);
        }
        $person = new stdClass();
        $person->name = trimName($exploded[0]); 
        $person->mail = trimMail($exploded[1]); 
        conditionalAdd($person, $people);
        
    }
}
addMissingNames($people);
echo "Number of contacts: ".count($people).PHP_EOL. "Saving to file out.csv".PHP_EOL;

$outCSV = fopen("out.csv", "w");
stream_filter_append($outCSV, 'crlf');
fputcsv($outCSV, [
    'Tytuł',
    'Imię',
    'Drugie imię',
    'Nazwisko',
    'Sufiks',
    'Firma',
    'Dział',
    'Stanowisko',
    'Adres służbowy - ulica',
    'Adres służbowy - ulica 2',
    'Adres służbowy - ulica 3',
    'Adres służbowy - miejscowość',
    'Adres służbowy - województwo',
    'Adres służbowy - kod pocztowy',
    'Adres służbowy - kraj/region',
    'Adres domowy - ulica',
    'Adres domowy - ulica (2)',
    'Adres domowy - ulica (3)',
    'Adres domowy - miejscowość',
    'Adres domowy - województwo',
    'Adres domowy - kod pocztowy',
    'Adres domowy - kraj/region',
    'Inny adres - ulica',
    'Inny adres - ulica 2',
    'Inny adres - ulica 3',
    'Inny adres - miejscowość',
    'Inny adres - województwo',
    'Inny adres - kod pocztowy',
    'Inny adres - kraj/region',
    'Telefon asystenta',
    'Faks służbowy',
    'Telefon służbowy',
    'Telefon służbowy 2',
    'Wywołanie zwrotne',
    'Telefon w samochodzie',
    'Główny telefon do firmy',
    'Faks domowy',
    'Telefon domowy',
    'Telefon domowy 2',
    'ISDN',
    'Telefon komórkowy',
    'Inny faks',
    'Inny telefon',
    'Pager',
    'Telefon podstawowy',
    'Radiotelefon',
    'Telefon',
    'TTY/TDD',
    'Teleks',
    'Adres e-mail',
    'Typ poczty e-mail',
    'Nazwa wyświetlana e-mail',
    'Adres e-mail 2',
    'Rodzaj e-mail 2',
    'Nazwa wyświetlana e-mail 2',
    'Adres e-mail 3',
    'Rodzaj e-mail 3',
    'Nazwa wyświetlana e-mail 3',
    'Charakter',
    'Domowa skrzynka pocztowa',
    'Dzieci',
    'Hobby',
    'Imię i nazwisko asystenta',
    'Informacje rozliczeniowe',
    'Inicjały',
    'Inna skrzynka pocztowa',
    'Internetowe informacje wolny/zajęty',
    'Język',
    'Kategorie',
    'Konto',
    'Lokalizacja',
    'Lokalizacja biura',
    'Menedżer',
    'Notatki',
    'Numer ewidencyjny w organizacji',
    'Osoba polecająca',
    'PESEL',
    'Płeć',
    'Priorytet',
    'Prywatne',
    'Przebieg',
    'Rocznica',
    'Serwer katalogowy',
    'Słowa kluczowe',
    'Służbowa skrzynka pocztowa',
    'Strona sieci Web',
    'Urodziny',
    'Użytkownik 1',
    'Użytkownik 2',
    'Użytkownik 3',
    'Użytkownik 4',
    'Współmałżonek',
    'Zawód'
]);

$i = 1;
foreach ($people as $mail => $name) { 
    fputcsv($outCSV, mapFields([
        'Title' => '',
        'Given name' =>  $name,
        'Surname' => '',
        'Mobile phone number' => '',
        'Email address 1' => $mail,
        'Initials' => ''
    ]));
    
    echo "$i. $name <$mail>".PHP_EOL;
    $i++;
}

fclose($outCSV);