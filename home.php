<?php
require('./_config.php');

// fetch API once (supports multiple possible keys as fallback)
$home_raw = @file_get_contents("$api/home");
if ($home_raw === false) {
    // fallback to v2 path if needed
    $home_raw = @file_get_contents("https://shifter-zeta.vercel.app/api/v2/hianime/home");
}
$home = json_decode($home_raw, true) ?? [];

// offer flexible key names depending on API variant
$spotlight = $home['spotlight'] ?? $home['spotlightAnimes'] ?? $home['spotlight_animes'] ?? [];
$trending  = $home['trending'] ?? $home['trendingAnimes'] ?? $home['topAiringAnimes'] ?? [];
$latest    = $home['latest'] ?? $home['latestEpisodes'] ?? $home['latest_episode_animes'] ?? $home['latest_sub'] ?? [];
$newOn     = $home['recommendedAnimes'] ?? $home['new_on'] ?? $home['new'] ?? [];
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <!-- === KEEP ORIGINAL SEO META (unchanged) === -->
    <title><?=$websiteTitle?> - Official <?=$websiteTitle?> #1 Watch High Quality Anime Online Without Ads</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="<?=$websiteTitle?> - Official <?=$websiteTitle?> #1 Watch High Quality Anime Online Without Ads" />
    <meta name="description" content="<?=$websiteTitle?> - Official <?=$websiteTitle?> #1 Watch High Quality Anime Online Without Ads. You can watch anime online free in HD without Ads. Best place for free find and one-click anime." />
    <meta name="keywords" content="<?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa" />
    <meta name="charset" content="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <meta name="robots" content="index, follow" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="Content-Language" content="en" />
    <meta property="og:title" content="<?=$websiteTitle?> - Official <?=$websiteTitle?> #1 Watch High Quality Anime Online Without Ads">
    <meta property="og:description" content="<?=$websiteTitle?> - Official <?=$websiteTitle?> #1 Watch High Quality Anime Online Without Ads. You can watch anime online free in HD without Ads. Best place for free find and one-click anime.">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?=$websiteTitle?>">
    <meta property="og:url" content="<?=$websiteUrl?>/home">
    <meta itemprop="image" content="<?=$banner?>">
    <meta property="og:image" content="<?=$banner?>">
    <meta property="og:image:secure_url" content="<?=$banner?>">
    <meta property="og:image:width" content="650">
    <meta property="og:image:height" content="350">
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <meta name="theme-color" content="#202125">
    <link rel="shortcut icon" href="<?=$websiteUrl?>/favicon.ico?v=<?=$version?>" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?=$websiteUrl?>/favicon.ico?v=<?=$version?>" />
    <!-- ================= REPLACED CSS: tailwind + swiper + fontawesome ================= -->
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Swiper (for slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://aniwatchtv.to/css/styles.min.css?v=1.1" />
    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="" crossorigin="anonymous"/>

    <!-- Keep any other head-injected analytics or inline scripts (if you used them earlier), e.g. Google Tag -->
    <!-- If you had inline GA script in _config or earlier, it remains intact as you requested -->

    <!-- small helper to preserve legacy classes that expect bootstrap-like container width -->
    <style>
        /* preserve some layout similarities to your previous theme */
        .container { max-width: 1200px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
        .film-poster-img { display:block; width:100%; height:auto; object-fit:cover; }
        /* minimize default list styles used by original markup */
        .film_list-wrap{ display:flex; flex-wrap:wrap; gap:1rem; }
        .flw-item{ width: calc(25% - 1rem); } /* desktop 4 columns - replicates original grid */
        @media (max-width: 980px){ .flw-item{ width: calc(33.333% - 1rem); } }
        @media (max-width: 740px){ .flw-item{ width: calc(50% - 1rem); } }
        @media (max-width: 420px){ .flw-item{ width: 100%; } }
        /* keep swiper slide sizing */
        .swiper { width:100%; }
        .deslide-cover img { width:100%; height:100%; object-fit:cover; }
    </style>

    <!-- optional small lazyload polyfill if browser doesn't support loading=lazy -->
    <script>
    // basic lazyloader for images using data-src attribute (runs after DOM loaded)
    document.addEventListener("DOMContentLoaded", function(){
        if('loading' in HTMLImageElement.prototype){
            // native lazy supported; just add loading=lazy to images if not present
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.setAttribute('loading','lazy');
                if(!img.getAttribute('src') || img.getAttribute('src').includes('no_poster')) {
                    // leave src as placeholder; set data-src to src later by IntersectionObserver or on load
                }
            });
        } else {
            // load simple lazy with IntersectionObserver
            const io = new IntersectionObserver((entries, observer) => {
                entries.forEach(e => {
                    if(e.isIntersecting){
                        const img = e.target;
                        img.src = img.dataset.src;
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: "200px" });
            document.querySelectorAll('img[data-src]').forEach(img => io.observe(img));
        }
    });
    </script>

</head>

<body data-page="page_home" class="bg-gray-50 text-gray-900">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="page_home">
        <?php include('./_php/header.php'); ?>
        <div class="clearfix"></div>

        <!-- ======= Spotlight Slider (replica of your original slidebar output) ======= -->
        <div class="deslide-wrap">
            <div class="container" style="max-width:100%!important;width:100%!important;">
                <div id="slider" class="swiper swiper-container">
                    <div class="swiper-wrapper">
                        <?php foreach ($spotlight as $index => $s): 
                            // attempt to pick best poster field name
                            $poster = $s['poster'] ?? $s['imgUrl'] ?? $s['image'] ?? ($s['posterUrl'] ?? '');
                            $title  = $s['title'] ?? $s['name'] ?? $s['animeTitle'] ?? '';
                            $jname  = $s['jname'] ?? $s['japaneseName'] ?? $title;
                            $desc   = $s['description'] ?? $s['overview'] ?? ($s['synopsis'] ?? '');
                            $type   = $s['type'] ?? ($s['format'] ?? '');
                            $year   = $s['year'] ?? $s['releaseYear'] ?? ($s['aired'] ?? '');
                            $slugWatch = $s['slugWatch'] ?? ($s['slug_watch'] ?? ($s['slug'] ?? ''));
                            $slugDetail = $s['slugDetail'] ?? ($s['slugDetail'] ?? ($s['slug'] ?? $s['id'] ?? ''));
                        ?>
                        <div class="swiper-slide">
                            <div class="deslide-item flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm">
                                <div class="deslide-cover w-full md:w-1/3">
                                    <div class="deslide-cover-img">
                                        <img class="film-poster-img lazyload" data-src="<?=htmlspecialchars($poster)?>" src="<?=$websiteUrl?>/files/images/no_poster.jpg" alt="<?=htmlspecialchars($title)?>">
                                    </div>
                                </div>

                                <div class="deslide-item-content p-4 md:p-6 w-full md:w-2/3">
                                    <div class="desi-sub-text text-sm text-gray-500">#<?=($index+1)?> Spotlight</div>
                                    <div class="desi-head-title dynamic-name text-2xl font-semibold" data-jname="<?=htmlspecialchars($jname)?>"><?=htmlspecialchars($title)?></div>

                                    <div class="sc-detail mt-3 flex flex-wrap gap-2 text-sm text-gray-600">
                                        <div class="scd-item inline-flex items-center"><i class="fas fa-play-circle mr-2"></i> <?=htmlspecialchars($type)?></div>
                                        <?php if(!empty($year)): ?>
                                        <div class="scd-item m-hide inline-flex items-center"><i class="fas fa-calendar mr-2"></i><?=htmlspecialchars($year)?></div>
                                        <?php endif; ?>
                                        <div class="scd-item"><span class="quality bg-gray-200 text-xs px-2 py-1 rounded">HD</span></div>
                                        <?php if(!empty($s['sub']) || !empty($s['hasSub'])): ?>
                                            <div class="scd-item"><span class="quality bg-white text-xs px-2 py-1 rounded">SUB</span></div>
                                        <?php endif; ?>
                                        <?php if(!empty($s['dub']) || !empty($s['hasDub'])): ?>
                                            <div class="scd-item"><span class="quality bg-white text-xs px-2 py-1 rounded">DUB</span></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="desi-description mt-3 text-sm text-gray-700" style="max-height:6.6rem; overflow:hidden;"><?=nl2br(htmlspecialchars($desc))?></div>

                                    <div class="desi-buttons mt-4 flex gap-3">
                                        <?php if(!empty($slugWatch)): ?>
                                            <a href="/watch/<?=htmlspecialchars($slugWatch)?>" class="btn btn-primary btn-radius inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded">
                                                <i class="fas fa-play-circle mr-2"></i>Watch Now
                                            </a>
                                        <?php else: ?>
                                            <a href="/anime/<?=htmlspecialchars($slugDetail)?>" class="btn btn-primary btn-radius inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded">
                                                <i class="fas fa-play-circle mr-2"></i>Watch
                                            </a>
                                        <?php endif; ?>

                                        <a class="btn btn-secondary btn-radius inline-flex items-center px-4 py-2 border border-gray-300 rounded text-gray-700" href="/anime/<?=htmlspecialchars($slugDetail)?>">
                                            <i class="fas fa-info-circle mr-2"></i> Detail <i class="fas fa-angle-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- pagination and nav -->
                    <div class="swiper-pagination mt-4"></div>
                    <div class="swiper-navigation hidden md:flex justify-end gap-3 mt-2">
                        <div class="swiper-button-prev inline-flex items-center justify-center w-10 h-10 rounded-full bg-white shadow"><i class="fas fa-angle-left"></i></div>
                        <div class="swiper-button-next inline-flex items-center justify-center w-10 h-10 rounded-full bg-white shadow"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- include trending.php style section but generated here from API -->
        <div id="anime-trending">
            <div class="container py-6">
                <section class="block_area block_area_trending bg-transparent">
                    <div class="block_area-header mb-4">
                        <div class="bah-heading">
                            <h2 class="cat-heading text-xl font-semibold">Trending</h2>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="block_area-content">
                        <div class="trending-list" id="trending-home">
                            <div class="swiper-container swiper--trending">
                                <div class="swiper-wrapper">
                                    <?php foreach ($trending as $i => $t):
                                        $timg = $t['poster'] ?? $t['imgUrl'] ?? $t['image'] ?? '';
                                        $tname = $t['title'] ?? $t['name'] ?? $t['animeTitle'] ?? '';
                                        $tid   = $t['id'] ?? $t['animeId'] ?? $t['slug'] ?? '';
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="item p-2">
                                            <div class="number mb-2">
                                                <span class="text-sm font-medium"><?=($i+1)?></span>
                                                <div class="film-title dynamic-name text-sm font-medium" data-jname="<?=htmlspecialchars($tname)?>">
                                                    <?=htmlspecialchars($tname)?>
                                                </div>
                                            </div>

                                            <a href="/anime/<?=htmlspecialchars($tid)?>" class="film-poster block" title="<?=htmlspecialchars($tname)?>">
                                                <img data-src="<?=htmlspecialchars($timg)?>" src="<?=$websiteUrl?>/files/images/no_poster.jpg" class="film-poster-img lazyload rounded" alt="<?=htmlspecialchars($tname)?>">
                                            </a>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- add navigation for the trending carousel if you want -->
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Combined Latest Episodes (no separate sub/dub sections) -->
        <div id="anime-featured" class="container py-6">
            <section class="block_area block_area_home">
                <div class="block_area-header flex items-center justify-between mb-4">
                    <div class="bah-heading">
                        <h2 class="cat-heading text-xl font-semibold">Latest Episodes</h2>
                    </div>
                    <div class="viewmore">
                        <a class="btn" href="/latest" class="text-sm text-blue-600">View more <i class="fas fa-angle-right ml-2"></i></a>
                    </div>
                </div>

                <div class="tab-content">
                    <div class="block_area-content block_area-list film_list film_list-grid">
                        <div class="film_list-wrap">
                            <?php foreach ($latest as $ep):
                                $img = $ep['imgUrl'] ?? $ep['poster'] ?? $ep['image'] ?? '';
                                $name = $ep['name'] ?? $ep['title'] ?? '';
                                $episodeId = $ep['episodeId'] ?? ($ep['id'] ?? '');
                                $epiNum = $ep['episodeNum'] ?? ($ep['ep'] ?? '');
                                $subOrDub = $ep['subOrDub'] ?? ($ep['lang'] ?? '');
                            ?>
                                <div class="flw-item bg-white rounded overflow-hidden shadow-sm">
                                    <div class="film-poster relative">
                                        <div class="tick ltr absolute left-2 top-2 z-10">
                                            <div class="tick-item-sub tick-eps amp-algn bg-white px-2 py-1 rounded text-xs">
                                                <?=htmlspecialchars($subOrDub)?>
                                            </div>
                                        </div>
                                        <div class="tick rtl absolute right-2 top-2 z-10">
                                            <div class="tick-item tick-eps amp-algn bg-white px-2 py-1 rounded text-xs">Episode <?=htmlspecialchars($epiNum)?></div>
                                        </div>

                                        <img class="film-poster-img lazyload" data-src="<?=htmlspecialchars($img)?>" src="<?=$websiteUrl?>/files/images/no_poster.jpg" alt="<?=htmlspecialchars($name)?>">
                                        <a class="film-poster-ahref absolute inset-0 flex items-center justify-center" href="/watch/<?=htmlspecialchars($episodeId)?>" title="<?=htmlspecialchars($name)?>" data-jname="<?=htmlspecialchars($name)?>">
                                            <i class="fas fa-play text-white text-2xl bg-black bg-opacity-50 p-3 rounded-full"></i>
                                        </a>
                                    </div>

                                    <div class="film-detail p-3">
                                        <h3 class="film-name text-sm font-medium leading-tight">
                                            <a href="/watch/<?=htmlspecialchars($episodeId)?>" title="<?=htmlspecialchars($name)?>" data-jname="<?=htmlspecialchars($name)?>"><?=htmlspecialchars($name)?></a>
                                        </h3>
                                        <div class="fd-infor text-xs text-gray-600 mt-2">
                                            <span class="fdi-item">Latest</span>
                                            <span class="dot mx-2">•</span>
                                            <span class="fdi-item"><?=htmlspecialchars($subOrDub)?></span>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Optional: New On / Recommended section (if API returned) -->
        <?php if(!empty($newOn)): ?>
        <div class="container py-6">
            <section class="block_area block_area_home">
                <div class="block_area-header mb-4">
                    <div class="bah-heading">
                        <h2 class="cat-heading text-xl font-semibold">New On <?=$websiteTitle?></h2>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="film_list-wrap">
                    <?php foreach($newOn as $n):
                        $nimg = $n['poster'] ?? $n['imgUrl'] ?? $n['image'] ?? '';
                        $nname = $n['title'] ?? $n['name'] ?? '';
                        $nid   = $n['id'] ?? $n['slug'] ?? '';
                    ?>
                    <div class="flw-item bg-white rounded overflow-hidden shadow-sm">
                        <div class="film-poster relative">
                            <img class="film-poster-img lazyload" data-src="<?=htmlspecialchars($nimg)?>" src="<?=$websiteUrl?>/files/images/no_poster.jpg" alt="<?=htmlspecialchars($nname)?>">
                            <a class="film-poster-ahref absolute inset-0 flex items-center justify-center" href="/anime/<?=htmlspecialchars($nid)?>" title="<?=htmlspecialchars($nname)?>">
                                <i class="fas fa-play text-white text-2xl bg-black bg-opacity-50 p-3 rounded-full"></i>
                            </a>
                        </div>
                        <div class="film-detail p-3">
                            <h3 class="film-name text-sm font-medium"><a href="/anime/<?=htmlspecialchars($nid)?>"><?=htmlspecialchars($nname)?></a></h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <?php endif; ?>

        <?php include('./_php/footer.php'); ?>

        <div id="mask-overlay"></div>

        <!-- keep jQuery if other legacy components rely on it (optional) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="" crossorigin="anonymous"></script>

        <!-- Initialize Swiper sliders -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Spotlight slider
                if (typeof Swiper !== 'undefined') {
                    new Swiper('#slider .swiper', {
                        loop: <?=$spotlight && count($spotlight) > 1 ? 'true' : 'false'?>,
                        slidesPerView: 1,
                        spaceBetween: 10,
                        pagination: {
                            el: '#slider .swiper-pagination',
                            clickable: true,
                        },
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        autoplay: {
                            delay: 6000,
                            disableOnInteraction: false,
                        },
                    });

                    // trending carousel: show multiple slides
                    try {
                        new Swiper('.swiper--trending', {
                            slidesPerView: 6,
                            spaceBetween: 12,
                            breakpoints: {
                                1200: { slidesPerView: 6 },
                                980: { slidesPerView: 4 },
                                740: { slidesPerView: 3 },
                                420: { slidesPerView: 2 },
                                0: { slidesPerView: 1.3 }
                            },
                            loop: false,
                        });
                    } catch (e) { /* no trending slides */ }
                }

                // simple data-src -> src swap for lazy images if not handled earlier
                document.querySelectorAll('img.lazyload[data-src]').forEach(img=>{
                    if('loading' in HTMLImageElement.prototype) {
                        img.setAttribute('loading','lazy');
                        img.src = img.dataset.src;
                    } else {
                        // IntersectionObserver already sets src above in head script
                    }
                });
            });
        </script>

    </div>
</body>
</html>
