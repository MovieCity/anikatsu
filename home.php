<?php
// DEBUG / DIAGNOSTIC home.php
// Overwrite your existing home.php with this temporarily to see what's coming from the API.
// IMPORTANT: restore your original file after debugging, or let me produce the final file.

require('./_config.php');

// show all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Candidate API endpoints (try $api/home and v2 path)
$apiCandidates = [
    rtrim($api, '/') . '/home',
    'https://shifter-zeta.vercel.app/api/v2/hianime/home',
    rtrim($api, '/') . '/api/v2/hianime/home',
    rtrim($api, '/') . '/api/v2/hianime/home?raw=1'
];

// helper to get via curl
function fetch_curl($url, &$info = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    // set a User-Agent to avoid some blocks
    curl_setopt($ch, CURLOPT_USERAGENT, 'AniSpine-Debug/1.0 (+https://example.com)');
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return [$res, $err, $info];
}

// attempt sequential fetch
$raw = false;
$used = null;
$fetchInfo = null;
foreach ($apiCandidates as $candidate) {
    list($res, $err, $info) = fetch_curl($candidate, $info_out);
    if ($res !== false && strlen($res) > 0 && ($info['http_code'] ?? 0) === 200) {
        $raw = $res;
        $used = $candidate . " (curl)";
        $fetchInfo = $info;
        break;
    }
    // try file_get_contents fallback for this candidate
    if (empty($raw)) {
        // only try file_get_contents if allow_url_fopen likely enabled
        if (ini_get('allow_url_fopen')) {
            $res2 = @file_get_contents($candidate);
            if ($res2 !== false && strlen($res2) > 0) {
                $raw = $res2;
                $used = $candidate . " (file_get_contents)";
                $fetchInfo = ['http_code' => 200];
                break;
            }
        }
    }
}

// If still nothing, try directly the $api variable raw
if ($raw === false && !empty($api)) {
    $try = rtrim($api, '/');
    list($res, $err, $info) = fetch_curl($try, $info_out);
    if ($res !== false && ($info['http_code'] ?? 0) === 200) {
        $raw = $res;
        $used = $try . " (curl)";
        $fetchInfo = $info;
    } else {
        if (ini_get('allow_url_fopen')) {
            $res2 = @file_get_contents($try);
            if ($res2 !== false) {
                $raw = $res2;
                $used = $try . " (file_get_contents)";
                $fetchInfo = ['http_code' => 200];
            }
        }
    }
}

