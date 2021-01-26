#!/usr/bin/env php
<?php 

/**
 * 1. Configure DB_DSN, DB_USER, DB_PASS constants
 * 2. Replace mail@example.com in CERTBOT_BASE with vaild email 
 * 3. Optionally add domains to IGNORED_DOMAINS
 * 4. Change mail.example.com to vaild base domain for cert in $domainString
 * 5. Run the script, it will output command to use with certbot
 */

const DB_DSN = "mysql:host=;dbname=";
const DB_USER = "";
const DB_PASS = "";

const APP_MODE = "prod";

const CERTBOT_BASE = "--preferred-challenges=dns --manual --expand --renew-by-default --text --agree-tos --manual-public-ip-logging-ok --email mail@example.com";
const CERTBOT_ARGS = CERTBOT_BASE." --cert-name san ";
const CERTBOT_ARGS_DEV = CERTBOT_BASE." --cert-name san_staging --staging";

const IGNORED_DOMAINS = [
];

try {
    $db = new \PDO(DB_DSN, DB_USER, DB_PASS);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$stmt = $db->query("SELECT domain FROM mail_domain");
$domains = $stmt->fetchAll();
$domainString = "-d mail.example.com ";

foreach ($domains as $domainRow) {
    if (!in_array("mail.$domainRow[domain]", IGNORED_DOMAINS))
    $domainString .= "-d mail.$domainRow[domain] ";
}

if (APP_MODE == "staging") {
    $command = "certbot certonly ".$domainString.CERTBOT_ARGS_DEV.PHP_EOL;
} else {
    $command = "certbot certonly ".$domainString.CERTBOT_ARGS.PHP_EOL;
}
echo($command);
// system($command);
