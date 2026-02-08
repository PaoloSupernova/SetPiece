#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Email Ingestion CLI Script
 * 
 * Processes .eml files from storage/inbox/ and creates complaints.
 * Includes OCR processing for image attachments.
 * 
 * Usage: php bin/ingest_emails.php
 * Cron: */5 * * * * cd /path/to/steward && php bin/ingest_emails.php >> storage/logs/ingestion.log 2>&1
 */

// Bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use ZBateson\MailMimeParser\MailMimeParser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Core\Database;
use App\Models\Complaint;
use App\Services\ToxicityService;
use App\Services\AuditService;

// Logging function
function logMessage(string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$message}\n";
}

logMessage("Starting email ingestion...");

// Paths
$inboxPath = __DIR__ . '/../storage/inbox';
$processedPath = $inboxPath . '/processed';
$failedPath = $inboxPath . '/failed';
$attachmentsPath = __DIR__ . '/../storage/attachments';
$ocrPath = __DIR__ . '/../storage/ocr';

// Ensure directories exist
foreach ([$processedPath, $failedPath, $attachmentsPath, $ocrPath] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Get .eml files
$emlFiles = glob($inboxPath . '/*.eml');

if (empty($emlFiles)) {
    logMessage("No .eml files found in inbox");
    exit(0);
}

logMessage("Found " . count($emlFiles) . " email(s) to process");

// Initialize services
$db = Database::getInstance();
$complaintModel = new Complaint();
$toxicityService = new ToxicityService();
$auditService = new AuditService();

// Process each email
foreach ($emlFiles as $emlFile) {
    $filename = basename($emlFile);
    logMessage("Processing: {$filename}");
    
    try {
        // Parse email
        $mailParser = new MailMimeParser();
        $message = $mailParser->parse(fopen($emlFile, 'r'));
        
        $from = $message->getHeaderValue('from');
        $subject = $message->getHeaderValue('subject') ?? 'No Subject';
        $textBody = $message->getTextContent();
        $htmlBody = $message->getHtmlContent();
        
        // Use text body, or strip HTML if only HTML body available
        $body = $textBody ?: strip_tags($htmlBody);
        
        if (empty($body)) {
            throw new Exception("Email has no body content");
        }
        
        // Extract email address from 'from' field
        preg_match('/<(.+?)>/', $from, $matches);
        $senderEmail = $matches[1] ?? $from;
        
        logMessage("From: {$senderEmail}");
        logMessage("Subject: {$subject}");
        
        // Find user by email
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$senderEmail]);
        
        if (!$user) {
            throw new Exception("Sender email not found in user database: {$senderEmail}");
        }
        
        // Run toxicity analysis
        $combinedText = $subject . ' ' . $body;
        $toxicityScore = $toxicityService->analyse($combinedText);
        
        logMessage("Toxicity score: " . round($toxicityScore * 100) . "%");
        
        // Create complaint
        $complaintId = $complaintModel->create([
            'user_id' => $user['id'],
            'subject' => substr($subject, 0, 500),
            'body' => $body,
            'category' => 'general',
            'stadium_block' => 'unknown',
            'toxicity_score' => $toxicityScore,
            'status' => 'new',
        ]);
        
        logMessage("Created complaint ID: {$complaintId}");
        
        // Log to audit trail
        $auditService->log(
            $complaintId,
            null, // System user
            'complaint_created_via_email',
            null,
            'new'
        );
        
        // Process attachments for OCR
        $attachmentCount = $message->getAttachmentCount();
        
        if ($attachmentCount > 0) {
            logMessage("Found {$attachmentCount} attachment(s)");
            
            for ($i = 0; $i < $attachmentCount; $i++) {
                $attachment = $message->getAttachmentPart($i);
                $attachmentFilename = $attachment->getHeaderParameter('Content-Disposition', 'filename', 'attachment_' . $i);
                $contentType = $attachment->getContentType();
                
                // Check if it's an image
                if (strpos($contentType, 'image/') === 0) {
                    logMessage("Processing image attachment: {$attachmentFilename}");
                    
                    // Save attachment
                    $attachmentPath = $attachmentsPath . '/' . uniqid() . '_' . $attachmentFilename;
                    file_put_contents($attachmentPath, $attachment->getContent());
                    
                    // Run OCR if Tesseract is available
                    $tesseractPath = $_ENV['TESSERACT_PATH'] ?? '/usr/bin/tesseract';
                    
                    if (file_exists($tesseractPath)) {
                        try {
                            $ocr = new TesseractOCR($attachmentPath);
                            $ocrText = $ocr->run();
                            
                            if (!empty(trim($ocrText))) {
                                logMessage("OCR extracted " . strlen($ocrText) . " characters");
                                
                                // Save OCR text
                                $ocrFilename = $ocrPath . '/ocr_' . $complaintId . '_' . uniqid() . '.txt';
                                file_put_contents($ocrFilename, $ocrText);
                                
                                // Add as system message to complaint
                                $complaintModel->addMessage(
                                    $complaintId,
                                    'system',
                                    "OCR Text from attachment '{$attachmentFilename}':\n\n{$ocrText}"
                                );
                                
                                logMessage("OCR text added as system message");
                            } else {
                                logMessage("OCR returned no text");
                            }
                        } catch (Exception $e) {
                            logMessage("OCR failed: " . $e->getMessage());
                        }
                    } else {
                        logMessage("Tesseract not found at: {$tesseractPath}");
                    }
                }
            }
        }
        
        // Move to processed
        $newPath = $processedPath . '/' . $filename;
        rename($emlFile, $newPath);
        logMessage("Moved to processed: {$filename}");
        
    } catch (Exception $e) {
        logMessage("ERROR: " . $e->getMessage());
        
        // Move to failed
        $newPath = $failedPath . '/' . $filename;
        rename($emlFile, $newPath);
        logMessage("Moved to failed: {$filename}");
    }
    
    logMessage("---");
}

logMessage("Email ingestion complete");
