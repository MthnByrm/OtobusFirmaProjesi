<?php
declare(strict_types=1);

use App\Repository\Json\JsonIslemYoneticisi;
use App\Repository\KayitDeposuInterface;
use App\Service\BiletService;
use App\Service\EtapCakisma;
use App\Service\Iade\{IadeHesaplayici, IadeYokPolitikasi, TamIadePolitikasi, YarimIadePolitikasi};
use App\Service\KoltukKilitService;
use App\Service\KoltukPlani;
use App\Service\OdemeService;
use App\Service\RaporService;
use App\Service\SatinAlmaService;
use App\Service\SeferAramaService;
use App\Service\TcKimlikDogrulayici;
use App\Service\YonetimService;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CekirdekTest extends TestCase
{
    private string $klasor;
    private SahteSaat $saat;
    private JsonIslemYoneticisi $islemler;
    private KoltukKilitService $koltuklar;
    private IadeHesaplayici $iadeler;
    private SatinAlmaService $satis;
    private BiletService $biletler;

    protected function setUp(): void
    {
        $this->klasor = sys_get_temp_dir() . '/otobusbilet-test-' . bin2hex(random_bytes(8));
        mkdir($this->klasor, 0700);
        copy(dirname(__DIR__) . '/storage/ornek-veriler.json', $this->klasor . '/ornek-veriler.json');
        $this->saat = new SahteSaat((new DateTimeImmutable('2026-11-10 08:00'))->getTimestamp());
        $this->islemler = new JsonIslemYoneticisi($this->klasor, $this->saat);
        $this->koltuklar = new KoltukKilitService($this->islemler, $this->saat);
        $this->iadeler = new IadeHesaplayici([new TamIadePolitikasi(), new YarimIadePolitikasi(), new IadeYokPolitikasi()]);
        $this->satis = new SatinAlmaService($this->islemler, $this->saat, new OdemeService());
        $this->biletler = new BiletService($this->islemler, $this->saat, $this->iadeler);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->klasor . '/*') ?: [] as $dosya) {
            unlink($dosya);
        }
        rmdir($this->klasor);
    }

    public function testBitisikEtaplarCakismazAmaKesisenlerCakisir(): void
    {
        self::assertFalse(EtapCakisma::varMi(0, 2, 2, 4));
        self::assertTrue(EtapCakisma::varMi(0, 3, 2, 4));
        self::assertTrue(EtapCakisma::varMi(1, 2, 0, 4));
    }

    public function testKoltukKomsuluguKoridoruGecmez(): void
    {
        self::assertSame(2, KoltukPlani::komsu('2+1', 1));
        self::assertNull(KoltukPlani::komsu('2+1', 3));
        self::assertSame(4, KoltukPlani::komsu('2+2', 3));
        self::assertSame(1, KoltukPlani::komsu('2+2', 2));
    }

    public function testAyniEtaptaBaskaKullaniciKoltuguKilitleyemez(): void
    {
        $this->koltuklar->kilitle(2, 1, 0, 2, 10, 'K');
        self::assertSame('kilitli', $this->koltuklar->durum(3, 1, 0, 2)['harita'][10]);
        $this->expectExceptionCode(409);
        $this->koltuklar->kilitle(3, 1, 0, 2, 10, 'K');
    }

    public function testKoltukBitisikEtaptaTekrarAyrilabilir(): void
    {
        $this->koltuklar->kilitle(2, 1, 0, 2, 10, 'K');
        $this->koltuklar->kilitle(3, 1, 2, 4, 10, 'E');
        self::assertSame('secili', $this->koltuklar->durum(3, 1, 2, 4)['harita'][10]);
    }

    public function testKilitTamOnDakikaDoluncaKalkar(): void
    {
        $this->koltuklar->kilitle(2, 1, 0, 2, 10, 'K');
        $this->saat->zaman += 599;
        self::assertSame('kilitli', $this->koltuklar->durum(3, 1, 0, 2)['harita'][10]);
        $this->saat->zaman++;
        self::assertArrayNotHasKey(10, $this->koltuklar->durum(3, 1, 0, 2)['harita']);
        $this->koltuklar->kilitle(3, 1, 0, 2, 10, 'E');
        self::assertSame('secili', $this->koltuklar->durum(3, 1, 0, 2)['harita'][10]);
    }

    public function testKarsiCinsiyetKomsuKoltuktaEngellenir(): void
    {
        $this->expectExceptionCode(409);
        $this->koltuklar->kilitle(3, 1, 0, 2, 2, 'E');
    }

    public function testBirIslemdeAltinciKoltukReddedilir(): void
    {
        foreach ([7, 8, 9, 10, 11] as $koltuk) {
            $this->koltuklar->kilitle(2, 1, 0, 2, $koltuk, 'K');
        }
        $this->expectExceptionMessage('en fazla 5');
        $this->koltuklar->kilitle(2, 1, 0, 2, 12, 'K');
    }

    public function testSuresiDolanKoltukSatilmaz(): void
    {
        $this->koltuklar->kilitle(2, 1, 0, 2, 10, 'K');
        $this->saat->zaman += 600;
        $this->expectExceptionCode(409);
        $this->satis->satinAl(2, 1, 0, 2, [10 => ['ad' => 'Demo Test', 'tc' => '12345678950']], 'onay');
    }

    public function testOdemeReddiArdindanTekrarDenemeVeSatis(): void
    {
        $this->koltuklar->kilitle(2, 1, 0, 2, 10, 'K');
        $yolcular = [10 => ['ad' => 'Demo Test', 'tc' => '12345678950']];
        try {
            $this->satis->satinAl(2, 1, 0, 2, $yolcular, 'ret');
            self::fail('Reddedilen ödeme işlemden geçmemeliydi.');
        } catch (UygulamaHatasi $hata) {
            self::assertStringContainsString('reddedildi', $hata->getMessage());
        }
        self::assertSame('secili', $this->koltuklar->durum(2, 1, 0, 2)['harita'][10]);
        self::assertCount(12, $this->biletler->biletlerim(2));
        $siparis = $this->satis->satinAl(2, 1, 0, 2, $yolcular, 'onay');
        self::assertSame('dolu', $this->koltuklar->durum(2, 1, 0, 2)['harita'][10]);
        [$bilet] = $this->biletler->siparis($siparis, 2);
        self::assertMatchesRegularExpression('/^[A-F0-9]{10}$/', $bilet['pnr']);
        self::assertSame($bilet['id'], $this->biletler->sorgula($bilet['pnr'], '12345678950'));
        self::assertSame('*********50', $bilet['maskeli_tc']);
    }

    public function testYaridaKesilenIslemKaliciKayitBirakmaz(): void
    {
        try {
            $this->islemler->calistir(function (KayitDeposuInterface $depo): void {
                $depo->ekle('siparisler', ['kullanici_id' => 2, 'zaman' => 0]);
                throw new RuntimeException('deneme');
            });
            self::fail('İşlem hata vermeliydi.');
        } catch (RuntimeException $hata) {
            self::assertSame('deneme', $hata->getMessage());
        }
        self::assertCount(12, $this->islemler->oku(fn (KayitDeposuInterface $depo): array => $depo->tumu('siparisler')));
    }

    public function testBiletIptaliRaporVeHaritayiBirlikteGunceller(): void
    {
        $rapor = new RaporService($this->islemler);
        $once = $rapor->rapor()['toplam'];
        $this->biletler->iptalEt(1, 2, false);
        $this->biletler->iptalEt(1, 2, false);
        $sonra = $rapor->rapor()['toplam'];
        self::assertSame($once['net'] - 69000, $sonra['net']);
        self::assertSame($once['bilet'] - 1, $sonra['bilet']);
        self::assertArrayNotHasKey(1, $this->koltuklar->durum(2, 1, 0, 2)['harita']);
        self::assertSame('dolu', $this->koltuklar->durum(2, 1, 2, 4)['harita'][1]);
    }

    public function testYetkisizKisiBiletiAcamaz(): void
    {
        $this->expectExceptionCode(404);
        $this->biletler->bilet(1, 3, false);
    }

    public function testAramaAraDuraktakiGercekKalkisaBakar(): void
    {
        $arama = new SeferAramaService($this->islemler, $this->saat);
        $seferler = $arama->ara('Ankara', 'Sivas', new DateTimeImmutable('2026-11-12'));
        $gecedenGelen = array_column($seferler, null, 'sefer_id')[81];
        self::assertSame(2, $gecedenGelen['binis']);
        self::assertSame('04:25', date('H:i', $gecedenGelen['kalkis']));
        self::assertSame(40000, $gecedenGelen['ucret']);
    }

    public function testYonetimCakisanOtobusAtamasiniEngeller(): void
    {
        $yonetim = new YonetimService($this->islemler, $this->saat);
        $this->expectExceptionCode(409);
        $yonetim->sefer(0, 2, 1, new DateTimeImmutable('2026-11-11 09:00'));
    }

    public function testTcNumarasiHaneleriDogrulanir(): void
    {
        self::assertTrue(TcKimlikDogrulayici::gecerli('12345678950'));
        self::assertFalse(TcKimlikDogrulayici::gecerli('12345678951'));
        self::assertFalse(TcKimlikDogrulayici::gecerli('00000000000'));
    }

    #[DataProvider('iadeSinirlari')]
    public function testIadeSaatSinirlari(int $saniye, int $oran): void
    {
        self::assertSame($oran, $this->iadeler->hesapla(10000, $saniye)['oran']);
    }

    public static function iadeSinirlari(): array
    {
        return [
            '24 saat 1 dakika' => [86460, 100],
            '24 saat' => [86400, 50],
            '6 saat' => [21600, 50],
            '5 saat 59 dakika' => [21540, 0],
        ];
    }

    public function testGecmisKalkisIptalEdilemez(): void
    {
        $this->expectExceptionCode(409);
        $this->iadeler->hesapla(10000, -1);
    }
}

final class SahteSaat implements SaatInterface
{
    public function __construct(public int $zaman)
    {
    }

    public function simdi(): int
    {
        return $this->zaman;
    }
}
