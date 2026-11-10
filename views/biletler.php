<section class="sayfa-basligi">
    <p class="ust-etiket">BİLET İŞLEMLERİ</p>
    <h1><?= e($baslik) ?></h1>
</section>
<?php if ($biletler === []): ?>
    <div class="kart bos"><p>Hesabınızda görüntülenecek bilet bulunmuyor.</p><a class="dugme" href="<?= url('anasayfa') ?>">Sefer ara</a></div>
<?php endif ?>
<div class="sonuclar">
<?php foreach ($biletler as $bilet): ?>
    <article class="kart sefer-karti">
        <div><h2><?= e($bilet['etap']['nereden']) ?> → <?= e($bilet['etap']['nereye']) ?></h2>
            <p><?= e($bilet['yolcu']) ?> · <?= e(date('d.m.Y H:i', $bilet['etap']['kalkis'])) ?> · Koltuk <?= e($bilet['koltuk']) ?></p>
        </div>
        <span class="rozet"><?= $bilet['durum'] === 'aktif' ? 'Aktif' : 'İptal edildi' ?> · <?= e($bilet['pnr']) ?></span>
        <a class="dugme ikincil" href="<?= url('bilet', ['id' => $bilet['id']]) ?>">Bilet detayı</a>
    </article>
<?php endforeach ?>
</div>
