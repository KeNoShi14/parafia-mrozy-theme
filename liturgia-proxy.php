<?php

declare(strict_types=1);

const PM_SOURCE_HOST   = 'https://widget.niedziela.pl';
const PM_CACHE_TTL     = 43200;
const PM_FETCH_TIMEOUT = 5;
const PM_CONNECT_TO    = 3;
const PM_DATE_RANGE    = 400;

$allowed_origins = [];

if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'GET' ) {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ( $origin !== '' && in_array($origin, $allowed_origins, true) ) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

$date = isset($_GET['date'])
    ? preg_replace('/[^0-9\-]/', '', (string) $_GET['date'])
    : date('Y-m-d');

if ( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ) {
    pm_json_error('Nieprawidłowy format daty', 400);
}

$d      = DateTime::createFromFormat('!Y-m-d', $date);
$errors = DateTime::getLastErrors();
if ( ! $d || ( is_array($errors) && ( $errors['warning_count'] || $errors['error_count'] ) ) ) {
    pm_json_error('Nieprawidłowa data', 400);
}

$today     = new DateTime('today');
$diff_days = (int) $today->diff($d)->format('%r%a');
if ( abs($diff_days) > PM_DATE_RANGE ) {
    pm_json_error('Data poza dozwolonym zakresem', 400);
}

$cache_dir  = pm_cache_dir();
$cache_file = $cache_dir . '/liturgia-' . substr( sha1( $date . __FILE__ ), 0, 16 ) . '.json';

if ( random_int(1, 100) === 1 ) {
    pm_cache_gc($cache_dir);
}

if ( is_file($cache_file) && ( time() - (int) filemtime($cache_file) ) < PM_CACHE_TTL ) {
    $cached = pm_cache_read($cache_file);
    if ( $cached !== null ) {
        header('X-Cache: hit');
        echo $cached;
        exit;
    }
}

$url = PM_SOURCE_HOST . '/liturgia_out.js.php?data=' . rawurlencode($date) . '&kodowanie=utf-8';
$response = pm_fetch_url($url, PM_FETCH_TIMEOUT);

if ( $response === null ) {
    $stale = is_file($cache_file) ? pm_cache_read($cache_file) : null;
    if ( $stale !== null ) {
        header('X-Cache: stale');
        echo $stale;
        exit;
    }
    pm_json_error('Nie udało się pobrać czytań', 502);
}

$html = '';
if ( preg_match("/document\.write\('(.+)'\);?\s*$/s", $response, $m) ) {
    $html = stripslashes($m[1]);
} elseif ( preg_match('/document\.write\("(.+)"\);?\s*$/s', $response, $m) ) {
    $html = stripslashes($m[1]);
}
if ( $html === '' ) {
    $html = $response;
}

$result = parseLiturgia($html);
$json   = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if ( $json === false ) {
    pm_json_error('Błąd przetwarzania czytań', 500);
}

if ( ! empty($result['msze']) ) {
    pm_cache_write($cache_file, $json);
}

header('X-Cache: miss');
echo $json;
exit;


function pm_json_error( string $msg, int $code ): void {
    http_response_code($code);
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function pm_cache_dir(): string {
    $dir = sys_get_temp_dir() . '/pm-liturgia-cache';
    if ( ! is_dir($dir) ) {
        @mkdir($dir, 0700, true);
    }
    return $dir;
}

function pm_cache_read( string $file ): ?string {
    $raw = @file_get_contents($file);
    if ( $raw === false || $raw === '' ) {
        return null;
    }
    $decoded = json_decode($raw, true);
    if ( ! is_array($decoded) || ( ! isset($decoded['msze']) && ! isset($decoded['error']) ) ) {
        return null;
    }
    return $raw;
}

function pm_cache_write( string $file, string $data ): void {
    $tmp = $file . '.' . getmypid() . '.tmp';
    if ( @file_put_contents($tmp, $data, LOCK_EX) !== false ) {
        @rename($tmp, $file);
    }
}

function pm_cache_gc( string $dir ): void {
    $cutoff = time() - ( PM_CACHE_TTL * 4 );
    foreach ( glob($dir . '/liturgia-*.json') ?: [] as $f ) {
        if ( (int) @filemtime($f) < $cutoff ) {
            @unlink($f);
        }
    }
}

function pm_fetch_url( string $url, int $timeout ): ?string {
    if ( function_exists('curl_init') ) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => PM_CONNECT_TO,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS      => 0,
            CURLOPT_USERAGENT      => 'ParafiaProxy/1.1 (+liturgia)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        ]);
        $body = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ( is_string($body) && $body !== '' && $http >= 200 && $http < 300 ) {
            return $body;
        }
        return null;
    }

    $ctx = stream_context_create([
        'http' => [
            'method'          => 'GET',
            'timeout'         => $timeout,
            'follow_location' => 0,
            'user_agent'      => 'ParafiaProxy/1.1 (+liturgia)',
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    return ( is_string($body) && $body !== '' ) ? $body : null;
}


function czyt_clean( $s ) {
    $s = preg_replace( '/<br\s*\/?>/i', "\n", $s );
    $s = strip_tags( $s );
    $s = html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    return trim( $s );
}

function generujWstep( $sigla, $typ ) {
    $ksiegi = [
        'Rdz'  => 'Czytanie z Księgi Rodzaju',
        'Wj'   => 'Czytanie z Księgi Wyjścia',
        'Kpł'  => 'Czytanie z Księgi Kapłańskiej',
        'Lb'   => 'Czytanie z Księgi Liczb',
        'Pwt'  => 'Czytanie z Księgi Powtórzonego Prawa',
        'Joz'  => 'Czytanie z Księgi Jozuego',
        'Sdz'  => 'Czytanie z Księgi Sędziów',
        'Rt'   => 'Czytanie z Księgi Rut',
        '1 Sm' => 'Czytanie z Pierwszej Księgi Samuela',
        '2 Sm' => 'Czytanie z Drugiej Księgi Samuela',
        '1 Krl'=> 'Czytanie z Pierwszej Księgi Królewskiej',
        '2 Krl'=> 'Czytanie z Drugiej Księgi Królewskiej',
        '1 Krn'=> 'Czytanie z Pierwszej Księgi Kronik',
        '2 Krn'=> 'Czytanie z Drugiej Księgi Kronik',
        'Ezd'  => 'Czytanie z Księgi Ezdrasza',
        'Ne'   => 'Czytanie z Księgi Nehemiasza',
        'Tb'   => 'Czytanie z Księgi Tobiasza',
        'Jdt'  => 'Czytanie z Księgi Judyty',
        'Est'  => 'Czytanie z Księgi Estery',
        '1 Mch'=> 'Czytanie z Pierwszej Księgi Machabejskiej',
        '2 Mch'=> 'Czytanie z Drugiej Księgi Machabejskiej',
        'Hi'   => 'Czytanie z Księgi Hioba',
        'Ps'   => 'Psalm',
        'Prz'  => 'Czytanie z Księgi Przysłów',
        'Koh'  => 'Czytanie z Księgi Koheleta',
        'Pnp'  => 'Czytanie z Pieśni nad Pieśniami',
        'Mdr'  => 'Czytanie z Księgi Mądrości',
        'Syr'  => 'Czytanie z Księgi Syracha',
        'Iz'   => 'Czytanie z Księgi proroka Izajasza',
        'Jr'   => 'Czytanie z Księgi proroka Jeremiasza',
        'Lm'   => 'Czytanie z Lamentacji',
        'Ba'   => 'Czytanie z Księgi proroka Barucha',
        'Ez'   => 'Czytanie z Księgi proroka Ezechiela',
        'Dn'   => 'Czytanie z Księgi proroka Daniela',
        'Oz'   => 'Czytanie z Księgi proroka Ozeasza',
        'Jl'   => 'Czytanie z Księgi proroka Joela',
        'Am'   => 'Czytanie z Księgi proroka Amosa',
        'Ab'   => 'Czytanie z Księgi proroka Abdiasza',
        'Jon'  => 'Czytanie z Księgi proroka Jonasza',
        'Mi'   => 'Czytanie z Księgi proroka Micheasza',
        'Na'   => 'Czytanie z Księgi proroka Nahuma',
        'Ha'   => 'Czytanie z Księgi proroka Habakuka',
        'So'   => 'Czytanie z Księgi proroka Sofoniasza',
        'Ag'   => 'Czytanie z Księgi proroka Aggeusza',
        'Za'   => 'Czytanie z Księgi proroka Zachariasza',
        'Ml'   => 'Czytanie z Księgi proroka Malachiasza',
        'Mt'   => 'Słowa Ewangelii według Świętego Mateusza',
        'Mk'   => 'Słowa Ewangelii według Świętego Marka',
        'Łk'   => 'Słowa Ewangelii według Świętego Łukasza',
        'J'    => $typ === 'ewangelia' ? 'Słowa Ewangelii według Świętego Jana' : 'Czytanie z Ewangelii według Świętego Jana',
        'Dz'   => 'Czytanie z Dziejów Apostolskich',
        'Rz'   => 'Czytanie z Listu Świętego Pawła Apostoła do Rzymian',
        '1 Kor'=> 'Czytanie z Pierwszego Listu Świętego Pawła Apostoła do Koryntian',
        '2 Kor'=> 'Czytanie z Drugiego Listu Świętego Pawła Apostoła do Koryntian',
        'Ga'   => 'Czytanie z Listu Świętego Pawła Apostoła do Galatów',
        'Ef'   => 'Czytanie z Listu Świętego Pawła Apostoła do Efezjan',
        'Flp'  => 'Czytanie z Listu Świętego Pawła Apostoła do Filipian',
        'Kol'  => 'Czytanie z Listu Świętego Pawła Apostoła do Kolosan',
        '1 Tes'=> 'Czytanie z Pierwszego Listu Świętego Pawła Apostoła do Tesaloniczan',
        '2 Tes'=> 'Czytanie z Drugiego Listu Świętego Pawła Apostoła do Tesaloniczan',
        '1 Tm' => 'Czytanie z Pierwszego Listu Świętego Pawła Apostoła do Tymoteusza',
        '2 Tm' => 'Czytanie z Drugiego Listu Świętego Pawła Apostoła do Tymoteusza',
        'Tt'   => 'Czytanie z Listu Świętego Pawła Apostoła do Tytusa',
        'Flm'  => 'Czytanie z Listu Świętego Pawła Apostoła do Filemona',
        'Hbr'  => 'Czytanie z Listu do Hebrajczyków',
        'Jk'   => 'Czytanie z Listu Świętego Jakuba Apostoła',
        '1 P'  => 'Czytanie z Pierwszego Listu Świętego Piotra Apostoła',
        '2 P'  => 'Czytanie z Drugiego Listu Świętego Piotra Apostoła',
        '1 J'  => 'Czytanie z Pierwszego Listu Świętego Jana Apostoła',
        '2 J'  => 'Czytanie z Drugiego Listu Świętego Jana Apostoła',
        '3 J'  => 'Czytanie z Trzeciego Listu Świętego Jana Apostoła',
        'Jud'  => 'Czytanie z Listu Świętego Judy Apostoła',
        'Ap'   => 'Czytanie z Apokalipsy Świętego Jana Apostoła',
    ];

    foreach ( $ksiegi as $skrot => $wstep ) {
        if ( strpos( $sigla, $skrot ) === 0 ) {
            if ( $typ === 'ewangelia' && in_array($skrot, ['Mt','Mk','Łk','J']) ) {
                return $wstep;
            }
            return $wstep;
        }
    }

    return '';
}

function parseLiturgia( $html ) {
    $data = [
        'dzien'   => '',
        'swieto'  => '',
        'rok'     => '',
        'msze'    => [],
        'dodatki' => [],
    ];

    $html = preg_replace( '/<img[^>]+nd_banner_bottom[^>]*>/i', '', $html );

    if ( preg_match( '/<p[^>]+nd_dzien[^>]*>(.*?)<\/p>/si', $html, $m ) )
        $data['dzien'] = czyt_clean( $m[1] );

    if ( preg_match( '/<span[^>]+nd_swieto[^>]*>(.*?)<\/span>/si', $html, $m ) )
        $data['swieto'] = czyt_clean( $m[1] );

    if ( preg_match_all( '/<p[^>]+nd_wstep[^>]*>(.*?)<\/p>/si', $html, $m ) ) {
        $parts = [];
        foreach ( $m[1] as $w ) {
            $t = czyt_clean( $w );
            if ( $t ) $parts[] = $t;
        }
        $data['rok'] = implode( ' · ', $parts );
    }

    $msza_parts = preg_split( '/<p[^>]+nd_msza_tyt[^>]*>/si', $html );

    if ( count($msza_parts) <= 1 ) {
        $data['msze'][] = parseMsza( $html, '' );
    } else {
        array_shift( $msza_parts );
        preg_match_all( '/<p[^>]+nd_msza_tyt[^>]*>(.*?)<\/p>/si', $html, $tm );
        $seen = [];
        foreach ( $msza_parts as $idx => $part ) {
            $tytul = isset($tm[1][$idx]) ? czyt_clean($tm[1][$idx]) : '';
            $tytul_key = mb_strtolower( preg_replace('/\s+/', ' ', trim($tytul)) );
            if ( in_array($tytul_key, $seen, true) ) continue;
            $seen[] = $tytul_key;
            $msza = parseMsza( $part, $tytul );
            if ( !empty($msza['czytania']) ) {
                $data['msze'][] = $msza;
            }
        }
        if ( empty($data['msze']) ) {
            $data['msze'][] = parseMsza( $html, '' );
        }
    }

    if ( preg_match( '/<div[^>]+nd_liturgia_dodatki[^>]*>(.*?)<\/div>/si', $html, $m ) ) {
        $add = $m[1];
        $dodatki = [];
        preg_match_all( '/<p[^>]+nd_dodatki_styt[^>]*>(.*?)<\/p>/si', $add, $as );
        preg_match_all( '/<p[^>]+nd_dodatki_tresc[^>]*>(.*?)<\/p>/si', $add, $at );
        for ( $i = 0; $i < count($as[1]); $i++ ) {
            $dodatki[] = [
                'stytul' => czyt_clean( $as[1][$i] ),
                'tresc'  => isset($at[1][$i]) ? czyt_clean( $at[1][$i] ) : '',
            ];
        }
        $data['dodatki'] = $dodatki;
    }

    return $data;
}

function parseMsza( $html, $tytul ) {
    $msza = [ 'tytul' => $tytul, 'czytania' => [] ];

    $bloki = preg_split( '/<p[^>]+nd_czytanie_nazwa[^>]*>/si', $html );
    if ( count($bloki) <= 1 ) return $msza;

    array_shift( $bloki );
    preg_match_all( '/<p[^>]+nd_czytanie_nazwa[^>]*>(.*?)<\/p>/si', $html, $nm );

    foreach ( $bloki as $idx => $blok ) {
        $nazwa_raw = isset($nm[1][$idx]) ? czyt_clean($nm[1][$idx]) : '';

        $n = mb_strtolower($nazwa_raw);
        if      ( str_contains($n, 'psalm') )     $typ = 'psalm';
        elseif  ( str_contains($n, 'aklamacja') ) $typ = 'aklamacja';
        elseif  ( str_contains($n, 'ewangelia') ) $typ = 'ewangelia';
        elseif  ( str_contains($n, 'sekwencja') ) $typ = 'sekwencja';
        else                                      $typ = 'czytanie';

        $sigla = '';
        $tytul_czyt = '';
        if ( preg_match( '/<p[^>]+nd_czytanie_sigla[^>]*>(.*?)<\/p>/si', $blok, $m ) ) {
            $sigla_raw = czyt_clean( $m[1] );
            if ( preg_match( '/^((?:[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+\s+[\d,.\s\-a-z]+?))(\s+[A-ZĄĆĘŁŃÓŚŹŻ].+)?$/', $sigla_raw, $sm ) ) {
                $sigla      = trim( $sm[1] );
                $tytul_czyt = isset($sm[2]) ? trim($sm[2]) : '';
            } else {
                $sigla = $sigla_raw;
            }
        }

        if ( preg_match( '/<p[^>]+nd_czytanie_tyt[^>]*>(.*?)<\/p>/si', $blok, $m ) )
            $tytul_czyt = czyt_clean( $m[1] );

        $nazwa = $nazwa_raw;

        $perykopa = '';
        if ( preg_match( '/<p[^>]+nd_czytanie_perykopa[^>]*>(.*?)<\/p>/si', $blok, $m ) )
            $perykopa = czyt_clean( $m[1] );

        $wstep = '';
        if ( preg_match( '/<p[^>]+nd_czytanie2[^>]*>(.*?)<\/p>/si', $blok, $m ) ) {
            $wstep = czyt_clean( $m[1] );
        }
        if ( empty($wstep) && !empty($sigla) ) {
            $wstep = generujWstep( $sigla, $typ );
        }

        $zakonczenie = '';
        if ( preg_match( '/<p[^>]+nd_czytanie_odp[^>]*>(.*?)<\/p>/si', $blok, $m ) )
            $zakonczenie = czyt_clean( $m[1] );

        $refren = '';
        if ( preg_match( '/<p[^>]+nd_psalm_refren[^>]*>(.*?)<\/p>/si', $blok, $m ) )
            $refren = czyt_clean( $m[1] );

        $strofy = [];
        $czesci = preg_split( '/<p[^>]+nd_psalm_refren[^>]*>.*?<\/p>/si', $blok );
        foreach ( $czesci as $czesc ) {
            if ( preg_match_all( '/<p[^>]+(?<!\w)nd_psalm(?!\w)[^>]*>(.*?)<\/p>/si', $czesc, $pm ) ) {
                $strofa_linie = [];
                foreach ( $pm[1] as $w ) {
                    $t = czyt_clean($w);
                    if ($t) $strofa_linie[] = $t;
                }
                if ( $strofa_linie ) $strofy[] = implode("\n", $strofa_linie);
            }
        }

        $tresc = [];
        if ( preg_match_all( '/<p[^>]+nd_czytanie_tresc[^>]*>(.*?)<\/p>/si', $blok, $tm ) ) {
            foreach ( $tm[1] as $t ) {
                $cleaned = czyt_clean($t);
                if ($cleaned) $tresc[] = $cleaned;
            }
        }

        $msza['czytania'][] = [
            'nazwa'       => $nazwa,
            'typ'         => $typ,
            'sigla'       => $sigla,
            'tytul'       => $tytul_czyt,
            'perykopa'    => $perykopa,
            'wstep'       => $wstep,
            'zakonczenie' => $zakonczenie,
            'refren'      => $refren,
            'strofy'      => $strofy,
            'tresc'       => $tresc,
        ];
    }

    return $msza;
}
