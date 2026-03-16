<?php

namespace ZirveDonusum\Gib\Services;

use Ramsey\Uuid\Uuid;

/**
 * GİB e-Arşiv Portal fatura servisi.
 *
 * GİB e-Arşiv sistemi üzerinde fatura oluşturma, listeleme, görüntüleme
 * ve indirme işlemlerini yönetir. Bu paket GİB'in e-Arşiv portalı ile
 * doğrudan iletişim kurar (entegratör aracılığı olmadan).
 *
 * GİB API yapısı:
 *   - Tüm istekler dispatch endpoint'ine gider
 *   - cmd: Komut adı (EARSIV_PORTAL_*)
 *   - pageName: Sayfa tanımlayıcı (RG_*)
 *   - jp: JSON parametreler (string olarak)
 */
class InvoiceService extends BaseService
{
    // ─── Fatura Oluşturma ─────────────────────────────────────────────

    /**
     * Taslak e-Arşiv fatura oluştur.
     *
     * GİB portalına yeni bir e-Arşiv fatura taslağı gönderir.
     * Başarılı ise yanıtta "başarıyla" ifadesi yer alır.
     *
     * @param array $invoiceData Fatura verileri:
     *   - faturaUuid: string — Fatura UUID (yoksa otomatik üretilir)
     *   - faturaTarihi: string — Tarih (dd/MM/yyyy)
     *   - saat: string — Saat (HH:mm:ss)
     *   - vknTckn: string — Alıcı VKN/TCKN
     *   - aliciUnvan: string — Alıcı unvan/ad soyad
     *   - vergiDairesi: string — Alıcı vergi dairesi
     *   - malHizmetTable: array — Mal/hizmet kalemleri
     *   - matrah: string — KDV matrahı
     *   - hesaplanankdv: string — Hesaplanan KDV
     *   - vergilerToplami: string — Vergiler toplamı
     *   - vergilerDahilToplamTutar: string — Vergiler dahil toplam
     *   - odenecekTutar: string — Ödenecek tutar
     *   - tip: string — Fatura tipi (varsayılan: İskonto)
     *   - not: string — Fatura notu
     * @return array GİB yanıt verisi
     *
     * @throws \ZirveDonusum\Exceptions\ApiException Fatura oluşturulamadığında
     */
    public function createDraft(array $invoiceData): array
    {
        // UUID yoksa üret
        if (empty($invoiceData['faturaUuid'])) {
            $invoiceData['faturaUuid'] = Uuid::uuid4()->toString();
        }

        return $this->http->dispatch(
            'EARSIV_PORTAL_FATURA_OLUSTUR',
            'RG_BASITFATURA',
            $invoiceData
        );
    }

    // ─── Fatura Sorgulama ─────────────────────────────────────────────

    /**
     * Fatura detayı getir (ETTN ile).
     *
     * Verilen ETTN (Elektronik Takip Numarası) ile faturanın
     * tüm detaylarını GİB'den çeker.
     *
     * @param string $ettn Fatura ETTN (UUID formatında)
     * @return array Fatura detay verileri
     *
     * @throws \ZirveDonusum\Exceptions\ApiException Fatura bulunamadığında
     */
    public function get(string $ettn): array
    {
        return $this->http->dispatch(
            'EARSIV_PORTAL_FATURA_GETIR',
            'RG_TASLAKLAR',
            ['ettn' => $ettn]
        );
    }

    /**
     * Taslak faturaları listele.
     *
     * Belirtilen tarih aralığındaki e-Arşiv taslak faturaları döndürür.
     * GİB portalında "Taslaklar" sayfasındaki listeye karşılık gelir.
     *
     * @param string $startDate Başlangıç tarihi (dd/MM/yyyy)
     * @param string $endDate Bitiş tarihi (dd/MM/yyyy)
     * @param string $type Fatura tipi filtresi (varsayılan: 5000/30000)
     * @return array Taslak fatura listesi
     *
     * @throws \ZirveDonusum\Exceptions\ApiException Listeleme başarısız olduğunda
     */
    public function listDrafts(string $startDate, string $endDate, string $type = '5000/30000'): array
    {
        return $this->http->dispatch(
            'EARSIV_PORTAL_TASLAKLARI_GETIR',
            'RG_TASLAKLAR',
            [
                'baslangic' => $startDate,
                'bitis' => $endDate,
                'hangiTip' => $type,
            ]
        );
    }

    // ─── Fatura Görüntüleme & İndirme ─────────────────────────────────

    /**
     * Fatura HTML önizlemesi.
     *
     * Faturanın GİB tarafından oluşturulan HTML görüntüsünü döndürür.
     * Tarayıcıda veya PDF'e dönüştürmek için kullanılabilir.
     *
     * @param string $ettn Fatura ETTN
     * @param string $approvalStatus Onay durumu (varsayılan: Onaylanmadı)
     * @return string Fatura HTML içeriği
     *
     * @throws \ZirveDonusum\Exceptions\ApiException HTML alınamadığında
     */
    public function getHtml(string $ettn, string $approvalStatus = 'Onaylanmadı'): string
    {
        $response = $this->http->dispatch(
            'EARSIV_PORTAL_FATURA_GOSTER',
            'RG_TASLAKLAR',
            [
                'ettn' => $ettn,
                'onayDurumu' => $approvalStatus,
            ]
        );

        return $response['data'] ?? '';
    }

