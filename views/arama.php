<section class="hero">
    <p class="ust-etiket">ŞEHİRLERARASI OTOBÜS BİLETİ</p>
    <h1>Bir sonraki durağın<br>neresi?</h1>
    <p>Güzergâhını seç, seferleri karşılaştır ve koltuğunu ayır.</p>
    <form class="arama-form" method="get" action="">
        <input type="hidden" name="sayfa" value="sefer-ara">
        <label>Nereden
            <select name="nereden" required>
                <option value="">Kalkış şehri</option>
                <?php foreach ($sehirler as $sehir): ?>
                    <option value="<?= e($sehir) ?>" <?= $nereden === $sehir ? 'selected' : '' ?>><?= e($sehir) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <button type="button" class="degistir" data-degistir aria-label="Kalkış ve varış şehirlerini değiştir" title="Şehirleri değiştir">⇄</button>
        <label>Nereye
            <select name="nereye" required>
                <option value="">Varış şehri</option>
                <?php foreach ($sehirler as $sehir): ?>
                    <option value="<?= e($sehir) ?>" <?= $nereye === $sehir ? 'selected' : '' ?>><?= e($sehir) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <label>Yolculuk tarihi<input name="tarih" type="date" value="<?= e($tarih) ?>" min="<?= e($bugun) ?>" required></label>
        <button class="dugme" type="submit">Seferleri bul</button>
    </form>
</section>

<?php if ($sonuclar === null): ?>
    <section class="bolum">
        <div class="bolum-baslik">
            <div><p class="ust-etiket">ÖRNEK GÜZERGÂHLAR</p><h2>Yolculuğunu planla</h2></div>
        </div>
        <div class="rota-kartlari">
            <?php foreach ([['İstanbul', 'Sivas', 'Ankara üzerinden'], ['Ankara', 'Trabzon', 'Gümüşhane üzerinden'], ['Hatay', 'Ankara', 'Adana üzerinden']] as [$ilk, $son, $ara]): ?>
                <a class="rota-karti" href="<?= url('sefer-ara', ['nereden' => $ilk, 'nereye' => $son, 'tarih' => $tarih]) ?>">
                    <span><?= e($ara) ?></span><strong><?= e($ilk) ?> <b aria-hidden="true">→</b> <?= e($son) ?></strong>
                    <small>Seferleri gör <span aria-hidden="true">↗</span></small>
                </a>
            <?php endforeach ?>
        </div>
    </section>
<?php else: ?>
    <section class="bolum">
        <div class="bolum-baslik">
            <div><p class="ust-etiket">ARAMA SONUÇLARI</p><h2><?= e($nereden) ?> → <?= e($nereye) ?></h2></div>
            <span class="rozet"><?= count($sonuclar) ?> sefer</span>
        </div>
        <?php if ($sonuclar === []): ?>
            <div class="kart bos"><h3>Bu tarihte sefer bulunamadı</h3><p>Arama alanından farklı bir tarih veya güzergâh seçebilirsiniz.</p></div>
        <?php endif ?>
        <div class="sonuclar">
        <?php foreach ($sonuclar as $sefer): ?>
            <article class="kart sefer-karti">
                <div class="zaman-cizgisi">
                    <div><strong><?= e(date('H:i', $sefer['kalkis'])) ?></strong><span><?= e($sefer['nereden']) ?></span></div>
                    <span class="hat" aria-hidden="true"></span>
                    <div><strong><?= e(date('H:i', $sefer['varis'])) ?></strong><span><?= e($sefer['nereye']) ?></span></div>
                </div>
                <div class="sefer-meta">
                    <span class="rozet"><?= e($sefer['duzen']) ?> otobüs</span>
                    <small><?= e(implode(' → ', array_column($sefer['duraklar'], 'sehir'))) ?></small>
                    <small>Kalkış <?= e(date('d.m.Y', $sefer['kalkis'])) ?> · Varış <?= e(date('d.m.Y', $sefer['varis'])) ?></small>
                </div>
                <div class="sefer-fiyat">
                    <strong><?= e(tl($sefer['ucret'])) ?></strong>
                    <a class="dugme" href="<?= url('koltuklar', ['sefer' => $sefer['sefer_id'], 'binis' => $sefer['binis'], 'inis' => $sefer['inis']]) ?>">Koltuk seç</a>
                </div>
            </article>
        <?php endforeach ?>
        </div>
    </section>
<?php endif ?>
