<?php
/**
Drop the file into contacts directory, the one with ContactXXXXX directories, and run it. It should create output file named out.csv. Header of CSV file is in Polish language. 

### It DOES NOT process every exported deatil. Currently works with:
  - title
  - name
  - surname
  - mobile phone number
  - first email address
  - initials

You need to chceck if encoding of CSV file is the same as one used in Windows/Outlook. For Polish language it will be WIN-1250 or ISO 8859-2.
*/

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

function extractData($array) {
    global $labelsToCheck;
    $isContact = false;
    $data = [];

    foreach ($array as $line) {
        if ($isContact) {
            $parts = explode(":", $line);
            $data[$parts[0]] = trim($parts[1]);
        }

        if (strstr($line, "Contact:") !== false) {
            $isContact = true;
        }
    }

    return $data;
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

$dirList = scandir('.');

$labelsToCheck = [
    "File under",
    "Given name",
    "Surname",
    "Email address 1",
    "Mobile phone number"
];

$people = [];

for ($i = 0; $i < count($dirList); ++$i) {
    $dir = $dirList[$i];

    if (!is_dir($dir)) {
        echo "Skipping $dir/ Not a directory.".PHP_EOL;
        continue;
    }
    
    if (strstr($dir, "Contact") === false) {
        echo "Skipping $dir/ Not a contact directory.".PHP_EOL;
        continue;
    }

    $file = file("$dir/Contact.txt");
    $person = extractData($file);
    $people[] = mapFields($person);
}

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

foreach ($people as $person) { 
    fputcsv($outCSV, $person);
}

fclose($outCSV);

echo "Done. Check if encoding of out.csv is correct.";
