<?php
// player.php - single-file JWPlayer + API integration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== Config =====
$API_BASE = "https://shifter-zeta.vercel.app/api/v2/hianime/episode/sources";
$REFERER_HEADER = "https://megacloud.blog/"; // API sample used this referer

// ===== Input (validate) =====
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$ep = isset($_GET['ep']) ? trim($_GET['ep']) : '';
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'sub'; // 'sub' or 'dub'

// Basic validation
if ($id === '' || $ep === '') {
    http_response_code(400);
    echo "Missing required parameters. Usage: ?id=ANIME_EPISODE_ID&ep=EP_NUMBER&cat=sub|dub";
    exit;
}
if (!in_array($cat, ['sub', 'dub'])) { $cat = 'sub'; }

// NOTE: Some earlier examples had malformed URL with extra '?' after animeEpisodeId.
// Use standard query param format: ?animeEpisodeId=...&ep=...&server=...&category=...
$apiUrl = sprintf(
    "%s?animeEpisodeId=%s&ep=%s&server=hd-2&category=%s",
    $API_BASE,
    urlencode($id),
    urlencode($ep),
    urlencode($cat)
);

// ===== Fetch API via cURL (more reliable than file_get_contents on many hosts) =====
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Referer: {$REFERER_HEADER}",
    "User-Agent: JWPlayerClient/1.0"
]);
$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($raw === false || $httpCode >= 400) {
    http_response_code(502);
    echo "<h3>API fetch failed (HTTP {$httpCode})</h3>";
    if ($curlErr) echo "<p>cURL error: " . htmlspecialchars($curlErr) . "</p>";
    echo "<p>Requested URL: " . htmlspecialchars($apiUrl) . "</p>";
    exit;
}

// Decode JSON and validate
$payload = json_decode($raw, true);
if ($payload === null) {
    http_response_code(502);
    echo "<h3>Invalid JSON from API</h3><pre>" . htmlspecialchars($raw) . "</pre>";
    exit;
}
if (!isset($payload['data'])) {
    http_response_code(502);
    echo "<h3>API response missing data key</h3><pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre>";
    exit;
}

$data = $payload['data'];

// ===== Extract source + tracks + intro/outro safely =====
$videoUrl = $data['sources'][0]['url'] ?? null;
$tracks_raw = $data['tracks'] ?? [];
$introStart = isset($data['intro']['start']) ? (float)$data['intro']['start'] : 0;
$introEnd   = isset($data['intro']['end'])   ? (float)$data['intro']['end']   : 0;
$outroStart = isset($data['outro']['start']) ? (float)$data['outro']['start'] : 0;
$outroEnd   = isset($data['outro']['end'])   ? (float)$data['outro']['end']   : 0;

if (!$videoUrl) {
    http_response_code(502);
    echo "<h3>No video source returned by API</h3><pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    exit;
}

// Map tracks into JWPlayer struct: { file, label, kind, default }
$tracks = [];
foreach ($tracks_raw as $t) {
    // API uses `url` and `lang`
    $file = $t['url'] ?? null;
    $lang = $t['lang'] ?? '';
    if (!$file) continue;
    $kind = ($lang === 'thumbnails') ? 'thumbnails' : 'captions';
    $tracks[] = [
        'file' => $file,
        'label' => $lang,
        'kind' => $kind,
        'default' => (strtolower($lang) === 'english')
    ];
}

// Optional: If you want to proxy the video through a CORS-friendly worker, add prefix here.
// $proxyPrefix = "https://goodproxy.eren-yeager-founding-titan-9.workers.dev/fetch?url=";
// $videoForPlayer = $proxyPrefix . urlencode($videoUrl);
$videoForPlayer = $videoUrl; // direct by default

// Prepare JS-friendly JSON for tracks
$tracks_json = json_encode($tracks, JSON_UNESCAPED_SLASHES);

