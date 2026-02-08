<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>IFO Deadlock Letter - <?php echo htmlspecialchars($reference); ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
        }
        .letterhead {
            border: 3px solid #1e3a8a;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .letterhead h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 24pt;
        }
        .letterhead p {
            margin: 5px 0;
            color: #666;
        }
        .stamp {
            background-color: #dc2626;
            color: white;
            font-weight: bold;
            font-size: 16pt;
            padding: 10px 20px;
            text-align: center;
            margin: 20px 0;
            transform: rotate(-5deg);
            display: inline-block;
        }
        .reference-block {
            background-color: #f3f4f6;
            border-left: 4px solid #1e3a8a;
            padding: 15px;
            margin: 20px 0;
        }
        .reference-block strong {
            color: #1e3a8a;
        }
        .complaint-summary {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
        }
        .ifo-address {
            background-color: #e0f2fe;
            border: 2px solid #0284c7;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        .ifo-address h3 {
            margin: 0 0 10px 0;
            color: #0284c7;
            font-size: 14pt;
        }
        .signature-block {
            margin-top: 40px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
        p {
            margin: 15px 0;
            text-align: justify;
        }
    </style>
</head>
<body>
    <!-- Letterhead -->
    <div class="letterhead">
        <h1>⚽ STEWARD</h1>
        <p>Complaint Management Portal</p>
        <p>Official Communication</p>
    </div>

    <!-- Deadlock Stamp -->
    <div style="text-align: center;">
        <div class="stamp">🚨 DEADLOCK DECLARED</div>
    </div>

    <!-- Reference Block -->
    <div class="reference-block">
        <strong>Reference:</strong> <?php echo htmlspecialchars($reference); ?><br>
        <strong>Date:</strong> <?php echo htmlspecialchars($date); ?><br>
        <strong>Supporter:</strong> <?php echo htmlspecialchars($supporter_name); ?><br>
        <strong>Original Complaint Date:</strong> <?php echo htmlspecialchars($created_date); ?><br>
        <strong>IFO Deadline:</strong> <?php echo htmlspecialchars($deadline_date); ?>
    </div>

    <!-- Letter Body -->
    <p>Dear <?php echo htmlspecialchars($supporter_name); ?>,</p>

    <p>
        We are writing to inform you that your complaint, reference <strong><?php echo htmlspecialchars($reference); ?></strong>, 
        has been formally declared as having reached <strong>DEADLOCK</strong> status under the Independent Football Ombudsman (IFO) 
        procedure.
    </p>

    <p>
        Your complaint was submitted on <strong><?php echo htmlspecialchars($created_date); ?></strong> with the subject:
    </p>

    <p><strong>"<?php echo htmlspecialchars($complaint_subject); ?>"</strong></p>

    <div class="complaint-summary">
        <strong>Original Complaint Summary:</strong><br>
        <?php echo nl2br(htmlspecialchars($complaint_body)); ?>
    </div>

    <p>
        In accordance with the IFO Scheme Rules, clubs must endeavor to resolve supporter complaints within 42 days of receipt. 
        Despite our best efforts, we have been unable to reach a mutually satisfactory resolution within this timeframe, which 
        expired on <strong><?php echo htmlspecialchars($deadline_date); ?></strong>.
    </p>

    <p>
        As per the IFO procedure, you now have the right to escalate this matter to the Independent Football Ombudsman for 
        formal adjudication. The IFO is an independent body that provides free, impartial resolution of disputes between 
        supporters and football clubs.
    </p>

    <!-- IFO Address Block -->
    <div class="ifo-address">
        <h3>📮 INDEPENDENT FOOTBALL OMBUDSMAN</h3>
        <p style="margin: 5px 0; line-height: 1.8;">
            <strong>Independent Football Ombudsman</strong><br>
            Premier House<br>
            1-5 Argyle Way<br>
            Stevenage<br>
            Hertfordshire<br>
            SG1 2AD
        </p>
    </div>

    <p>
        To refer your complaint to the IFO, please contact them directly at the address above or visit their website for 
        online submission procedures. You will need to provide this letter and your original complaint reference number 
        <strong><?php echo htmlspecialchars($reference); ?></strong>.
    </p>

    <p>
        Please note that the IFO may decline to review cases that do not fall within their remit or where alternative 
        resolution routes are more appropriate. Full details of the IFO Scheme Rules and jurisdiction can be found on 
        their official website.
    </p>

    <p>
        We regret that we were unable to resolve your complaint to your satisfaction within our internal procedures. 
        We remain committed to supporter welfare and will fully cooperate with any IFO investigation should you choose 
        to proceed with escalation.
    </p>

    <!-- Signature Block -->
    <div class="signature-block">
        <p>Yours faithfully,</p>
        <p style="margin-top: 40px;">
            <strong>Supporter Liaison Department</strong><br>
            Steward Complaint Management Portal
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Document Reference:</strong> DEADLOCK-<?php echo htmlspecialchars($reference); ?>-<?php echo date('Ymd'); ?><br>
            <strong>Generated:</strong> <?php echo date('d F Y \a\t H:i'); ?><br>
            This is an official document generated by the Steward Complaint Management System.
        </p>
    </div>
</body>
</html>
