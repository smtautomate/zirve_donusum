<?php

namespace ZirveDonusum\Services;

/**
 * Sözleşme İşlemleri
 *
 * Gerçek endpoint'ler:
 *   GET /Home/GetContractTypes
 *   GET /Home/GetContracts?taxNumber={vkn}&contractName={name}
 */
class ContractService extends BaseService
{
    /**
     * Sözleşme tiplerini listele
     */
    public function getContractTypes(): array
    {
        return $this->http->get('/Home/GetContractTypes');
    }

    /**
     * Sözleşmeleri getir (VKN ve sözleşme adına göre)
     */
    public function getContracts(string $taxNumber, ?string $contractName = null): array
    {
        $query = ['taxNumber' => $taxNumber];

        if ($contractName) {
            $query['contractName'] = $contractName;
        }

        return $this->http->get('/Home/GetContracts', $query);
    }
}
