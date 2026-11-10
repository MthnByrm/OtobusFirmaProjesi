<section class="sayfa-basligi">
    <p class="ust-etiket">1 / 3 · KOLTUK SEÇİMİ</p>
    <h1><?= e($etap['nereden']) ?> → <?= e($etap['nereye']) ?></h1>
    <p><?= e(date('d.m.Y · H:i', $etap['kalkis'])) ?> kalkış · <?= e($etap['duzen']) ?> düzen · <?= e(tl($etap['ucret'])) ?> / yolcu</p>
</section>
<section class="koltuk-yerlesim" data-harita
         data-durum="<?= url('koltuk-durum', $etapIstegi) ?>"
         data-kilitle="<?= url('koltuk-kilitle') ?>" data-birak="<?= url('koltuk-birak') ?>"
         data-csrf="<?= e(\App\Support\Oturum::csrf()) ?>"
         data-sefer="<?= e($etapIstegi['sefer']) ?>" data-binis="<?= e($etapIstegi['binis']) ?>" data-inis="<?= e($etapIstegi['inis']) ?>"
         data-acik="<?= $kullanici !== null && $etap['kalkis'] > $simdi ? '1' : '0' ?>">
    <div class="kart">
        <div class="bolum-baslik"><h2>Koltuk planı</h2><span class="rozet"><?= e($etap['kapasite']) ?> koltuk</span></div>
        <div class="lejant"><span><i class="bos-koltuk"></i> Boş</span><span><i class="secili"></i> Seçili</span><span><i class="dolu"></i> Dolu</span><span><i class="kilitli"></i> Kilitli</span></div>
        <div class="otobus" aria-label="Otobüs koltuk planı">
            <div class="surucu">Sürücü</div>
            <?php $siraKoltuk = $etap['duzen'] === '2+1' ? 3 : 4 ?>
            <?php for ($ilk = 1; $ilk <= $etap['kapasite']; $ilk += $siraKoltuk): ?>
                <div class="koltuk-sirasi">
                    <?php for ($sira = 0; $sira < $siraKoltuk; $sira++): ?>
                        <?php if ($sira === 2): ?><span class="koridor" aria-hidden="true"></span><?php endif ?>
                        <?php $numara = $ilk + $sira; $durum = $harita[$numara] ?? 'bos-koltuk'; ?>
                        <button type="button" data-koltuk="<?= e($numara) ?>" class="koltuk <?= e($durum) ?>"
                                aria-label="<?= e($numara) ?> numaralı koltuk"
                                <?= $kullanici === null || $etap['kalkis'] <= $simdi || in_array($durum, ['dolu', 'kilitli'], true) ? 'disabled' : '' ?>><?= e($numara) ?></button>
                    <?php endfor ?>
                </div>
            <?php endfor ?>
        </div>
    </div>
    <aside class="kart koltuk-ozet">
        <h2>Yolculuk özeti</h2>
        <p>Seçimleriniz 10 dakikaya kadar tutulur. Bir işlemde en fazla 5 koltuk seçebilirsiniz.</p>
        <?php if ($kullanici === null): ?>
            <a class="dugme" href="<?= url('giris', $etapIstegi) ?>">Koltuk seçmek için giriş yap</a>
        <?php elseif ($etap['kalkis'] <= $simdi): ?>
            <p class="bildirim">Bu duraktan kalkış zamanı geçti.</p>
        <?php else: ?>
            <label>Seçeceğiniz koltuğun yolcusu
                <select data-cinsiyet><option value="">Cinsiyet seçin</option><option value="K">Kadın</option><option value="E">Erkek</option></select>
            </label>
            <p class="durum-metin" data-secimler>Seçili koltuklar yükleniyor.</p>
            <p class="geri-sayim" data-sayac hidden></p>
            <p class="hata-metin" data-koltuk-hata role="status"></p>
            <a class="dugme" data-devam href="<?= url('odeme', $etapIstegi) ?>" <?= $secimler === [] ? 'hidden' : '' ?>>Yolcu bilgilerine geç</a>
            <noscript><p>Koltuk seçimi için tarayıcınızda JavaScript’i etkinleştirin.</p></noscript>
        <?php endif ?>
    </aside>
</section>
