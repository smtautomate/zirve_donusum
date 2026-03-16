<?php

namespace ZirveDonusum\Services;

/**
 * Firma / Şirket / Mükellef İşlemleri
 */
class CompanyService extends BaseService
{
    /**
     * Firma bilgilerini getir
     * Endpoint: GET /cp/{accountId}/Account/GetAccountInfo
     */
    public function info(): array
    {
        return $this->http->get($this->cp('Account/GetAccountInfo'));
    }

    /**
     * Firma IBAN bilgilerini getir
     * Endpoint: GET /cp/{accountId}/Account/GetAccountIbanWithBankInfo
     */
    public function getIbanInfo(): array
    {
        return $this->http->get($this->cp('Account/GetAccountIbanWithBankInfo'));
    }

    /**
     * Belge ön ek bilgileri (e-Fatura seri QAA, e-Arşiv seri QAB vb.)
     * Endpoint: GET /cp/{accountId}/Account/GetPrefixCodeInfo
     *
     * Response: {
     *   mainAccountInfo: {
     *     eInvoice: { isServiceActive: true, serialNumber: "QAA" },
     *     eArchive: { isServiceActive: true, serialNumber: "QAB" },
     *     eDespatch: { isServiceActive: false, serialNumber: null },
     *     ...
     *   },
     *   subAccounts: []
     * }
     */
    public function getPrefixCodes(): array
    {
        return $this->http->get($this->cp('Account/GetPrefixCodeInfo'));
    }

    /**
     * VKN/TCKN ile e-Fatura mükellef kontrolü
     * Müşterinin e-Fatura mükellefi olup olmadığını, alias bilgisini döndürür.
     *
     * Endpoint: POST /cp/{accountId}/newInvoice/getCustomerEInvoiceUsers/{taxNumber}
     *
     * Response (mükellef ise): {
     *   Data: {
     *     customer: { TaxNumber, Title, TaxOffice, Alias: { Alias, Title, ... } },
     *     users: [{ Alias, Title, Type, AccountType, Active, ... }]
     *   }
     * }
     *
     * Response (mükellef değilse): {
     *   Data: { customer: null, users: [] }
     * }
     *
     * @return array API response
     */
    public function checkEInvoiceRegistered(string $taxNumber): array
    {
        return $this->http->post($this->cp("newInvoice/getCustomerEInvoiceUsers/{$taxNumber}"), []);
    }

    /**
     * Mükellef kimlik kontrolü (Nace üzerinden)
     * Endpoint: GET /cp/{accountId}/Nace/CheckTaxPayerHasIdentity?vkn={vkn}&checkEInvoiceAlias=true
     */
    public function checkTaxpayerIdentity(string $taxNumber): array
    {
        return $this->http->get($this->cp('Nace/CheckTaxPayerHasIdentity'), [
            'vkn' => $taxNumber,
            'checkEInvoiceAlias' => 'true',
        ]);
    }

    /**
     * Mükellef sorgula (eski endpoint — geriye uyumluluk)
     */
    public function lookupTaxpayer(string $taxNumber): array
    {
        return $this->checkEInvoiceRegistered($taxNumber);
    }
}
