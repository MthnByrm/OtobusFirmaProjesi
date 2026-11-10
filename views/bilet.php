<section class="kart bilet">
    <div class="bolum-baslik">
        <div><p class="ust-etiket">3 / 3 · ELEKTRONİK BİLET</p><h1><?= e($bilet['etap']['nereden']) ?> → <?= e($bilet['etap']['nereye']) ?></h1></div>
        <span class="rozet"><?= $bilet['durum'] === 'aktif' ? 'Aktif' : 'İptal edildi' ?></span>
    </div>
    <div class="pnr"><span>PNR kodu</span><strong><?= e($bilet['pnr']) ?></strong></div>
    <dl class="bilet-bilgileri">
        <div><dt>Yolcu</dt><dd><?= e($bilet['yolcu']) ?></dd></div>
        <div><dt>T.C. kimlik numarası</dt><dd><?= e($bilet['maskeli_tc']) ?></dd></div>
        <div><dt>Kalkış</dt><dd><?= e(date('d.m.Y H:i', $bilet['etap']['kalkis'])) ?></dd></div>
        <div><dt>Varış</dt><dd><?= e(date('d.m.Y H:i', $bilet['etap']['varis'])) ?></dd></div>
        <div><dt>Koltuk</dt><dd><?= e($bilet['koltuk']) ?></dd></div>
        <div><dt>Bilet tutarı</dt><dd><?= e(tl($bilet['ucret'])) ?></dd></div>
        <?php if ($bilet['durum'] === 'iptal'): ?><div><dt>Demo iade tutarı</dt><dd><?= e(tl($bilet['iade'])) ?></dd></div><?php endif ?>
    </dl>
    <p class="yardim">PHP Programlama projesi için oluşturulmuş demo bileti.</p>
    <div class="bilet-aksiyon">
        <button class="dugme ikincil" type="button" data-yazdir>Yazdır</button>
        <?php if ($bilet['iade_teklifi'] !== null): ?>
            <details>
                <summary>Bileti iptal et</summary>
                <p>Şimdi iptal ederseniz demo iade tutarı <strong><?= e(tl($bilet['iade_teklifi']['tutar'])) ?></strong> (%<?= e($bilet['iade_teklifi']['oran']) ?>).</p>
                <form method="post" action="<?= url('bilet-iptal') ?>">
                    <?php csrfAlani() ?>
                    <input type="hidden" name="id" value="<?= e($bilet['id']) ?>">
                    <button class="dugme tehlike">İptali onayla</button>
                </form>
            </details>
        <?php endif ?>
    </div>
</section>
