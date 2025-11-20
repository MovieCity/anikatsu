<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============== INPUTS ==============
$id  = $_GET['id'] ?? '';
$ep  = $_GET['ep'] ?? '';
$cat = $_GET['cat'] ?? 'sub';
$referer = $_GET['referer'] ?? '';  // optional referer

if ($id === '' || $ep === '') {
    echo "Missing ?id= and ?ep=";
    exit;
}

// ============== BUILD API URL EXACTLY THE WAY YOU WANT ==============
// DO NOT CHANGE THIS FORMAT — YOU SPECIFIED IT MUST STAY WRONG LIKE THIS:
$apiUrl =
    "https://shifter-zeta.vercel.app/api/v2/hianime/episode/sources" .
    "?animeEpisodeId=" . urlencode($id) .
    "?ep=" . urlencode($ep) .
    "&server=hd-2" .
    "&category=" . urlencode($cat);

// ============== FETCH API ==============
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// add referer ONLY if user requested it
$headers = ["User-Agent: AnimePlayer/1.0"];
if (!empty($referer)) {
    $headers[] = "Referer: $referer";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// API FAIL CHECK
if ($response === false || $http >= 400) {
    echo "<h3>API Failed</h3>";
    echo "<p>HTTP: $http</p>";
    echo "<p>Error: $error</p>";
    echo "<p>URL: $apiUrl</p>";
    exit;
}

// decode JSON
$json = json_decode($response, true);
if (!$json || !isset($json["data"])) {
    echo "Invalid Response:<br><pre>$response</pre>";
    exit;
}

$data = $json["data"];

// ============== EXTRACT FIELDS ==============
$video = $data["sources"][0]["url"] ?? "";
$tracks = $data["tracks"] ?? [];

$introStart = (float)($data["intro"]["start"] ?? 0);
$introEnd   = (float)($data["intro"]["end"] ?? 0);
$outroStart = (float)($data["outro"]["start"] ?? 0);
$outroEnd   = (float)($data["outro"]["end"] ?? 0);

$jwTracks = [];
foreach ($tracks as $t) {
    $jwTracks[] = [
        "file" => $t["url"],
        "label" => $t["lang"],
        "kind" => ($t["lang"] === "thumbnails" ? "thumbnails" : "captions"),
        "default" => strtolower($t["lang"]) === "english"
    ];
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Player</title>
<script src="https://cdn.jsdelivr.net/gh/ErenYeager-AttackTitan/jwplayer/jw.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/ErenYeager-AttackTitan/jw-style/style.css">
<style>
body,html { margin:0; height:100%; background:#000; }
#player { height:100%; }

.skip-btn{
  position:absolute;right:20px;
  padding:8px 12px;background:#fff;color:#000;
  border-radius:6px;font-weight:700;cursor:pointer;
  display:none;z-index:99999;
}
#skipIntro{top:20%;}
#skipOutro{top:28%;}
</style>
</head>
<body>

<div id="player"></div>

<button id="skipIntro" class="skip-btn">Skip Intro</button>
<button id="skipOutro" class="skip-btn">Skip Outro</button>

<script>
const player = jwplayer("player").setup({
    playlist:[{
        file: <?= json_encode($video) ?>,
        tracks: <?= json_encode($jwTracks) ?>
    }],
    autostart:false,
    skin:{name:"netflix"}
});

const introStart = <?= $introStart ?>;
const introEnd   = <?= $introEnd ?>;
const outroStart = <?= $outroStart ?>;
const outroEnd   = <?= $outroEnd ?>;

const introBtn = document.getElementById("skipIntro");
const outroBtn = document.getElementById("skipOutro");

introBtn.onclick = () => player.seek(introEnd);
outroBtn.onclick = () => player.seek(outroEnd);

let introOnce = false;
let outroOnce = false;

player.on("time", e => {
    const t = e.position;

    // INTRO
    if (t >= introStart && t < introEnd) {
        introBtn.style.display = "block";
        if (!introOnce) {
            introOnce = true;
            player.seek(introEnd);
        }
    } else {
        introBtn.style.display = "none";
    }

    // OUTRO
    if (t >= outroStart && t < outroEnd) {
        outroBtn.style.display = "block";
        if (!outroOnce) {
            outroOnce = true;
            player.seek(outroEnd);
        }
    } else {
        outroBtn.style.display = "none";
    }
});
</script>
</body>
</html>
