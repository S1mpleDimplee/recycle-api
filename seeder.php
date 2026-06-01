<?php
/**
 * Dutch sample data seeder — v2
 * Open in browser or run via CLI: php seeder.php
 * Safe to re-run — skips rows that already exist.
 */

$envPath = __DIR__ . '/.env';
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$key, $val] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($val);
}

$conn = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if (!$conn) { die('Verbinding mislukt: ' . mysqli_connect_error()); }

echo "<pre>\n";

// ─── helpers ─────────────────────────────────────────────────────────────────

function insertUser($conn, $role, $name, $username, $surname, $email, $password, $adress, $phone, $credits = 2000)
{
    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR username = ?");
    mysqli_stmt_bind_param($check, 'ss', $email, $username);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        mysqli_stmt_bind_result($check, $existingId);
        mysqli_stmt_fetch($check);
        echo "Gebruiker '$username' bestaat al (id=$existingId), overgeslagen.\n";
        return $existingId;
    }

    $cred = mysqli_prepare($conn, "INSERT INTO credits (amount) VALUES (?)");
    mysqli_stmt_bind_param($cred, 'i', $credits);
    mysqli_stmt_execute($cred);
    $creditId = mysqli_insert_id($conn);

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (credit_id, role, name, username, surname, email, password, email_verified, adress, phonenumber)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issssssss', $creditId, $role, $name, $username, $surname, $email, $hash, $adress, $phone);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    echo "Gebruiker '$username' aangemaakt (id=$id, wachtwoord='$password', credits=$credits).\n";
    return $id;
}

function insertProduct($conn, $userId, $name, $price, $description, $listingType, $deadline = null, $availability = 'available')
{
    $check = mysqli_prepare($conn, "SELECT id FROM products WHERE user_id = ? AND product_name = ?");
    mysqli_stmt_bind_param($check, 'is', $userId, $name);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        mysqli_stmt_bind_result($check, $existingId);
        mysqli_stmt_fetch($check);
        echo "Product '$name' bestaat al (id=$existingId), overgeslagen.\n";
        return $existingId;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO products (user_id, product_name, product_price, product_description, product_availability, listing_type, bid_deadline)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isdssss', $userId, $name, $price, $description, $availability, $listingType, $deadline);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    echo "Product '$name' aangemaakt (id=$id, type=$listingType).\n";
    return $id;
}

function insertBid($conn, $productId, $bidderId, $amount, $status = 'pending', $createdAt = null)
{
    $check = mysqli_prepare($conn, "SELECT id FROM bids WHERE product_id = ? AND bidder_id = ? AND amount = ?");
    mysqli_stmt_bind_param($check, 'iii', $productId, $bidderId, $amount);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        mysqli_stmt_bind_result($check, $existingId);
        mysqli_stmt_fetch($check);
        echo "  Bod €$amount op product #$productId bestaat al (id=$existingId), overgeslagen.\n";
        return $existingId;
    }

    if ($createdAt) {
        $stmt = mysqli_prepare($conn, "INSERT INTO bids (product_id, bidder_id, amount, status, created_at) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiss', $productId, $bidderId, $amount, $status, $createdAt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO bids (product_id, bidder_id, amount, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiis', $productId, $bidderId, $amount, $status);
    }
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    echo "  Bod €$amount aangemaakt (id=$id, bidder=#$bidderId, status=$status).\n";
    return $id;
}

function markSold($conn, $productId, $sellerId, $winningBidId, $buyerId, $amount)
{
    $q1 = mysqli_prepare($conn, "UPDATE products SET product_availability = 'sold' WHERE id = ?");
    mysqli_stmt_bind_param($q1, 'i', $productId);
    mysqli_stmt_execute($q1);

    $q2 = mysqli_prepare($conn, "UPDATE bids SET status = 'accepted' WHERE id = ?");
    mysqli_stmt_bind_param($q2, 'i', $winningBidId);
    mysqli_stmt_execute($q2);

    $q3 = mysqli_prepare($conn, "UPDATE bids SET status = 'rejected' WHERE product_id = ? AND id != ? AND status = 'pending'");
    mysqli_stmt_bind_param($q3, 'ii', $productId, $winningBidId);
    mysqli_stmt_execute($q3);

    $check = mysqli_prepare($conn, "SELECT id FROM purchases WHERE product_id = ?");
    mysqli_stmt_bind_param($check, 'i', $productId);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        echo "  Aankoop voor product #$productId bestaat al, overgeslagen.\n";
        return;
    }

    $q4 = mysqli_prepare($conn,
        "INSERT INTO purchases (bid_id, product_id, buyer_id, seller_id, amount_paid) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($q4, 'iiiii', $winningBidId, $productId, $buyerId, $sellerId, $amount);
    mysqli_stmt_execute($q4);
    echo "  Aankoop aangemaakt (product #$productId, koper #$buyerId, €$amount).\n";
}

