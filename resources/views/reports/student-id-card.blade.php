<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card - {{ $user->student_id }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 40px auto;
            padding: 0;
            background-color: #ffffff;
            width: 3.625in;
        }
        .card-container {
            width: 3.625in;
            height: 2.375in;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            background-color: #022c22;
            color: #ffffff;
            border: 1px solid #064e3b;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .banner-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #022c22;
            border-bottom: 1px solid rgba(234, 179, 8, 0.3);
        }
        .banner-cell {
            padding: 0.125in 0.15in;
        }
        .banner-logo {
            width: 32px;
            height: 32px;
            vertical-align: middle;
        }
        .banner-text {
            padding-left: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .banner-title {
            font-size: 10px;
            font-weight: bold;
            color: #facc15; /* yellow-400 */
            margin: 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .banner-subtitle {
            font-size: 7px;
            color: #6ee7b7; /* emerald-300 */
            margin: 1px 0 0 0;
            font-weight: 600;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }
        .content-cell {
            padding: 0.125in 0.15in;
        }
        .photo-cell {
            width: 1.0in;
            text-align: center;
            vertical-align: top;
        }
        .photo-box {
            width: 0.85in;
            height: 0.85in;
            border: 2px solid rgba(234, 179, 8, 0.5);
            background-color: #022c22;
            overflow: hidden;
            margin-bottom: 6px;
            border-radius: 6px;
        }
        .student-photo {
            width: 0.85in;
            height: 0.85in;
        }
        .no-photo-placeholder {
            width: 0.85in;
            height: 0.85in;
            line-height: 0.85in;
            background-color: #064e3b;
            color: #6ee7b7;
            font-size: 32px;
            text-align: center;
            font-weight: bold;
        }
        .badge-container {
            text-align: center;
            margin-top: 3px;
        }
        .badge {
            background-color: rgba(234, 179, 8, 0.2);
            border: 1px solid rgba(234, 179, 8, 0.3);
            color: #facc15;
            font-size: 7px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 3px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
        }
        .details-cell {
            vertical-align: top;
            padding-left: 10px;
        }
        .detail-label {
            font-size: 6px;
            color: #34d399; /* emerald-400 */
            text-transform: uppercase;
            font-weight: bold;
            margin: 0;
        }
        .detail-value {
            font-size: 9px;
            font-weight: bold;
            color: #ffffff;
            margin: 0 0 4px 0;
        }
        .detail-value-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-cell {
            width: 33.33%;
            vertical-align: top;
            padding-right: 5px;
        }
        .grid-cell-half {
            width: 50%;
            vertical-align: top;
            padding-right: 5px;
        }
        .blood-group-val {
            color: #facc15 !important; /* yellow-400 */
            font-weight: bold;
        }
        .accent-footer-bar {
            width: 100%;
            height: 5px;
            background-color: #10b981; /* emerald-500 */
            position: absolute;
            bottom: 0;
            left: 0;
        }

        /* BACK CARD */
        .back-banner {
            background-color: #022c22;
            text-align: center;
            padding: 6px 0;
            border-bottom: 1px solid rgba(234, 179, 8, 0.3);
        }
        .back-banner-title {
            font-size: 9px;
            font-weight: bold;
            color: #facc15;
            letter-spacing: 1.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .back-content {
            padding: 0.125in 0.15in;
        }
        .instructions-title {
            font-size: 8px;
            font-weight: bold;
            color: #ffffff;
            border-bottom: 1px solid #064e3b;
            padding-bottom: 2px;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .instructions-list {
            margin: 0;
            padding-left: 12px;
            font-size: 7px;
            color: #a7f3d0; /* emerald-200 */
            line-height: 1.25;
        }
        .instructions-list li {
            margin-bottom: 3px;
        }
        .back-footer-table {
            width: 3.325in; /* 3.625in card width - 0.15in * 2 horizontal padding */
            border-collapse: collapse;
            position: absolute;
            top: 1.62in;
            left: 0.15in;
            border-top: 1px solid rgba(6, 78, 59, 0.8);
        }
        .back-footer-cell {
            padding-top: 6px;
        }
        .footer-contact {
            width: 40%;
            font-size: 6px;
            color: #34d399;
            font-family: monospace;
            vertical-align: bottom;
            padding-bottom: 4px;
        }
        .footer-contact p {
            margin: 0 0 1px 0;
        }
        .footer-qr {
            width: 25%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 4px;
        }
        .qr-box {
            background-color: #ffffff;
            padding: 2px;
            border-radius: 3px;
            display: inline-block;
            border: 1px solid #064e3b;
        }
        .qr-img {
            width: 36px;
            height: 36px;
            display: block;
        }
        .qr-label {
            font-size: 4px;
            color: #022c22;
            font-weight: bold;
            margin-top: 1px;
            text-transform: uppercase;
        }
        .footer-sig {
            width: 35%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 4px;
        }
        .sig-text {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-style: italic;
            font-weight: bold;
            font-size: 9px;
            color: #facc15;
            margin-bottom: 2px;
        }
        .sig-line {
            border-top: 1px solid #10b981; /* emerald-500 line for clean visibility */
            width: 70px;
            margin: 0 auto;
            padding-top: 4px;
        }
        .sig-name {
            font-size: 6px;
            font-weight: bold;
            color: #ffffff;
            margin: 0;
        }
        .sig-title {
            font-size: 5px;
            color: #34d399;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- CARD FRONT -->
    <div class="card-container">
        <!-- Banner -->
        <table class="banner-table">
            <tr>
                <td class="banner-cell" style="width: 40px; vertical-align: middle;">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" class="banner-logo" alt="Logo" />
                    @endif
                </td>
                <td class="banner-cell banner-text">
                    <h3 class="banner-title">COX'S BAZAR MODEL POLYTECHNIC INSTITUTE</h3>
                    <p class="banner-subtitle">Approved by BTEB & Government of Bangladesh</p>
                </td>
            </tr>
        </table>

        <!-- Content -->
        <table class="content-table" style="margin-top: 10px;">
            <tr>
                <td class="content-cell photo-cell">
                    <div class="photo-box">
                        @if($avatarSrc)
                            <img src="{{ $avatarSrc }}" class="student-photo" alt="Photo" />
                        @else
                            <div class="no-photo-placeholder">?</div>
                        @endif
                    </div>
                    <div class="badge-container">
                        <span class="badge">STUDENT</span>
                    </div>
                </td>
                <td class="content-cell details-cell">
                    <p class="detail-label">Name</p>
                    <p class="detail-value" style="font-size: 12px; margin-bottom: 8px;">{{ $user->name }}</p>

                    <table class="grid-table">
                        <tr>
                            <td class="grid-cell">
                                <p class="detail-label">Class ID/Roll</p>
                                <p class="detail-value detail-value-mono">{{ $user->student_id }}</p>
                            </td>
                            <td class="grid-cell">
                                <p class="detail-label">Board Roll</p>
                                <p class="detail-value detail-value-mono">{{ $user->board_roll ?: 'N/A' }}</p>
                            </td>
                            <td class="grid-cell">
                                <p class="detail-label">Reg No</p>
                                <p class="detail-value detail-value-mono">{{ $user->reg_no ?: 'N/A' }}</p>
                            </td>
                        </tr>
                    </table>

                    <table class="grid-table" style="margin-top: 5px;">
                        <tr>
                            <td class="grid-cell-half">
                                <p class="detail-label">Technology/Dept</p>
                                <p class="detail-value" style="font-size: 10px;">{{ $user->department ?: 'N/A' }}</p>
                            </td>
                            <td class="grid-cell-half">
                                <p class="detail-label">Session</p>
                                <p class="detail-value detail-value-mono">{{ $user->session ?: 'N/A' }}</p>
                            </td>
                        </tr>
                    </table>

                    @php
                        $startYear = date('Y');
                        if (preg_match('/(\d{4})/', $user->session, $matches)) {
                            $startYear = intval($matches[1]);
                        }
                        $expiryYear = $startYear + 4;
                    @endphp
                    <table class="grid-table" style="margin-top: 5px;">
                        <tr>
                            <td class="grid-cell-half">
                                <p class="detail-label">Expiry Date</p>
                                <p class="detail-value detail-value-mono">{{ $expiryYear }}</p>
                            </td>
                            <td class="grid-cell-half">
                                <p class="detail-label">Blood Group</p>
                                <p class="detail-value blood-group-val">{{ $user->blood_group ?: 'N/A' }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- Footer Accent -->
        <div class="accent-footer-bar"></div>
    </div>

    <!-- CARD BACK -->
    <div class="card-container">
        <!-- Banner -->
        <div class="back-banner">
            <h4 class="back-banner-title">CMPI CAMPUS</h4>
        </div>

        <!-- Content -->
        <div class="back-content">
            <p class="instructions-title">INSTRUCTIONS</p>
            <ul class="instructions-list">
                <li>This card is the property of Cox's Bazar Model Polytechnic Institute.</li>
                <li>The holder must wear and display this card visibly on campus at all times.</li>
                <li>If found, please return to the institute administrative office.</li>
            </ul>
        </div>

        <!-- Footer Details -->
        <table class="back-footer-table">
            <tr>
                <td class="back-footer-cell footer-contact">
                    <p>Emergency: +880 1888-000000</p>
                    <p>Email: admin@cmpi.edu.bd</p>
                </td>
                <td class="back-footer-cell footer-qr">
                    <div class="qr-box">
                        <img src="{{ $qrSrc }}" class="qr-img" alt="QR" />
                    </div>
                </td>
                <td class="back-footer-cell footer-sig">
                    @if($sigSrc)
                        <img src="{{ $sigSrc }}" style="height: 18px; width: auto; max-width: 60px; display: block; margin: 0 auto 3px auto; opacity: 0.9;" alt="Signature" />
                    @else
                        <div class="sig-text">DidarUllah</div>
                    @endif
                    <div class="sig-line">
                        <p class="sig-name">Ln. Md. Didar Ullah</p>
                        <p class="sig-title">Principal</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Footer Accent -->
        <div class="accent-footer-bar"></div>
    </div>

</body>
</html>
