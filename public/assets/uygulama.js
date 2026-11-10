'use strict';

const degistir = document.querySelector('[data-degistir]');
degistir?.addEventListener('click', () => {
    const nereden = degistir.form.elements.nereden;
    const nereye = degistir.form.elements.nereye;
    [nereden.value, nereye.value] = [nereye.value, nereden.value];
});

document.querySelector('[data-yazdir]')?.addEventListener('click', () => window.print());
document.querySelector('[data-geri]')?.addEventListener('click', () => window.history.back());

function sureMetni(saniye) {
    const dakika = Math.floor(saniye / 60);
    return [dakika, saniye % 60].map(parca => String(parca).padStart(2, '0')).join(':');
}

const odemeSayaci = document.querySelector('[data-kalan]');
if (odemeSayaci) {
    const bitis = Date.now() + Number(odemeSayaci.dataset.kalan) * 1000;
    const say = () => {
        const kalan = Math.max(0, Math.ceil((bitis - Date.now()) / 1000));
        odemeSayaci.textContent = kalan > 0 ? 'Kalan seçim süresi ' + sureMetni(kalan) : 'Seçim süresi doldu.';
        document.querySelector('[data-odeme]').disabled = kalan === 0;
    };
    say();
    setInterval(say, 1000);
}

const harita = document.querySelector('[data-harita]');
if (harita) {
    const ayar = harita.dataset;
    const dugmeler = harita.querySelectorAll('[data-koltuk]');
    const secimAlani = harita.querySelector('[data-secimler]');
    const sayac = harita.querySelector('[data-sayac]');
    const hata = harita.querySelector('[data-koltuk-hata]');
    const devam = harita.querySelector('[data-devam]');
    const cinsiyet = harita.querySelector('[data-cinsiyet]');
    const adlar = { 'bos-koltuk': 'boş', secili: 'seçili', dolu: 'dolu', kilitli: 'kilitli' };
    let etkinIstek;
    let secimSuruyor = false;
    let bitis = 0;

    function say() {
        if (!sayac || !bitis) return;
        const kalan = Math.max(0, Math.ceil((bitis - Date.now()) / 1000));
        sayac.textContent = kalan > 0 ? 'Kalan seçim süresi ' + sureMetni(kalan) : 'Seçim süresi doldu.';
        if (kalan === 0) devam.hidden = true;
    }

    function goster(veri) {
        const acik = ayar.acik === '1' && veri.etap.kalkis > veri.simdi;
        for (const dugme of dugmeler) {
            const durum = veri.harita[dugme.dataset.koltuk] || 'bos-koltuk';
            dugme.className = 'koltuk ' + durum;
            dugme.disabled = !acik || durum === 'dolu' || durum === 'kilitli';
            dugme.setAttribute('aria-label', dugme.dataset.koltuk + ' numaralı koltuk, ' + adlar[durum]);
            dugme.setAttribute('aria-pressed', String(durum === 'secili'));
        }
        if (!secimAlani) return;
        const secimler = veri.secimler;
        secimAlani.textContent = !acik ? 'Bu duraktan kalkış zamanı geçti.' : secimler.length
            ? 'Seçili koltuklar · ' + secimler.map(secim => secim.koltuk + ' (' + secim.cinsiyet + ')').join(', ')
            : 'Henüz koltuk seçmediniz.';
        bitis = secimler.length ? Date.now() + (Math.min(...secimler.map(secim => secim.bitis)) - veri.simdi) * 1000 : 0;
        sayac.hidden = !bitis;
        devam.hidden = !acik || !secimler.length;
        cinsiyet.disabled = !acik;
        say();
    }

    async function istek(yol, ayarlar) {
        if (secimSuruyor || (!ayarlar && etkinIstek)) return;
        etkinIstek?.abort();
        const denetleyici = new AbortController();
        etkinIstek = denetleyici;
        secimSuruyor = Boolean(ayarlar);
        try {
            const yanit = await fetch(yol, { ...ayarlar, signal: denetleyici.signal });
            const veri = await yanit.json();
            if (!yanit.ok) throw new Error(veri.hata || 'Koltuk bilgisi alınamadı.');
            goster(veri);
            if (hata && ayarlar) hata.textContent = '';
        } catch (sorun) {
            if (hata && sorun.name !== 'AbortError') hata.textContent = sorun.message;
        } finally {
            if (etkinIstek === denetleyici) {
                etkinIstek = undefined;
                secimSuruyor = false;
            }
        }
    }

    if (ayar.acik === '1') {
        for (const dugme of dugmeler) {
            dugme.addEventListener('click', () => {
                const birak = dugme.classList.contains('secili');
                if (!birak && !cinsiyet.value) {
                    hata.textContent = 'Önce yolcunun cinsiyetini seçin.';
                    cinsiyet.focus();
                    return;
                }
                const body = new URLSearchParams({
                    csrf: ayar.csrf, sefer: ayar.sefer, binis: ayar.binis, inis: ayar.inis,
                    koltuk: dugme.dataset.koltuk, cinsiyet: cinsiyet.value,
                });
                istek(birak ? ayar.birak : ayar.kilitle, { method: 'POST', body });
            });
        }
        setInterval(say, 1000);
    }
    istek(ayar.durum);
    setInterval(() => istek(ayar.durum), 3000);
}
