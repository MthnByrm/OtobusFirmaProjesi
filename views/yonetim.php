<section class="sayfa-basligi">
    <p class="ust-etiket">YÖNETİCİ PANELİ</p>
    <h1><?= e($baslik) ?></h1>
    <nav class="sekmeler" aria-label="Yönetim bölümleri">
        <?php foreach ($bolumler as $anahtar => $ad): ?>
            <a href="<?= url('yonetim', ['bolum' => $anahtar]) ?>" <?= $bolum === $anahtar ? 'aria-current="page"' : '' ?>><?= e($ad) ?></a>
        <?php endforeach ?>
    </nav>
</section>
<?php if ($bolum === 'rapor'): ?>
    <div class="metrikler">
        <div class="kart"><span>Aktif bilet</span><strong><?= e($toplam['bilet']) ?></strong></div>
        <div class="kart"><span>Brüt gelir</span><strong><?= e(tl($toplam['brut'])) ?></strong></div>
        <div class="kart"><span>İade</span><strong><?= e(tl($toplam['iade'])) ?></strong></div>
        <div class="kart"><span>Net gelir</span><strong><?= e(tl($toplam['net'])) ?></strong></div>
    </div>
    <section class="kart bolum">
        <h2>Sefer raporu</h2>
        <p class="yardim">Doluluk, seferin en yoğun durak aralığındaki aktif koltuk sayısı üzerinden hesaplanır.</p>
        <div class="tablo-sarma">
            <table><thead><tr><th>Güzergâh</th><th>Kalkış</th><th>Aktif bilet</th><th>Doluluk</th><th>Brüt</th><th>İade</th><th>Net</th></tr></thead><tbody>
            <?php foreach ($satirlar as $satir): $etap = $satir['etap']; ?>
                <tr><td><?= e($etap['nereden']) ?> → <?= e($etap['nereye']) ?></td>
                    <td><?= e(date('d.m.Y H:i', $etap['kalkis'])) ?></td>
                    <td><?= e($satir['bilet']) ?></td>
                    <td><?= e($satir['dolu']) ?>/<?= e($etap['kapasite']) ?> (%<?= e($satir['oran']) ?>)</td>
                    <td><?= e(tl($satir['brut'])) ?></td><td><?= e(tl($satir['iade'])) ?></td><td><?= e(tl($satir['net'])) ?></td></tr>
            <?php endforeach ?>
            </tbody></table>
        </div>
    </section>
<?php else: ?>
    <?php $liste = ${$bolum}; $kayit = $liste[$duzenle] ?? [] ?>
    <div class="yonetim-yerlesim">
        <section class="kart">
            <div class="bolum-baslik"><h2><?= $duzenle ? 'Kaydı düzenle' : 'Yeni kayıt' ?></h2>
                <?php if ($duzenle): ?><a href="<?= url('yonetim', ['bolum' => $bolum]) ?>">Vazgeç</a><?php endif ?>
            </div>
            <form class="form-dikey" method="post" action="<?= url('yonetim-kaydet') ?>">
                <?php csrfAlani() ?>
                <input type="hidden" name="bolum" value="<?= e($bolum) ?>">
                <input type="hidden" name="id" value="<?= e($duzenle) ?>">
                <?php if ($bolum === 'guzergahlar'): ?>
                    <?php
                    $durakSatirlari = array_map(
                        fn (array $durak): string => $durak['sehir'] . ' | ' . $durak['dakika'] . ' | ' .
                            intdiv($durak['ucret'], 100) . ',' . str_pad((string) ($durak['ucret'] % 100), 2, '0', STR_PAD_LEFT),
                        $kayit['duraklar'] ?? []
                    );
                    ?>
                    <label>Güzergâh adı<input name="ad" value="<?= e($kayit['ad'] ?? '') ?>" required maxlength="100"></label>
                    <label>Duraklar
                        <textarea name="duraklar" rows="6" maxlength="3000" aria-describedby="durak-bicim" required><?= e(implode("\n", $durakSatirlari)) ?></textarea>
                    </label>
                    <small id="durak-bicim">Her satıra şehir, ilk duraktan itibaren dakika ve toplam ücret (TL) yazın.<br>Örnek<br>İstanbul | 0 | 0<br>Bolu | 220 | 550<br>Ankara | 360 | 820</small>
                <?php elseif ($bolum === 'otobusler'): ?>
                    <label>Plaka<input name="plaka" value="<?= e($kayit['plaka'] ?? '') ?>" placeholder="29 OB 100" maxlength="11" required></label>
                    <label>Koltuk düzeni
                        <select name="duzen">
                            <?php foreach (['2+1', '2+2'] as $duzen): ?>
                                <option <?= ($kayit['duzen'] ?? '') === $duzen ? 'selected' : '' ?>><?= e($duzen) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                    <label>Sıra sayısı<input name="sira" type="number" min="1" max="20" value="<?= e($kayit['sira'] ?? 10) ?>" required></label>
                <?php else: ?>
                    <label>Güzergâh<select name="guzergah" required>
                        <?php foreach ($guzergahlar as $rota): ?>
                            <option value="<?= e($rota['id']) ?>" <?= ($kayit['guzergah_id'] ?? null) === $rota['id'] ? 'selected' : '' ?>><?= e($rota['ad']) ?></option>
                        <?php endforeach ?>
                    </select></label>
                    <label>Otobüs<select name="otobus" required>
                        <?php foreach ($otobusler as $arac): ?>
                            <option value="<?= e($arac['id']) ?>" <?= ($kayit['otobus_id'] ?? null) === $arac['id'] ? 'selected' : '' ?>><?= e($arac['plaka']) ?> · <?= e($arac['duzen']) ?></option>
                        <?php endforeach ?>
                    </select></label>
                    <label>Kalkış tarihi ve saati<input name="kalkis" type="datetime-local" value="<?= isset($kayit['kalkis']) ? e(date('Y-m-d\TH:i', $kayit['kalkis'])) : '' ?>" required></label>
                <?php endif ?>
                <button class="dugme">Kaydet</button>
            </form>
        </section>
        <section class="kart">
            <h2>Kayıtlı <?= e($bolumler[$bolum]) ?></h2>
            <div class="tablo-sarma">
                <table><thead><tr><th>Kayıt</th><th>Ayrıntı</th><th>İşlem</th></tr></thead><tbody>
                <?php foreach ($liste as $satir): ?>
                    <tr>
                        <?php if ($bolum === 'guzergahlar'): ?>
                            <td><?= e($satir['ad']) ?></td><td><?= e(implode(' → ', array_column($satir['duraklar'], 'sehir'))) ?></td>
                        <?php elseif ($bolum === 'otobusler'): ?>
                            <td><?= e($satir['plaka']) ?></td><td><?= e($satir['duzen']) ?> · <?= e($satir['sira']) ?> sıra</td>
                        <?php else: ?>
                            <td><?= e($guzergahlar[$satir['guzergah_id']]['ad']) ?></td>
                            <td><?= e(date('d.m.Y H:i', $satir['kalkis'])) ?> · <?= e($otobusler[$satir['otobus_id']]['plaka']) ?></td>
                        <?php endif ?>
                        <td><a href="<?= url('yonetim', ['bolum' => $bolum, 'id' => $satir['id']]) ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach ?>
                </tbody></table>
            </div>
        </section>
    </div>
<?php endif ?>
