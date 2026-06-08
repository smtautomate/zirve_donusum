<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft mukellef / firma sorgulama servisi.
 * API: POST /api/BasicIntegrationApi
 * E-Fatura'ya kayitli mukellef ve firma bilgisi sorgulari.
 */
class CompanyService extends BaseService
{
    /**
     * VKN/TCKN ile mukellefin GIB e-Fatura sistemine kayitli olup olmadigini sorgula.
     */
    public function lookupTaxpayer(string $vknTckn): array
    {
        return $this->http->action('IsEInvoiceUser', [
            'vknTckn' => $vknTckn,
        ]);
    }

    /**
     * GIB'e kayitli tum e-Fatura mukelleflerini getir.
     *
     * @param  array $filters  orn. startDate, endDate
     */
    public function listRegisteredTaxpayers(array $filters = []): array
    {
        return $this->http->action('GetEInvoiceUsers', $filters);
    }

    /**
     * Hesaba ait kullanici takma ad (alias) listesi.
     */
    public function getUserAliases(string $vknTckn): array
    {
        return $this->http->action('GetUserAliasses', [
            'vknTckn' => $vknTckn,
        ]);
    }
}