    /**
     * Fatura ZIP olarak indir.
     *
     * GİB portalından faturayı ZIP dosyası olarak indirir.
     * ZIP içinde fatura XML'i ve/veya PDF'i bulunur.
     *
     * @param string $ettn Fatura ETTN
     * @param string $approvalStatus Onay durumu (varsayılan: Onaylandı)
     * @return string ZIP dosya içeriği (binary)
     *
     * @throws \ZirveDonusum\Exceptions\ApiException İndirme başarısız olduğunda
     */
    public function download(string $ettn, string $approvalStatus = 'Onaylandı'): string
    {
        return $this->http->download($ettn, $approvalStatus);
    }

    // ─── CRM Entegrasyonu ─────────────────────────────────────────────

    /**
     * CRM fatura modelinden GİB e-Arşiv fatura formatına dönüştür.
     *
     * CRM sistemlerinde kullanılan yaygın fatura yapısını GİB'in beklediği
     * formata çevirir. Bu statik method hem servis içinde hem de
     * CRM entegrasyonlarında doğrudan kullanılabilir.
     *
     * CRM Alan Eşleştirmesi:
     *   date → faturaTarihi (dd/MM/yyyy formatına çevrilir)
     *   time → saat (HH:mm:ss)
     *   customer.tax_number → vknTckn
     *   customer.name → aliciUnvan
     *   customer.tax_office → vergiDairesi
     *   items[] → malHizmetTable[]
     *   subtotal → matrah
     *   tax_total → hesaplanankdv, vergilerToplami
     *   grand_total → vergilerDahilToplamTutar, odenecekTutar
     *   uuid → faturaUuid
     *   note → not
     *   type → tip
     *
     * @param array $crmInvoice CRM fatura verileri
     * @return array GİB formatında fatura verileri
     */
    public static function mapToGibFormat(array $crmInvoice): array
    {
        $customer = $crmInvoice['customer'] ?? [];
        $items = $crmInvoice['items'] ?? [];

        // Tarih dönüşümü: ISO/Y-m-d → dd/MM/yyyy
        $date = $crmInvoice['date'] ?? date('Y-m-d');
        $faturaTarihi = date('d/m/Y', strtotime($date));

        // Saat dönüşümü
        $saat = $crmInvoice['time'] ?? date('H:i:s');

        // UUID: varsa kullan, yoksa üret
        $uuid = $crmInvoice['uuid'] ?? Uuid::uuid4()->toString();

        // Mal/hizmet kalemlerini GİB formatına dönüştür
        $malHizmetTable = array_map(function (array $item) {
            $miktar = (float) ($item['quantity'] ?? 1);
            $birimFiyat = (float) ($item['unit_price'] ?? 0);
            $kdvOrani = (int) ($item['tax_rate'] ?? 18);
            $iskontoOrani = (float) ($item['discount_rate'] ?? 0);

            $fiyat = $miktar * $birimFiyat;
            $iskontoTutari = $fiyat * ($iskontoOrani / 100);
            $malHizmetTutari = $fiyat - $iskontoTutari;
            $kdvTutari = $malHizmetTutari * ($kdvOrani / 100);

            return [
                'malHizmet' => $item['name'] ?? $item['description'] ?? '',
                'miktar' => $miktar,
                'birim' => $item['unit'] ?? 'HUR',
                'birimFiyat' => number_format($birimFiyat, 2, '.', ''),
                'fiyat' => number_format($fiyat, 2, '.', ''),
                'kdvOrani' => $kdvOrani,
                'kdvTutari' => number_format($kdvTutari, 2, '.', ''),
                'malHizmetTutari' => number_format($malHizmetTutari, 2, '.', ''),
                'iskontoOrani' => $iskontoOrani,
                'iskontoTutari' => number_format($iskontoTutari, 2, '.', ''),
                'iskontoArttm' => $item['discount_type'] ?? 'İskonto',
            ];
        }, $items);

        return [
            'faturaUuid' => $uuid,
            'faturaTarihi' => $faturaTarihi,
            'saat' => $saat,
            'vknTckn' => $customer['tax_number'] ?? '',
            'aliciUnvan' => $customer['name'] ?? '',
            'vergiDairesi' => $customer['tax_office'] ?? '',
            'malHizmetTable' => $malHizmetTable,
            'matrah' => number_format((float) ($crmInvoice['subtotal'] ?? 0), 2, '.', ''),
            'hesaplanankdv' => number_format((float) ($crmInvoice['tax_total'] ?? 0), 2, '.', ''),
            'vergilerToplami' => number_format((float) ($crmInvoice['tax_total'] ?? 0), 2, '.', ''),
            'vergilerDahilToplamTutar' => number_format((float) ($crmInvoice['grand_total'] ?? 0), 2, '.', ''),
            'odenecekTutar' => number_format((float) ($crmInvoice['grand_total'] ?? 0), 2, '.', ''),
            'tip' => $crmInvoice['type'] ?? 'İskonto',
            'not' => $crmInvoice['note'] ?? '',
        ];
    }
}
