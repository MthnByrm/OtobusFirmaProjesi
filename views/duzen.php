<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($baslik) ?> | OtobüsBilet</title>
    <link rel="stylesheet" href="assets/stil.css">
    <script src="assets/uygulama.js" defer></script>
</head>
<body>
<a class="skip" href="#icerik">İçeriğe geç</a>
<header class="ust">
    <div class="cerceve ust-ic">
        <a class="marka" href="<?= url('anasayfa') ?>">
            <span class="marka-isaret" aria-hidden="true">OB</span> Otobüs<span>Bilet</span>
        </a>
        <nav aria-label="Ana menü">
            <a href="<?= url('anasayfa') ?>">Sefer ara</a>
            <a href="<?= url('sorgu') ?>">Bilet sorgula</a>
            <?php if ($kullanici): ?>
                <a href="<?= url('biletlerim') ?>">Biletlerim</a>
                <?php if ($kullanici['rol'] === 'yonetici'): ?>
                    <a href="<?= url('yonetim') ?>">Yönetim</a>
                <?php endif ?>
                <form method="post" action="<?= url('cikis') ?>">
                    <?php csrfAlani() ?>
                    <button class="baglanti" title="<?= e($kullanici['ad']) ?>">Çıkış</button>
                </form>
            <?php else: ?>
                <a class="nav-giris" href="<?= url('giris') ?>">Giriş yap</a>
            <?php endif ?>
        </nav>
    </div>
</header>
<main class="cerceve ana" id="icerik">
    <?php if ($mesaj !== null): ?><div class="bildirim" role="status"><?= e($mesaj) ?></div><?php endif ?>
    <?php require $dosya ?>
</main>
<footer class="alt">
    <div class="cerceve alt-ic">
        <span class="footer-marka">OtobüsBilet</span>
        <span>PHP Programlama · Aşama 2 · Demo uygulama</span>
    </div>
</footer>
</body>
</html>
