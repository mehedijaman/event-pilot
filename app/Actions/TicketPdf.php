<?php

namespace App\Actions;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class TicketPdf
{
    public function generate(Registration $registration): string
    {
        $qrCode = $this->generateQr($registration->ticket_code);

        $pdf = Pdf::loadView('pdf.ticket', [
            'registration' => $registration,
            'qrCode' => $qrCode,
        ]);

        return $pdf->output();
    }

    private function generateQr(string $ticketCode): string
    {
        $result = new Builder(
            writer: new PngWriter,
            data: $ticketCode,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 250,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $result->build()->getDataUri();
    }
}
