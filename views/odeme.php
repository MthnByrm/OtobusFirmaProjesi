<section class="sayfa-basligi">
    <p class="ust-etiket">2 / 3 · YOLCU BİLGİSİ VE ÖDEME</p>
    <h1><?= e($etap['nereden']) ?> → <?= e($etap['nereye']) ?></h1>
    <p><?= e(date('d.m.Y · H:i', $etap['kalkis'])) ?> · <a href="<?= url('koltuklar', $etapIstegi) ?>">Koltuk haritasına dön</a></p>
</section>
<form class="odeme-yerlesim" method="post" action="<?= url('odeme-yap') ?>">
    <?php csrfAlani() ?>
    <?php foreach ($etapIstegi as $ad => $deger): ?>
        <input type="hidden" name="<?= e($ad) ?>" value="<?= e($deger) ?>">
    <?php endforeach ?>
    <div>
        <p class="bildirim">Örnek yolcu bilgileri kullanın. Demo kimlik numaraları <strong>12345678950</strong>, <strong>23456789060</strong>, <strong>34567890170</strong>, <strong>45678901280</strong>, <strong>56789012390</strong>.</p>
        <?php foreach ($secimler as $secim): ?>
            <fieldset class="kart form-dikey yolcu">
                <legend class="rozet">Koltuk <?= e($secim['koltuk']) ?> · <?= $secim['cinsiyet'] === 'K' ? 'Kadın' : 'Erkek' ?></legend>
                <label>Yolcu ad soyad<input name="yolcular[<?= e($secim['koltuk']) ?>][ad]" minlength="3" maxlength="100" required></label>
                <label>Demo T.C. kimlik numarası<input name="yolcular[<?= e($secim['koltuk']) ?>][tc]" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" autocomplete="off" required></label>
            </fieldset>
        <?php endforeach ?>
    </div>
    <aside class="kart koltuk-ozet">
        <h2>Demo kartla ödeme</h2>
        <p class="demo-kart"><span>DEMO KART</span><strong>•••• •••• •••• 4242</strong></p>
        <p>Ödeme senaryosunu seçerek başarılı veya reddedilen işlemi deneyin.</p>
        <p class="geri-sayim" data-kalan="<?= e($bitis - $simdi) ?>" role="timer"></p>
        <label>Ödeme senaryosu
            <select name="odeme"><option value="onay">Onaylanan demo ödeme</option><option value="ret">Reddedilen demo ödeme</option></select>
        </label>
        <p class="toplam"><span>Toplam tutar</span><strong><?= e(tl($toplam)) ?></strong></p>
        <button class="dugme" data-odeme>Demo ödemeyi tamamla</button>
    </aside>
</form>