// ===== Output HTML page =====
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Player - <?=htmlspecialchars($id)?> Ep <?=$ep?> (<?=$cat?>)</title>
<meta name="robots" content="noindex,nofollow" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/ErenYeager-AttackTitan/jw-style/style.css">
<style>
  html,body{height:100%;margin:0;background:#000;color:#fff;font-family:system-ui,Arial}
  #player{position:fixed;inset:0;height:100%;width:100%}
  .skip-btn{
    position: absolute;
    right: 18px;
    z-index: 9999;
    background: rgba(255,255,255,0.95);
    color:#000;
    padding:8px 12px;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
    display:none; /* hidden by default */
    box-shadow:0 6px 18px rgba(0,0,0,0.4);
  }
  .skip-intro { top: 14%; }
  .skip-outro { top: 22%; }
  .status {
    position: absolute;
    left: 18px;
    top: 18px;
    z-index: 9999;
    background: rgba(0,0,0,0.5);
    padding:6px 10px;border-radius:6px;font-size:13px;
  }
  /* small responsiveness */
  @media (max-width:600px){
    .skip-btn{padding:6px 10px;font-size:12px;right:12px}
  }
</style>
</head>
<body>

<div id="player"></div>
<div class="status">Ep <?=$ep?> • <?=htmlspecialchars($id)?> • <?=$cat?></div>

<!-- Skip buttons appended by script, but also include no-JS fallback -->
<button class="skip-btn skip-intro" id="skipIntro">Skip Intro</button>
<button class="skip-btn skip-outro" id="skipOutro">Skip Outro</button>

<script src="https://cdn.jsdelivr.net/gh/ErenYeager-AttackTitan/jwplayer/jw.js"></script>
<script>
(function(){
  const playerConfig = {
    playlist: [{
      title: <?= json_encode("$id - Episode $ep") ?>,
      description: <?= json_encode("$id - Episode $ep ($cat)") ?>,
      image: "",
      sources: [
        { file: <?= json_encode($videoForPlayer) ?>, type: "hls" }
      ],
      tracks: <?= $tracks_json ?>
    }],
    autostart: false,
    controls: true,
    displaytitle: true,
    displaydescription: true,
    abouttext: "Anime Player",
    aboutlink: "#",
    skin: { name: "netflix" },
    playbackRateControls: true
  };

  const player = jwplayer("player").setup(playerConfig);

  // Intro/Outro times injected from PHP
  const introStart = <?= json_encode($introStart) ?>;
  const introEnd   = <?= json_encode($introEnd) ?>;
  const outroStart = <?= json_encode($outroStart) ?>;
  const outroEnd   = <?= json_encode($outroEnd) ?>;

  // Buttons
  const $skipIntro = document.getElementById('skipIntro');
  const $skipOutro = document.getElementById('skipOutro');

  // Click handlers
  $skipIntro.addEventListener('click', function(){ player.seek(introEnd); });
  $skipOutro.addEventListener('click', function(){ player.seek(outroEnd); });

  // Helper to show/hide a button
  function show(btn){ btn.style.display = 'block'; }
  function hide(btn){ btn.style.display = 'none'; }

  // Keep track to avoid repeated seeks flooding
  let lastSeekedIntro = false;
  let lastSeekedOutro = false;

  // When player reports time - show skip buttons only when position within range
  player.on('time', function(e){
    const pos = e.position || 0;

    // Intro
    if (introStart > 0 && introEnd > introStart) {
      if (pos >= introStart && pos < introEnd) {
        // show button and auto-skip only once per entry
        show($skipIntro);
        if (!lastSeekedIntro) {
          lastSeekedIntro = true;
          // small delay avoids seeking repeatedly in the same tick
          setTimeout(()=>{ try{ player.seek(introEnd); }catch(e){} }, 50);
        }
      } else {
        hide($skipIntro);
        lastSeekedIntro = false;
      }
    } else {
      hide($skipIntro);
    }

    // Outro
    if (outroStart > 0 && outroEnd > outroStart) {
      if (pos >= outroStart && pos < outroEnd) {
        show($skipOutro);
        if (!lastSeekedOutro) {
          lastSeekedOutro = true;
          setTimeout(()=>{ try{ player.seek(outroEnd); }catch(e){} }, 50);
        }
      } else {
        hide($skipOutro);
        lastSeekedOutro = false;
      }
    } else {
      hide($skipOutro);
    }
  });

  // Hide buttons until time event triggers
  hide($skipIntro); hide($skipOutro);

  // Basic error handling
  player.on('error', function(err){
    console.error("Player error:", err);
  });

  // Optional: Seek 10s forward buttons (adds to control)
  player.on('ready', function(){
    try {
      // Add 10s forward button to the control bar (if JW exposes it)
      // This is a gentle attempt; JW skin DOM may vary. If it fails silently, no problem.
      const cont = document.querySelector('#player .jw-controlbar');
      // nothing else mandatory here — main functionality already done above
    } catch(e){}
  });

})();
</script>

</body>
</html>
