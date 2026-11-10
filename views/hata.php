<section class="kart dar">
    <p class="ust-etiket">İŞLEM BİLDİRİMİ</p>
    <h1><?= e($baslik) ?></h1>
    <p><?= e($hata) ?></p>
    <div class="eylemler">
        <a class="dugme" href="<?= url('anasayfa') ?>">Ana sayfaya dön</a>
        <button class="dugme ikincil" type="button" data-geri>Önceki sayfa</button>
    </div>
</section>