// Start page (keep meta/SEO minimal to avoid losing indexing)
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Debug Home — AniSpine / HiAnime API Debug</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial; padding:18px; background:#f7fafc; color:#111}
    .box{background:#fff;border:1px solid #e5e7eb;padding:14px;border-radius:8px;margin-bottom:12px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
    pre{white-space:pre-wrap;word-break:break-word;font-size:13px}
    .grid{display:flex;gap:12px;flex-wrap:wrap}
    .card{background:#fff;border:1px solid #e5e7eb;padding:10px;border-radius:8px;width:240px}
    img{max-width:100%}
    .fail{color:#b91c1c;font-weight:700}
    .ok{color:#047857;font-weight:700}
  </style>
</head>
<body>
  <h1>API Debug — home.php</h1>

  <div class="box">
    <strong>Attempted endpoints (in order):</strong>
    <ul>
      <?php foreach ($apiCandidates as $c): ?><li><?=htmlspecialchars($c)?></li><?php endforeach; ?>
    </ul>
  </div>

  <div class="box">
    <strong>Fetch result:</strong>
    <p>
      <?php if ($raw === false): ?>
        <span class="fail">No response fetched from any candidate endpoint.</span>
      <?php else: ?>
        <span class="ok">Fetched successfully from: <?=htmlspecialchars($used)?></span>
    <br>
    HTTP info: <?=htmlspecialchars(json_encode($fetchInfo))?>
      <?php endif; ?>
    </p>
  </div>

  <div class="box">
    <strong>Server PHP settings (useful):</strong>
    <pre>
allow_url_fopen: <?=ini_get('allow_url_fopen') ? 'ON' : 'OFF'?>
curl enabled: <?=function_exists('curl_version') ? 'YES' : 'NO'?>
PHP version: <?=PHP_VERSION?>
    </pre>
  </div>

<?php
if ($raw === false) {
    // show some common checks and tips
    ?>
    <div class="box">
      <strong>Troubleshooting tips</strong>
      <ol>
        <li>Check that <code>$api</code> in <code>_config.php</code> is set to the correct base URL (no trailing slash recommended).</li>
        <li>Check server can reach the API: try <code>curl -I https://shifter-zeta.vercel.app/api/v2/hianime/home</code> from server shell (if you have SSH).</li>
        <li>If <code>allow_url_fopen</code> is OFF, file_get_contents won't work for URL; cURL should be used (we tried cURL above).</li>
        <li>If output is blank, check webserver error logs (apache/nginx/php-fpm) for DNS/SSL errors or blocked outbound connections.</li>
        <li>Some hosts block outbound HTTP(S) requests — ask your host to enable outbound HTTP(S).</li>
      </ol>
    </div>
    <?php
    exit;
}

// decode JSON and inspect
$json = json_decode($raw, true);
$decodeError = json_last_error_msg();
?>

  <div class="box">
    <strong>JSON decode status:</strong>
    <p><?php echo ($json === null) ? "<span class='fail'>JSON decode failed: ".htmlspecialchars($decodeError)."</span>" : "<span class='ok'>Decoded OK</span>"; ?></p>
    <details>
      <summary>Raw JSON (first 40k chars)</summary>
      <pre><?php echo htmlspecialchars(substr($raw,0,40000)); ?></pre>
    </details>
  </div>

<?php
if ($json === null) {
    // nothing else to do
    exit;
}

// Heuristics: search for common keys
$possibleSpotlightKeys = ['spotlight','spotlightAnimes','spotlight_animes','spotlightAnime','featured','data'];
$foundSpotlightKey = null;
foreach ($possibleSpotlightKeys as $k) {
    if (isset($json[$k])) { $foundSpotlightKey = $k; break; }
    // some APIs wrap in data
    if (isset($json['data']) && isset($json['data'][$k])) { $foundSpotlightKey = 'data->'.$k; $json = $json['data']; break; }
}
// trending and latest keys
$possibleTrendingKeys = ['trending','trendingAnimes','topAiringAnimes','trending_now','popular','top'];
$foundTrendingKey = null;
foreach ($possibleTrendingKeys as $k) {
    if (isset($json[$k])) { $foundTrendingKey = $k; break; }
}
$possibleLatestKeys = ['latest','latestEpisodes','latest_episode_animes','recent','recent-release','recent_release','latest_sub','latest_dub','recentlyUpdated'];
$foundLatestKey = null;
foreach ($possibleLatestKeys as $k) {
    if (isset($json[$k])) { $foundLatestKey = $k; break; }
}

// Print what keys exist at top level
$topKeys = array_keys($json);
?>

  <div class="box">
    <strong>Top-level keys in the returned JSON:</strong>
    <pre><?php echo htmlspecialchars(json_encode($topKeys, JSON_PRETTY_PRINT)); ?></pre>
    <p>Detected spotlight key: <strong><?= $foundSpotlightKey ?? 'NOT FOUND' ?></strong></p>
    <p>Detected trending key: <strong><?= $foundTrendingKey ?? 'NOT FOUND' ?></strong></p>
    <p>Detected latest key: <strong><?= $foundLatestKey ?? 'NOT FOUND' ?></strong></p>
  </div>

<?php
// Prepare arrays based on best guesses
$spotlight = [];
$trending = [];
$latest = [];

if ($foundSpotlightKey) {
    // handle data->spotlight case
    if ($foundSpotlightKey === 'data->spotlight') { $spotlight = $json['spotlight'] ?? []; }
    else { $spotlight = $json[$foundSpotlightKey] ?? []; }
}

if ($foundTrendingKey) { $trending = $json[$foundTrendingKey] ?? []; }
if ($foundLatestKey) { $latest = $json[$foundLatestKey] ?? []; }

// Additional heuristic: some APIs use $json['data'] containing arrays inside
if (empty($spotlight) && isset($json['data']) && is_array($json['data'])) {
    foreach (['spotlight','spotlightAnimes','spotlight_animes','featured'] as $k) {
        if (isset($json['data'][$k])) { $spotlight = $json['data'][$k]; break; }
    }
    foreach (['trending','trendingAnimes','popular','topAiringAnimes'] as $k) {
        if (isset($json['data'][$k])) { $trending = $json['data'][$k]; break; }
    }
    foreach (['latest','latestEpisodes','recent'] as $k) {
        if (isset($json['data'][$k])) { $latest = $json['data'][$k]; break; }
    }
}

// Show counts
?>
  <div class="box">
    <strong>Detected arrays and counts:</strong>
    <ul>
      <li>Spotlight count: <?php echo is_array($spotlight) ? count($spotlight) : 'not array'; ?></li>
      <li>Trending count: <?php echo is_array($trending) ? count($trending) : 'not array'; ?></li>
      <li>Latest count: <?php echo is_array($latest) ? count($latest) : 'not array'; ?></li>
    </ul>
  </div>

<?php
// Render a few sample items from each array to inspect exact fields
function showSamples($arr, $title, $limit=4) {
    if (!is_array($arr) || count($arr)===0) {
        echo "<div class='box'><strong>{$title}:</strong> <em>none</em></div>";
        return;
    }
    echo "<div class='box'><strong>{$title} (first {$limit} entries):</strong><div class='grid' style='margin-top:8px'>";
    $c=0;
    foreach ($arr as $item) {
        if ($c++ >= $limit) break;
        echo "<div class='card'>";
        echo "<div style='height:140px;overflow:hidden;margin-bottom:8px'>";
        $img = $item['poster'] ?? $item['imgUrl'] ?? $item['image'] ?? $item['thumbnail'] ?? '';
        if ($img) echo "<img src='".htmlspecialchars($img)."' alt='thumb' onerror=\"this.style.opacity=.5\">"; else echo "<div style='width:100%;height:100%;background:#efefef;display:flex;align-items:center;justify-content:center;color:#666'>no image</div>";
        echo "</div>";
        echo "<div style='font-weight:600;margin-bottom:6px'>".htmlspecialchars($item['title'] ?? $item['name'] ?? $item['animeTitle'] ?? 'NO TITLE')."</div>";
        echo "<pre style='font-size:12px;max-height:120px;overflow:auto'>".htmlspecialchars(json_encode(array_slice($item,0,10), JSON_PRETTY_PRINT))."</pre>";
        echo "</div>";
    }
    echo "</div></div>";
}

showSamples($spotlight, 'Spotlight samples');
showSamples($trending, 'Trending samples');
showSamples($latest, 'Latest samples');

?>

  <div class="box">
    <strong>Next steps I will do for you after you paste the diagnostics here:</strong>
    <ol>
      <li>If we see the fields names differ, I'll adapt the rendering code to those fields (e.g. 'poster' vs 'imgUrl' vs 'image').</li>
      <li>If fetch fails due to server restrictions, I'll provide a cURL-based fallback and instructions to enable outbound requests or the GitHub Action auto-deploy alternative.</li>
      <li>Once we confirm the exact keys, I'll give you the full production `home.php` that uses those keys, Tailwind, Swiper and preserves your SEO & JS includes.</li>
    </ol>
  </div>

</body>
</html>
