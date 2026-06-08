<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Bilet servisi.
 * API: POST /api/BiletApi
 * Referans: iuyum_api/Bilgi Sistemleri/E-Bilet/SendTicket.json
 */
class EBiletService extends BaseService
{
    /**
     * E-Bilet gonder.
     *
     * @param  array $ticket  {
     *   "TravelDate"          => "2023-01-01T00:59:59.999",
     *   "PreperationDate"     => "2023-01-01T23:59:59.999",
     *   "DocumentId"          => "uuid-string",
     *   "ExpenseOwnerTcknVkn" => "15058023598",
     *   "PaymentType"         => "0",
     *   "Amount"              => 50.00,
     *   "Tax"                 => 3.70,
     *   "AdditionalData"      => [["Name" => "YOLCUADI", "Value" => "..."], ...],
     * }
     */
    public function send(array $ticket): array
    {
        return $this->http->action('SendTicket', [
            'ticket' => $ticket,
        ]);
    }
}
