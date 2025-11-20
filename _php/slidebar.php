<?php
$home = json_decode(file_get_contents("https://shifter-zeta.vercel.app/api/v2/hianime/home"), true);
$spotlight = $home["spotlight"];
?>

<div class="swiper-wrapper">

<?php foreach ($spotlight as $index => $item): ?>
    <div class="swiper-slide">
        <div class="deslide-item">
            <div class="deslide-cover">
                <div class="deslide-cover-img">
                    <img class="film-poster-img lazyload" 
                         data-src="<?= $item['poster'] ?>"
                         alt="<?= $item['title'] ?>">
                </div>
            </div>

            <div class="deslide-item-content">
                <div class="desi-sub-text">#<?= $index+1 ?> Spotlight</div>

                <div class="desi-head-title dynamic-name" 
                     data-jname="<?= $item['title'] ?>">
                     <?= $item['title'] ?>
                </div>

                <div class="sc-detail">
                    <div class="scd-item">
                        <i class="fas fa-play-circle mr-1"></i> 
                        <?= $item['type'] ?>
                    </div>

                    <div class="scd-item m-hide">
                        <i class="fas fa-calendar mr-1"></i>
                        <?= $item['year'] ?>
                    </div>

                    <div class="scd-item mr-1"><span class="quality">HD</span></div>

                    <div class="scd-item">
                        <?php if ($item['sub']): ?>
                            <span class="quality bg-white">SUB</span>
                        <?php endif; ?>

                        <?php if ($item['dub']): ?>
                            <span class="quality bg-white">DUB</span>
                        <?php endif; ?>
                    </div>

                    <div class="desi-description">
                        <?= $item['description'] ?>
                    </div>
                </div>

                <div class="desi-buttons">
                    <a href="/watch/<?= $item['slugWatch'] ?>" class="btn btn-primary btn-radius mr-2">
                        <i class="fas fa-play-circle mr-2"></i>Watch Now
                    </a>
                    <a href="/anime/<?= $item['slugDetail'] ?>" class="btn btn-secondary btn-radius">
                        <i class="fas fa-info-circle mr-2"></i>Detail<i class="fas fa-angle-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>
<?php endforeach; ?>

</div>
