#!/usr/bin/env php
<?php
const DB_DSN = "mysql:host=DB_HOST;dbname=DB_ISPCONFIG";
const DB_USER = "DB_USER";
const DB_PASS = "DB_PASSWORD";
const MY_IP = "x.x.x.x";

    $db = new \PDO(DB_DSN, DB_USER, DB_PASS);
    $stmt = $db->query("SELECT domain FROM mail_domain");
    $domains = $stmt->fetchAll();
    $dList = [];

    foreach ($domains as $d) {
        $dList[] = $d[0];
    }

    $f = file("list.txt");
    $arr = [];
    foreach($f as $fr) {
        if (strstr($fr, "Host: ")) {
            $domain = strtolower(substr($fr, 6));
            if (!in_array($domain, $arr) && !in_array($domain, $dList)) {
                $ip = `getent hosts $domain`;
                $ip = explode(" ", $ip)[0];
                $arr[] = $ip;
            }
        }
    }

    $arr = array_unique($arr);

    $file = fopen('ips.txt', 'w');
    foreach($arr as $a => $i) {
        if ($i != MY_IP) {
            fwrite($file, $i."\n");
        }
    }
    fclose($file);
