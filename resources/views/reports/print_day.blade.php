<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>দৈনিক খরচের ভাউচার - {{ date('d M, Y', strtotime($date)) }}</title>
    
    <!-- SolaimanLipi & Google Fonts -->
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'SolaimanLipi', 'Hind Siliguri', sans-serif;
            font-size: 13px;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .no-print {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
        }
        .btn-print {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .btn-close {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 9px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 12px;
            font-family: inherit;
        }
        
        /* Colorful Executive Header Banner */
        .header-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #064e3b 100%);
            color: #ffffff;
            padding: 24px 28px;
            border-radius: 16px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
        }
        .header-title h2 {
            margin: 4px 0 0 0;
            font-size: 15px;
            color: #34d399;
            font-weight: 800;
        }
        .header-title p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #94a3b8;
        }
        .header-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            color: #fef08a;
            margin-bottom: 6px;
        }

        /* 3 Colorful Metric Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }
        .metric-card {
            padding: 14px 18px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
        }
        .metric-emerald {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #a7f3d0;
            color: #065f46;
        }
        .metric-indigo {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border-color: #c7d2fe;
            color: #3730a3;
        }
        .metric-amber {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fde68a;
            color: #92400e;
        }
        .metric-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
            opacity: 0.85;
        }
        .metric-value {
            font-size: 20px;
            font-weight: 900;
            line-height: 1.2;
        }

        /* Voucher Header Ribbon */
        .voucher-ribbon {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 5px solid #059669;
            padding: 10px 16px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 18px;
            font-size: 12px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        th {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            font-weight: 800;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .total-row th, .total-row td {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-weight: 900;
            font-size: 13px;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
            padding: 10px 12px;
        }

        /* Slips Box */
        .slips-container {
            margin-top: 12px;
            padding: 14px;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            page-break-inside: avoid;
        }
        .slips-title {
            font-weight: 800;
            font-size: 12px;
            color: #1e293b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .slips-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        .slip-img {
            max-width: 380px;
            max-height: 480px;
            border: 2px solid #e2e8f0;
            padding: 4px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Grand Total Banner */
        .grand-banner {
            background: linear-gradient(135deg, #0f172a 0%, #134e4a 50%, #064e3b 100%);
            color: #ffffff;
            padding: 16px 24px;
            border-radius: 16px;
            margin: 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px -4px rgba(19, 78, 74, 0.4);
        }
        .grand-banner-amount {
            font-size: 24px;
            font-weight: 900;
            color: #34d399;
        }

        /* QR Card */
        .qr-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .qr-img {
            width: 55px;
            height: 55px;
            border-radius: 6px;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 55px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 220px;
        }
        .sig-line {
            border-top: 2px solid #0f172a;
            margin-bottom: 6px;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 800;
            color: #334155;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-black { font-weight: 900; }
        .font-bold { font-weight: 700; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; background: #fff; font-size: 12px; }
            .container { max-width: 100%; border: none; box-shadow: none; padding: 0; }
            @page { margin: 10mm 12mm; size: A4 portrait; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Action Bar -->
    <div class="no-print">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 18px;">🖨️</span>
            <strong>রঙিন দৈনিক বাজার ভাউচার (PDF Preview)</strong>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">📥 রঙিন PDF প্রিন্ট / সেভ করুন</button>
            <button onclick="window.close()" class="btn-close">বন্ধ করুন</button>
        </div>
    </div>

    <!-- Header Banner -->
    <div class="header-banner">
        <div class="header-title">
            <span class="header-badge">👑 এক্সিকিউটিভ প্রশাসন ও গৃহস্থালি</span>
            <h1>প্রিন্সিপাল মহোদয়ের দপ্তর / এক্সিকিউটিভ ভাউচার</h1>
            <h2>দৈনিক বাজার খরচ ও ফান্ড ভাউচার হিসাব বিবরণী</h2>
            <p>ভাউচার তারিখ: {{ date('d F, Y (l)', strtotime($date)) }} • ডিজিটাল অডিট সিস্টেম</p>
        </div>

        <!-- Verification QR Code -->
        <div class="qr-card">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode(url()->current()) }}" class="qr-img" alt="QR">
            <div style="text-align: left;">
                <span style="font-size: 9px; font-weight: 800; color: #065f46; display: block;">ডিজিটাল ভাউচার</span>
                <span style="font-size: 8px; color: #64748b;">ভেরিফায়েড মেমো</span>
            </div>
        </div>
    </div>

    <!-- 3 Metric Cards -->
    <div class="metrics-grid">
        <div class="metric-card metric-emerald">
            <span class="metric-label">💰 আজকের সর্বমোট খরচ</span>
            <div class="metric-value">৳ {{ number_format($totalAmount, 2) }}</div>
        </div>

        <div class="metric-card metric-indigo">
            <span class="metric-label">📋 মোট ভাউচার সংখ্যা</span>
            <div class="metric-value">{{ $expenses->count() }} টি ভাউচার</div>
        </div>

        <div class="metric-card metric-amber">
            <span class="metric-label">📅 তারিখ বিবরণী</span>
            <div class="metric-value" style="font-size: 16px;">{{ date('d M, Y', strtotime($date)) }}</div>
        </div>
    </div>

    <!-- Expenses List -->
    @forelse($expenses as $expIndex => $expense)
    <div style="margin-bottom: 26px; page-break-inside: avoid;">
        <div class="voucher-ribbon">
            <div>
                <span style="font-size: 14px; color: #0f172a;">ভাউচার #{{ $expense->id }}: <strong>{{ $expense->title }}</strong></span>
                @if($expense->vendor_name)
                <span style="color: #4338ca; margin-left: 6px;">(🏪 দোকান: {{ $expense->vendor_name }})</span>
                @endif
                @if($expense->memo_no)
                <span style="color: #059669; margin-left: 6px;">(মেমো নং: {{ $expense->memo_no }})</span>
                @endif
            </div>
            <div style="font-size: 11px; color: #64748b;">
                👤 এন্ট্রি কারী: <strong>{{ $expense->creator->name }}</strong>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 6%;" class="text-center">#</th>
                    <th style="width: 36%;">বাজার পণ্যের বিবরণ</th>
                    <th style="width: 18%;">ক্যাটাগরি</th>
                    <th style="width: 16%;" class="text-center">পরিমাণ ও একক</th>
                    <th style="width: 12%;" class="text-right">একক দর (৳)</th>
                    <th style="width: 12%;" class="text-right">মোট টাকা (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expense->items as $itemIndex => $item)
                <tr>
                    <td class="text-center font-bold">{{ $itemIndex + 1 }}</td>
                    <td class="font-black" style="color: #0f172a;">{{ $item->item_name }}</td>
                    <td><span style="font-size: 10px; font-weight: bold; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $item->category }}</span></td>
                    <td class="text-center font-bold" style="color: #065f46;">{{ $item->quantity + 0 }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right font-black" style="color: #0f172a;">৳ {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <th colspan="5" class="text-right">ভাউচার সাবটোটাল:</th>
                    <th class="text-right" style="color: #065f46; font-size: 14px;">৳ {{ number_format($expense->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Cash Memo / Slip Photos -->
        @if($expense->slips->isNotEmpty())
        <div class="slips-container">
            <div class="slips-title">
                <span>📸</span>
                <span>সংযুক্ত ক্যাশ মেমো / রসিদের ছবি (ভাউচার #{{ $expense->id }})</span>
            </div>
            <div class="slips-grid">
                @foreach($expense->slips as $slip)
                <div>
                    <img src="{{ asset('storage/' . $slip->image_path) }}" class="slip-img" alt="ক্যাশ মেমো">
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @empty
    <div style="text-align: center; padding: 40px; color: #64748b;">
        এই তারিখে কোনো বাজার খরচের হিসাব পাওয়া যায়নি।
    </div>
    @endforelse

    <!-- Grand Summary Banner -->
    @if($expenses->isNotEmpty())
    <div class="grand-banner">
        <div>
            <div style="font-size: 13px; font-weight: 700; color: #a7f3d0;">দৈনিক সর্বমোট অনুমোদিত বাজার খরচ:</div>
            <div style="font-size: 11px; color: #d1fae5; margin-top: 2px;">(কথায়: {{ number_format($totalAmount, 2) }} টাকা মাত্র)</div>
        </div>
        <div class="grand-banner-amount">
            ৳ {{ number_format($totalAmount, 2) }}
        </div>
    </div>

    <!-- Official Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">মেসেঞ্জার / বাজার প্রস্তুতকারী</div>
        </div>
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">যাচাইকারী (পিএ / ম্যানেজার)</div>
        </div>
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">অনুমোদনকারী (প্রিন্সিপাল মহোদয়)</div>
        </div>
    </div>
    @endif

</div>

</body>
</html>
