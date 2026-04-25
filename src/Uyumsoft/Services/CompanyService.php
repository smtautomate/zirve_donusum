<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Mukellef sorgulama / firma bilgisi servisi.
 */
class CompanyService extends BaseService
{
    /**
     * VKN/TCKN ile mukellefin GIB e-Fatura kayitli olup olmadigini sorgula.
     */
    public function lookupTaxpayer(string $taxNumber): array
    {
        return $this->http->post('Integration/Common/CheckUser', [
            'VknTckn' => $taxNumber,
        ]);
    }

    /**
     * Tum kayitli e-Fatura mukelleflerini getir (GIB listesi).
     */
    public function listRegisteredTaxpayers(array $filters = []): array
    {
        return $this->http->post('Integration/Common/GetUserList', $filters);
    }

    /**
     * Aktif kullanicinin firma bilgisi.
     */
    public function info(): array
    {
        return $this->http->post('Integration/Common/GetCompanyInfo', []);
    }
}
