<section class="kart dar">
    <p class="ust-etiket">BİLET İŞLEMLERİ</p>
    <h1>Bilet sorgula</h1>
    <p>Biletteki PNR kodunu ve satın alırken kullanılan yolcu kimlik numarasını girin.</p>
    <form class="form-dikey" method="post" action="<?= url('bilet-sorgula') ?>">
        <?php csrfAlani() ?>
        <label>PNR kodu<input name="pnr" pattern="[A-Za-z0-9]{6,10}" maxlength="10" required></label>
        <label>Demo T.C. kimlik numarası<input name="tc" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" autocomplete="off" required></label>
        <button class="dugme">Bileti bul</button>
    </form>
    <p class="yardim">Örnek sorgu: <strong>DEMO01</strong> / <strong>12345678950</strong></p>
</section>
