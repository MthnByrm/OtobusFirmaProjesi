<?php
declare(strict_types=1);

use App\Controller\{BiletController, KimlikController, KoltukController, SatinAlmaController, SeferAramaController, YonetimController};
use App\Repository\Json\JsonIslemYoneticisi;
use App\Service\Iade\{IadeHesaplayici, IadeYokPolitikasi, TamIadePolitikasi, YarimIadePolitikasi};
use App\Service\{BiletService, KimlikService, KoltukKilitService, OdemeService, RaporService, SatinAlmaService, SeferAramaService, YonetimService};
use App\Support\{Router, SistemSaati};

require __DIR__ . '/autoload.php';

$saat = new SistemSaati();
$islemler = new JsonIslemYoneticisi(__DIR__ . '/storage', $saat);
$koltukServisi = new KoltukKilitService($islemler, $saat);
$iadeler = new IadeHesaplayici([new TamIadePolitikasi(), new YarimIadePolitikasi(), new IadeYokPolitikasi()]);
$kimlik = new KimlikController(new KimlikService($islemler));
$arama = new SeferAramaController(new SeferAramaService($islemler, $saat));
$koltuklar = new KoltukController($koltukServisi);
$odeme = new SatinAlmaController($koltukServisi, new SatinAlmaService($islemler, $saat, new OdemeService()));
$bilet = new BiletController(new BiletService($islemler, $saat, $iadeler));
$yonetim = new YonetimController(new YonetimService($islemler, $saat), new RaporService($islemler));

return new Router([
    'anasayfa' => ['GET', $arama->form(...)],
    'sefer-ara' => ['GET', $arama->ara(...)],
    'giris' => ['GET', $kimlik->girisFormu(...)],
    'giris-yap' => ['POST', $kimlik->giris(...)],
    'kayit' => ['GET', $kimlik->kayitFormu(...)],
    'kayit-ol' => ['POST', $kimlik->kaydet(...)],
    'cikis' => ['POST', $kimlik->cikis(...)],
    'koltuklar' => ['GET', $koltuklar->harita(...)],
    'koltuk-durum' => ['GET', $koltuklar->durum(...)],
    'koltuk-kilitle' => ['POST', $koltuklar->kilitle(...), 'oturum'],
    'koltuk-birak' => ['POST', $koltuklar->birak(...), 'oturum'],
    'odeme' => ['GET', $odeme->form(...), 'oturum'],
    'odeme-yap' => ['POST', $odeme->ode(...), 'oturum'],
    'siparis' => ['GET', $bilet->siparis(...), 'oturum'],
    'biletlerim' => ['GET', $bilet->biletlerim(...), 'oturum'],
    'sorgu' => ['GET', $bilet->sorguFormu(...)],
    'bilet-sorgula' => ['POST', $bilet->sorgula(...)],
    'bilet' => ['GET', $bilet->goster(...)],
    'bilet-iptal' => ['POST', $bilet->iptal(...)],
    'yonetim' => ['GET', $yonetim->goster(...), 'yonetici'],
    'yonetim-kaydet' => ['POST', $yonetim->kaydet(...), 'yonetici'],
]);
