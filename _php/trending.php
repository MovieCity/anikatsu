<?php
$trending = $home["trending"];
?>

<div class="swiper-wrapper">
<?php foreach ($trending as $index => $anime): ?>
    <div class="swiper-slide">
        <div class="item">
            <div class="number">
                <span><?= $index+1 ?></span>
                <div class="film-title dynamic-name" data-jname="<?= $anime['title'] ?>">
                    <?= $anime['title'] ?>
                </div>
            </div>

            <a href="/anime/<?= $anime['id'] ?>" class="film-poster" title="<?= $anime['title'] ?>">
                <img data-src="<?= $anime['poster'] ?>"
                     src="https://anikatsu.me/files/images/no_poster.jpg"
                     class="film-poster-img lazyload"
                     alt="<?= $anime['title'] ?>">
            </a>

            <div class="clearfix"></div>
        </div>
    </div>
<?php endforeach; ?>
</div>
