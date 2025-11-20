<?php 
require('./_config.php');

// Fetch API
$home = json_decode(file_get_contents("https://shifter-zeta.vercel.app/api/v2/hianime/home"), true);

// Helper to render anime card
function card($a, $websiteUrl){
    return '
    <div class="flw-item">
        <div class="film-poster">
            <img class="film-poster-img lazyload" data-src="'.$a['poster'].'" src="'.$websiteUrl.'/files/images/no_poster.jpg" alt="'.$a['name'].'">
            <a class="film-poster-ahref" href="/info/'.$a['id'].'" title="'.$a['name'].'" data-jname="'.$a['jname'].'">
                <i class="fas fa-play"></i>
            </a>
        </div>
        <div class="film-detail">
            <h3 class="film-name"><a href="/info/'.$a['id'].'">'.$a['name'].'</a></h3>
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <title><?=$websiteTitle?> - Watch Anime Online Free</title>

    <!-- YOUR ORIGINAL SEO UNCHANGED -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="<?=$websiteTitle?> - Official <?=$websiteTitle?>">
    <meta name="description" content="<?=$websiteTitle?> - Watch Anime Free HD No Ads">
    <meta name="keywords" content="<?=$websiteTitle?>, watch anime online, anime hd free">
    <meta name="charset" content="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:image" content="<?=$banner?>">

    <!-- YOUR CSS EXACTLY AS YOU USE -->
    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/style.css?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/min.css?v=<?=$version?>">
</head>

<body data-page="page_home">
    <?php include('./_php/header.php'); ?>

    <!-- SLIDER / SPOTLIGHT -->
    <div class="deslide-wrap">
        <div class="container" style="width:100%!important">
            <div id="slider" class="swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach($home['spotlightAnimes'] as $a): ?>
                    <div class="swiper-slide">
                        <div class="deslide-item">
                            <img src="<?=$a['poster']?>" class="deslide-cover">
                            <div class="deslide-detail">
                                <h3><?=$a['name']?></h3>
                                <a href="/info/<?=$a['id']?>" class="btn btn-primary">Watch Now</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <!-- TRENDING -->
    <section class="block_area block_area_home">
        <div class="block_area-header">
            <h2 class="cat-heading">Trending</h2>
        </div>
        <div class="block_area-content block_area-list film_list film_list-grid">
            <div class="film_list-wrap">
                <?php foreach($home['trendingAnimes'] as $a) echo card($a, $websiteUrl); ?>
            </div>
        </div>
    </section>

    <!-- LATEST EPISODES -->
    <section class="block_area block_area_home">
        <div class="block_area-header">
            <h2 class="cat-heading">Latest Episodes</h2>
        </div>
        <div class="film_list-wrap">
            <?php foreach($home['latestEpisodeAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- TOP UPCOMING -->
    <section class="block_area block_area_home">
        <div class="block_area-header"><h2 class="cat-heading">Top Upcoming</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['topUpcomingAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- TOP 10 -->
    <section class="block_area block_area_home">
        <div class="block_area-header"><h2 class="cat-heading">Top 10</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['top10Animes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- TOP AIRING -->
    <section class="block_area block_area_home">
        <div class="block_area-header"><h2 class="cat-heading">Top Airing</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['topAiringAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- MOST POPULAR -->
    <section class="block_area">
        <div class="block_area-header"><h2 class="cat-heading">Most Popular</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['mostPopularAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- MOST FAVORITE -->
    <section class="block_area">
        <div class="block_area-header"><h2 class="cat-heading">Most Favorite</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['mostFavoriteAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- COMPLETED -->
    <section class="block_area">
        <div class="block_area-header"><h2 class="cat-heading">Latest Completed</h2></div>
        <div class="film_list-wrap">
            <?php foreach($home['latestCompletedAnimes'] as $a) echo card($a, $websiteUrl); ?>
        </div>
    </section>

    <!-- GENRES -->
    <section class="block_area">
        <div class="block_area-header"><h2 class="cat-heading">Genres</h2></div>
        <ul class="genre-list">
            <?php foreach($home['genres'] as $g): ?>
                <li><a href="/genre/<?=$g['id']?>"><?=$g['name']?></a></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php include('./_php/footer.php'); ?>

    <!-- YOUR JS EXACTLY AS BEFORE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?=$websiteUrl?>/files/js/app.js"></script>
    <script src="<?=$websiteUrl?>/files/js/comman.js"></script>
    <script src="<?=$websiteUrl?>/files/js/movie.js"></script>
    <script src="<?=$websiteUrl?>/files/js/function.js"></script>
</body>
</html>
