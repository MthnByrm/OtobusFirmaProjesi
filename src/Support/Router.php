<?php
declare(strict_types=1);

namespace App\Support;

final class Router
{
    /**
     * @param array<string, array{0: string, 1: callable, 2?: string}> $rotalar
     */
    public function __construct(private array $rotalar)
    {
    }

    public function calistir(string $sayfa, string $metot, array $girdi): void
    {
        $json = in_array($sayfa, ['koltuk-durum', 'koltuk-kilitle', 'koltuk-birak'], true);
        try {
            if (!isset($this->rotalar[$sayfa])) {
                throw new UygulamaHatasi('Aradığınız sayfa bulunamadı.', 404);
            }
            [$beklenenMetot, $eylem] = $this->rotalar[$sayfa];
            if ($metot !== $beklenenMetot) {
                header('Allow: ' . $beklenenMetot);
                throw new UygulamaHatasi('Bu istek yöntemi desteklenmiyor.', 405);
            }
            if ($metot === 'POST' && !hash_equals(Oturum::csrf(), Girdi::metin($girdi['csrf'] ?? null))) {
                throw new UygulamaHatasi('Oturum doğrulanamadı. Sayfayı yenileyip tekrar deneyin.', 403);
            }
            $rol = $this->rotalar[$sayfa][2] ?? '';
            if ($rol === 'oturum' && Oturum::kimlik() === 0) {
                throw new UygulamaHatasi('Bu işlem için giriş yapın.', 401);
            }
            if ($rol === 'yonetici' && (Oturum::kullanici()['rol'] ?? null) !== 'yonetici') {
                throw new UygulamaHatasi('Bu sayfa yalnızca yöneticilere açık.', 403);
            }
            $eylem($girdi);
        } catch (UygulamaHatasi $hata) {
            http_response_code($hata->getCode());
            $json ? Yanit::json(['hata' => $hata->getMessage()]) :
                Yanit::gorunum('hata', 'İşlem tamamlanamadı', ['hata' => $hata->getMessage()]);
        } catch (\Throwable $hata) {
            error_log('OtobüsBilet: ' . get_class($hata) . ' (' . basename($hata->getFile()) . ':' . $hata->getLine() . ')');
            http_response_code(500);
            $mesaj = 'Beklenmedik bir sorun oluştu. Kurulumdaki veri klasörünün yazılabilir olduğunu kontrol edin.';
            $json ? Yanit::json(['hata' => $mesaj]) : Yanit::gorunum('hata', 'Sistem hatası', ['hata' => $mesaj]);
        }
    }
}
