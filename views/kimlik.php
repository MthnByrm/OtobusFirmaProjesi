<section class="kart dar">
    <p class="ust-etiket">YOLCU HESABI</p>
    <h1><?= $kayit ? 'Hesap oluştur' : 'Giriş yap' ?></h1>
    <p><?= $kayit ? 'Koltuk ayırmak ve biletlerini görmek için yolcu hesabı oluştur.' : 'Koltuk seçimine ve biletlerine hesabından ulaş.' ?></p>
    <form class="form-dikey" method="post" action="<?= url($kayit ? 'kayit-ol' : 'giris-yap') ?>">
        <?php csrfAlani() ?>
        <?php foreach ($donus as $ad => $deger): ?>
            <input type="hidden" name="<?= e($ad) ?>" value="<?= e($deger) ?>">
        <?php endforeach ?>
        <?php if ($kayit): ?><label>Ad soyad<input name="ad" autocomplete="name" maxlength="100" minlength="3" required></label><?php endif ?>
        <label>E-posta<input type="email" name="eposta" autocomplete="email" maxlength="160" required></label>
        <label>Parola<input type="password" name="parola" autocomplete="<?= $kayit ? 'new-password' : 'current-password' ?>" maxlength="72" <?= $kayit ? 'minlength="8"' : '' ?> required></label>
        <button class="dugme"><?= $kayit ? 'Hesap oluştur' : 'Giriş yap' ?></button>
    </form>
    <p><a href="<?= url($kayit ? 'giris' : 'kayit', $donus) ?>"><?= $kayit ? 'Hesabım var, giriş yap' : 'Yeni hesap oluştur' ?></a></p>
</section>