// ─── gebruikers ──────────────────────────────────────────────────────────────
echo "\n=== GEBRUIKERS ===\n";

$jan    = insertUser($conn, 'user',  'Jan',     'jandeBoer',   'de Boer',  'jan@recycle.nl',    'Welkom123!', 'Kerkstraat 12, Amsterdam',   '0612345678', 2000);
$emma   = insertUser($conn, 'user',  'Emma',    'emmaVisser',  'Visser',   'emma@recycle.nl',   'Welkom123!', 'Dorpsweg 5, Rotterdam',      '0698765432', 2000);
$roos   = insertUser($conn, 'user',  'Roos',    'roosMeyer',   'Meyer',    'roos@recycle.nl',   'Welkom123!', 'Hoofdstraat 88, Utrecht',    '0623456789', 2000);
$lucas  = insertUser($conn, 'user',  'Lucas',   'lucasBakker', 'Bakker',   'lucas@recycle.nl',  'Welkom123!', 'Laan van Poot 47, Den Haag', '0645678901', 2000);
$sophie = insertUser($conn, 'user',  'Sophie',  'sophieJansen','Jansen',   'sophie@recycle.nl', 'Welkom123!', 'Woenselse Markt 9, Eindhoven','0656789012',2000);
$thomas = insertUser($conn, 'user',  'Thomas',  'thomasSmit',  'Smit',     'thomas@recycle.nl', 'Welkom123!', 'Herestraat 3, Groningen',    '0667890123', 2000);
$admin  = insertUser($conn, 'admin', 'Beheer',  'adminBeheer', 'Beheerder','admin@recycle.nl',  'Admin1234!', 'Beheerdersweg 1, Den Haag',  '0600000001', 500);

// ─── tijden ──────────────────────────────────────────────────────────────────
$d3   = date('Y-m-d H:i:s', strtotime('+3 days'));
$d5   = date('Y-m-d H:i:s', strtotime('+5 days'));
$d7   = date('Y-m-d H:i:s', strtotime('+7 days'));
$d10  = date('Y-m-d H:i:s', strtotime('+10 days'));
$d12  = date('Y-m-d H:i:s', strtotime('+12 days'));
$d14  = date('Y-m-d H:i:s', strtotime('+14 days'));
$past = date('Y-m-d H:i:s', strtotime('-5 days'));

// ─── veiling-producten (actief) ──────────────────────────────────────────────
echo "\n=== ACTIEVE VEILINGEN ===\n";

$p1 = insertProduct($conn, $jan, 'Vintage houten kledingkast', 40,
    'Prachtige massief houten kledingkast uit de jaren \'60. Twee deuren, drie lades en een spiegel aan de binnenkant. Lichte gebruiksslitjes maar verder in uitstekende staat. Ophalen in Amsterdam.',
    'bid', $d7);

$p2 = insertProduct($conn, $jan, 'Lego Technic set 42093 – Corvette', 50,
    'Volledig complete Lego Technic Chevrolet Corvette ZR1 (set 42093). Alle steentjes aanwezig, geen beschadigingen. Handleiding inbegrepen. Doos is wat versleten maar inhoud perfect.',
    'bid', $d3);

$p3 = insertProduct($conn, $emma, 'Gazelle retro stadsfiets dames', 65,
    'Klassieke Gazelle damesfiets, 7 versnellingen, terugtraprem. Nieuw achterlicht en nieuwe band vorig jaar geplaatst. Lichte roestsporen op het frame maar rijdt soepel. Maat 57 cm. Ophalen Rotterdam.',
    'bid', $d14);

$p4 = insertProduct($conn, $roos, 'Keramische plantenpotten set (6 stuks)', 18,
    'Set van 6 handgemaakte keramische plantenpotten in aardetinten. Maten variëren van 8 cm tot 18 cm diameter. Ideaal voor kruiden of cactussen. Één pot heeft een kleine chip aan de onderkant.',
    'bid', $d7);

$p5 = insertProduct($conn, $lucas, 'Canon EOS 2000D spiegelreflexcamera', 110,
    'Canon EOS 2000D met kit-lens 18-55 mm. Sluiterstand ca. 3.200. Scherm en sensor krasloos. Incl. originele lader, 2x SD-kaart (32 GB) en cameratas. Ideaal voor beginners.',
    'bid', $d5);

