<?php

namespace ZirveDonusum\Services;

/**
 * Firma / Şirket / Mükellef İşlemleri
 */
class CompanyService extends BaseService
{
    /**
     * Firma bilgilerini getir
     */
    public function info(): array
    {
        return $this->http->get($this->cp('company/GetCompanyInfo'));
    }

    /**
     * Mükellef sorgula (VKN/TCKN ile)
     */
    public function lookupTaxpayer(string $taxNumber): array
    {
        return $this->http->get($this->cp('company/CheckTaxpayer'), [
            'taxNumber' => $taxNumber,
        ]);
    }

    /**
     * E-Fatura mükellefi mi kontrol et
     */
    public function checkEInvoiceRegistered(string $taxNumber): array
    {
        return $this->http->get($this->cp('company/CheckEInvoiceUser'), [
            'taxNumber' => $taxNumber,
        ]);
    }
}
