<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Deadlock Letter PDF Generation Service
 * 
 * Generates formal IFO deadlock letters as PDF documents
 * when complaints reach the 42-day deadline without resolution.
 */
class DeadlockService
{
    /**
     * Generate a deadlock letter PDF
     * 
     * @param array $data Complaint data for the letter
     * @return string Raw PDF content
     */
    public function generateLetter(array $data): string
    {
        // Configure DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);

        // Load the PDF template
        $templatePath = __DIR__ . '/../../views/pdf/deadlock_letter.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Deadlock letter template not found");
        }

        // Extract data for template
        extract($data);
        
        // Capture template output
        ob_start();
        require $templatePath;
        $html = ob_get_clean();

        // Generate PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Stream a deadlock letter PDF to the browser
     * 
     * @param array $data Complaint data for the letter
     * @param string $filename Filename for download
     */
    public function streamLetter(array $data, string $filename): void
    {
        $pdf = $this->generateLetter($data);

        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdf;
        exit;
    }

    /**
     * Format a complaint reference number
     * 
     * @param int $id Complaint ID
     * @return string Formatted reference (STW-000001)
     */
    public function formatReference(int $id): string
    {
        return sprintf('STW-%06d', $id);
    }
}