$p6 = insertProduct($conn, $sophie, 'Nintendo Switch + 3 spellen', 160,
    'Nintendo Switch (2019 revisie, verbeterde accu) in neon rood/blauw. Inclusief Mario Kart 8 Deluxe, Zelda: Breath of the Wild en Pokémon Sword. Alle originele onderdelen aanwezig.',
    'bid', $d10);

$p7 = insertProduct($conn, $thomas, 'Vintage leren 3-zitsbank', 90,
    'Cognackleurige leren 3-zitsbank uit de jaren \'80. Stevige constructie, leer vertoont mooie gebruikspatina. Afmetingen: 210 x 85 x 75 cm. Ophalen in Groningen, geen transport.',
    'bid', $d7);

$p8 = insertProduct($conn, $jan, 'Trek Marlin 5 mountainbike 29 inch', 200,
    'Trek Marlin 5 uit 2021. 29 inch wielen, 21 versnellingen Shimano, hydraulische schijfremmen. Frame maat M (17.5 inch). Lichte krasjes op het frame, technisch perfect. Incl. origineel slot.',
    'bid', $d12);

$p9 = insertProduct($conn, $emma, 'De\'Longhi Dedica espressomachine', 65,
    'De\'Longhi EC685 Dedica Style. Roestvrijstalen uitvoering. Inclusief melkopschuimer, 2 filterkorfjes en ontkalkingsvloeistof. Lichte kalkaanslag, net ontkalkt. Ophalen Rotterdam.',
    'bid', $d5);

$p10 = insertProduct($conn, $roos, 'Boekenset gemengd (22 stuks)', 15,
    'Diverse Nederlandse romans en thrillers: o.a. Remco Campert, Arnon Grunberg, Lize Spit, A.F.Th. van der Heijden. Alles in goede staat. Ophalen Utrecht of verzenden voor €4,50.',
    'bid', $d7);

$p11 = insertProduct($conn, $lucas, 'Campingset 4-persoons tent + slaapzakken', 75,
    'Coleman 4-persoons tunneltent (waterkolom 3000 mm) + 4 slaapzakken tot -5°C. Alles compleet aanwezig, lichte vlekjes op grondzeil. Ideaal voor festivals of kampeeruitjes.',
    'bid', $d10);

$p12 = insertProduct($conn, $sophie, 'Bose QuietComfort 35 II koptelefoon', 95,
    'Bose QC35 II draadloze noise-cancelling koptelefoon. Zwart. Batterijduur ca. 18 uur. Kleine slijtage op oorschelpen, geluid perfect. Incl. case en kabels.',
    'bid', $d5);

// ─── vaste-prijs producten ───────────────────────────────────────────────────
echo "\n=== VASTE PRIJS ===\n";

$p13 = insertProduct($conn, $jan,    'Laptop Lenovo ThinkPad T480',          280,
    'Lenovo ThinkPad T480, Intel Core i5-8350U, 8 GB RAM, 256 GB SSD. Windows 11. Batterijduur ca. 5 uur. Kleine kras op deksel. Incl. originele oplader.',
    'buy');
$p14 = insertProduct($conn, $emma,   'IKEA MALM bureau (120 x 65 cm)',        35,
    'Wit IKEA MALM bureau. Afmetingen 120 x 65 cm. Klein krasje op het blad. Zelfmontage vereist. Ophalen Rotterdam.',
    'buy');
$p15 = insertProduct($conn, $emma,   'Heren winterjas maat L – gewatteerd',   30,
    'Donkerblauwe gewatteerde winterjas maat L. Waterdicht bovenmateriaal, fleece voering. Lichte verbleking op schouders. Gewassen en klaar voor gebruik.',
    'buy');
$p16 = insertProduct($conn, $roos,   'Sony soundbar HT-S200F',                60,
    'Sony 2.1 soundbar met ingebouwde subwoofer. HDMI ARC + optisch. Bluetooth 4.2. Afstandsbediening en alle kabels inbegrepen. Nauwelijks gebruikt.',
    'buy');
$p17 = insertProduct($conn, $lucas,  'iPhone 12 64 GB – zwart',               175,
    'Apple iPhone 12 64 GB zwart. iOS 17. Batterijgezondheid 87%. Lichte krasjes op scherm (niet zichtbaar bij gebruik). Incl. nieuw screenprotector en siliconen hoesje.',
    'buy');
$p18 = insertProduct($conn, $lucas,  'Weber compact BBQ (47 cm)',              80,
    'Weber One-Touch Premium 47 cm houtskoolbarbecue. Inclusief deksel, rooster en asopvangbak. Twee seizoenen gebruikt. Schoon en gebruiksklaar.',
    'buy');
$p19 = insertProduct($conn, $sophie, 'Dyson V8 Absolute steelstofzuiger',     115,
    'Dyson V8 Absolute cordless. Inclusief dierenhaarborstelkop, crevice tool en mini-motorkop. Accu houdt ca. 25 min. Filteronderhoud recent gedaan.',
    'buy');
$p20 = insertProduct($conn, $sophie, 'Yoga mat + 2 blokken + riem',            18,
    'Antislip yogamat 6 mm dik (paars), 2 kurken blokken en een yogariem. Alles in goede staat. Ideaal voor beginners.',
    'buy');
$p21 = insertProduct($conn, $thomas, 'Kinderwagen Bugaboo Cameleon 3',         90,
    'Bugaboo Cameleon 3 in zandkleur. Inclusief regenhoes, zonnekap en voetenzak. Wielen en remmen prima. Zitting en duwstang verstelbaar. Ophalen Groningen.',
    'buy');
$p22 = insertProduct($conn, $thomas, 'Serviesset voor 6 personen – wit',       38,
    'Compleet serviesset wit keramiek voor 6 personen: 6 dinerborden, 6 soepkommen, 6 dessertborden, 6 kopjes met schotels. Vaatwasmachinebestendig. Geen beschadigingen.',
    'buy');

// ─── afgeronde veilingen (sold) ───────────────────────────────────────────────
echo "\n=== AFGERONDE VEILINGEN ===\n";

$s1 = insertProduct($conn, $emma, 'Samsung QLED TV 55 inch (2020)',        150,
    'Samsung QE55Q60T QLED 4K Smart TV uit 2020. HDR, Tizen OS, 3x HDMI. Lichte kijkhoeken uitstekend. Afstandsbediening en voet aanwezig. Ophalen Rotterdam.',
    'bid', $past, 'sold');
$b_s1_1 = insertBid($conn, $s1, $thomas, 155, 'rejected', date('Y-m-d H:i:s', strtotime('-8 days')));
$b_s1_2 = insertBid($conn, $s1, $jan,   168, 'rejected', date('Y-m-d H:i:s', strtotime('-7 days')));
$b_s1_3 = insertBid($conn, $s1, $roos,  175, 'rejected', date('Y-m-d H:i:s', strtotime('-6 days')));
$b_s1_4 = insertBid($conn, $s1, $lucas, 190, 'accepted', date('Y-m-d H:i:s', strtotime('-5 days 2 hours')));
markSold($conn, $s1, $emma, $b_s1_4, $lucas, 190);

$s2 = insertProduct($conn, $roos, 'PlayStation 4 Slim + 5 games',         70,
    'Sony PlayStation 4 Slim 500 GB. Controllers 2x. Games: FIFA 23, GTA V, Spider-Man, Horizon Zero Dawn, God of War. Alles werkt perfect.',
    'bid', $past, 'sold');
$b_s2_1 = insertBid($conn, $s2, $emma,   72, 'rejected', date('Y-m-d H:i:s', strtotime('-10 days')));
$b_s2_2 = insertBid($conn, $s2, $thomas, 80, 'rejected', date('Y-m-d H:i:s', strtotime('-9 days')));
$b_s2_3 = insertBid($conn, $s2, $sophie, 88, 'rejected', date('Y-m-d H:i:s', strtotime('-7 days')));
$b_s2_4 = insertBid($conn, $s2, $jan,    98, 'accepted', date('Y-m-d H:i:s', strtotime('-6 days')));
markSold($conn, $s2, $roos, $b_s2_4, $jan, 98);

$s3 = insertProduct($conn, $lucas, 'Elektrische step Xiaomi M365',          100,
    'Xiaomi Mi Electric Scooter M365. Accu: ca. 20 km bereik. Nieuw achterlicht, banden 80% profiel. Kleine krasjes op het frame. Incl. originele lader.',
    'bid', $past, 'sold');
$b_s3_1 = insertBid($conn, $s3, $jan,   105, 'rejected', date('Y-m-d H:i:s', strtotime('-12 days')));
$b_s3_2 = insertBid($conn, $s3, $emma,  115, 'rejected', date('Y-m-d H:i:s', strtotime('-11 days')));
$b_s3_3 = insertBid($conn, $s3, $roos,  128, 'rejected', date('Y-m-d H:i:s', strtotime('-9 days')));
$b_s3_4 = insertBid($conn, $s3, $sophie,142, 'accepted', date('Y-m-d H:i:s', strtotime('-8 days')));
markSold($conn, $s3, $lucas, $b_s3_4, $sophie, 142);

$s4 = insertProduct($conn, $sophie, 'Vintage vinylplatenspeler Dual CS505',  55,
    'Dual CS505-4 semi-automatische platenspeler. Audio-Technica AT95E naald. Inclusief origineel stofkapje en keramische schaal. Werkt perfect, naald recent vervangen.',
    'bid', $past, 'sold');
$b_s4_1 = insertBid($conn, $s4, $jan,   58, 'rejected', date('Y-m-d H:i:s', strtotime('-15 days')));
$b_s4_2 = insertBid($conn, $s4, $emma,  65, 'rejected', date('Y-m-d H:i:s', strtotime('-14 days')));
$b_s4_3 = insertBid($conn, $s4, $roos,  72, 'rejected', date('Y-m-d H:i:s', strtotime('-13 days')));
$b_s4_4 = insertBid($conn, $s4, $thomas,82, 'accepted', date('Y-m-d H:i:s', strtotime('-12 days')));
markSold($conn, $s4, $sophie, $b_s4_4, $thomas, 82);

// ─── biedingen op actieve veilingen ──────────────────────────────────────────
echo "\n=== BIEDINGEN ===\n";

echo "Kledingkast (p1):\n";
insertBid($conn, $p1, $emma,   45);
insertBid($conn, $p1, $roos,   52);
insertBid($conn, $p1, $lucas,  60);

echo "Lego Technic (p2):\n";
insertBid($conn, $p2, $sophie, 55);
insertBid($conn, $p2, $thomas, 62);

echo "Gazelle fiets (p3):\n";
insertBid($conn, $p3, $jan,    70);
insertBid($conn, $p3, $lucas,  80);
insertBid($conn, $p3, $thomas, 88);
insertBid($conn, $p3, $roos,   95);

echo "Plantenpotten (p4):\n";
insertBid($conn, $p4, $emma,   20);
insertBid($conn, $p4, $sophie, 24);

echo "Canon camera (p5):\n";
insertBid($conn, $p5, $jan,    118);
insertBid($conn, $p5, $roos,   132);
insertBid($conn, $p5, $thomas, 148);
insertBid($conn, $p5, $sophie, 162);

echo "Nintendo Switch (p6):\n";
insertBid($conn, $p6, $emma,   168);
insertBid($conn, $p6, $jan,    175);
insertBid($conn, $p6, $lucas,  182);
insertBid($conn, $p6, $thomas, 195);

echo "Leren bank (p7):\n";
insertBid($conn, $p7, $roos,   95);
insertBid($conn, $p7, $jan,    108);

echo "Trek mountainbike (p8):\n";
insertBid($conn, $p8, $emma,   210);
insertBid($conn, $p8, $sophie, 235);
insertBid($conn, $p8, $lucas,  255);
insertBid($conn, $p8, $thomas, 270);

echo "Espressomachine (p9):\n";
insertBid($conn, $p9, $roos,   70);
insertBid($conn, $p9, $sophie, 78);

echo "Boekenset (p10):\n";
insertBid($conn, $p10, $jan,   18);
insertBid($conn, $p10, $thomas,25);

echo "Campingset (p11):\n";
insertBid($conn, $p11, $emma,  78);
insertBid($conn, $p11, $roos,  85);
insertBid($conn, $p11, $jan,   92);

echo "Bose koptelefoon (p12):\n";
insertBid($conn, $p12, $lucas, 98);
insertBid($conn, $p12, $thomas,108);
insertBid($conn, $p12, $jan,   118);
insertBid($conn, $p12, $emma,  125);

// ─── klaar ───────────────────────────────────────────────────────────────────
echo "\n=== KLAAR ===\n";
echo "Wachtwoord voor alle gewone gebruikers : Welkom123!\n";
echo "Wachtwoord voor admin                  : Admin1234!\n";
echo "\nSamenvatting:\n";
echo "  - 7 gebruikers (6 regulier + 1 admin)\n";
echo "  - 12 actieve veilingen\n";
echo "  - 10 vaste-prijs artikelen\n";
echo "  - 4 afgeronde veilingen (sold)\n";
echo "  - 38 biedingen\n";
echo "</pre>\n";
