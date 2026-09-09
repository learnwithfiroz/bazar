<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সামগ্রিক বাজার তালিকা ও খরচ রিপোর্ট - {{ date('d M, Y', strtotime($startDate)) }} হতে {{ date('d M, Y', strtotime($endDate)) }}</title>
    
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
            transition: all 0.2s;
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
        
        /* Colorful Luxury Executive Header */
        .header-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #064e3b 100%);
            color: #ffffff;
            padding: 24px 28px;
            border-radius: 16px;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-title h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
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

        /* 3 Colorful Metric KPI Cards */
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
            position: relative;
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
        .metric-sub {
            font-size: 10px;
            font-weight: 600;
            margin-top: 2px;
            opacity: 0.8;
        }

        /* Section Titles with Colorful Badges */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 24px 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }
        .section-title {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-tag {
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 8px;
            background: #ede9fe;
            color: #6b21a8;
            border: 1px solid #ddd6fe;
        }

        /* Colorful Modern Data Tables */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
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
            letter-spacing: 0.3px;
        }
        th.th-emerald {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        }
        th.th-indigo {
            background: linear-gradient(135deg, #3730a3 0%, #4338ca 100%);
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        tr:hover td {
            background-color: #f1f5f9;
        }
        
        .total-row th, .total-row td {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-weight: 900;
            font-size: 13px;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
            padding: 10px 12px;
        }

        /* Category Color Badges */
        .cat-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid transparent;
        }
        .cat-fish { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .cat-veg { background: #dcfce7; color: #166534; border-color: #86efac; }
        .cat-grocery { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .cat-fruits { background: #f3e8ff; color: #6b21a8; border-color: #d8b4fe; }
        .cat-other { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

        /* Grand Total Colorful Banner Box */
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
        .grand-banner-text {
            font-size: 13px;
            font-weight: 700;
            color: #a7f3d0;
        }
        .grand-banner-amount {
            font-size: 24px;
            font-weight: 900;
            color: #34d399;
            letter-spacing: -0.5px;
        }

        /* Official Signatures */
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
        .sig-sub {
            font-size: 10px;
            color: #64748b;
        }

        /* QR Code & Verification Tag */
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
    
    <!-- Top Print & Action Toolbar (Hidden when printing) -->
    <div class="no-print">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 18px;">🖨️</span>
            <strong>রঙিন সামগ্রিক বাজার রিপোর্ট ও আইটেম তালিকা (PDF Preview)</strong>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">📥 রঙিন PDF হিসেবে প্রিন্ট / সেভ করুন</button>
            <button onclick="window.close()" class="btn-close">বন্ধ করুন</button>
        </div>
    </div>

    <!-- Colorful Executive Header Banner -->
    <div class="header-banner">
        <div class="header-title">
            <span class="header-badge">👑 এক্সিকিউটিভ গৃহস্থালি ও প্রশাসন</span>
            <h1>প্রিন্সিপাল মহোদয়ের দপ্তর / গৃহস্থালি বাজার অডিট</h1>
            <h2>সামগ্রিক বাজার খরচের রিপোর্ট ও একত্রীকৃত পণ্যের তালিকা</h2>
            <p>তারিখ রেঞ্জ: {{ date('d F, Y (l)', strtotime($startDate)) }} হতে {{ date('d F, Y (l)', strtotime($endDate)) }}</p>
        </div>

        <!-- Verification QR Code -->
        <div class="qr-card">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode(url()->current()) }}" class="qr-img" alt="QR">
            <div style="text-align: left;">
                <span style="font-size: 9px; font-weight: 800; color: #065f46; display: block;">ডিজিটাল অডিট</span>
                <span style="font-size: 8px; color: #64748b;">ভেরিফায়েড রিপোর্ট</span>
            </div>
        </div>
    </div>

    <!-- 3 Colorful Top Metric Cards -->
    <div class="metrics-grid">
        <div class="metric-card metric-emerald">
            <span class="metric-label">💰 সর্বমোট বাজার খরচ</span>
            <div class="metric-value">৳ {{ number_format($totalAmount, 2) }}</div>
            <div class="metric-sub">নির্বাচিত সময়সীমার মোট টাকা</div>
        </div>

        <div class="metric-card metric-indigo">
            <span class="metric-label">📦 পণ্যের বৈচিত্র্য</span>
            <div class="metric-value">{{ $aggregatedItems->count() }} প্রকার পণ্য</div>
            <div class="metric-sub">একত্রিত আইটেমের মোট সংখ্যা</div>
        </div>

        <div class="metric-card metric-amber">
            <span class="metric-label">📋 ভাউচার ও মেমো সংখ্যা</span>
            <div class="metric-value">{{ $expenses->count() }} টি ভাউচার</div>
            <div class="metric-sub">{{ $expenses->pluck('expense_date')->unique()->count() }} দিনের বাজার বিবরণী</div>
        </div>
    </div>

    <!-- Section 1: Consolidated Aggregated Items Table (একত্রীকৃত পণ্যের মোট পরিমাণ) -->
    <div class="section-header">
        <div class="section-title">
            <span style="color: #059669;">🛒</span>
            <span>১. একত্রীকৃত বাজার পণ্যের বিবরণী (কোন পণ্য কতটুকু কেনা হয়েছে)</span>
        </div>
        <span class="section-tag">{{ $aggregatedItems->count() }} আইটেম</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="th-emerald text-center" style="width: 6%;">#</th>
                <th class="th-emerald" style="width: 34%;">বাজারের পণ্যের নাম</th>
                <th class="th-emerald" style="width: 18%;">ক্যাটাগরি</th>
                <th class="th-emerald text-center" style="width: 18%;">মোট ক্রয়কৃত পরিমাণ</th>
                <th class="th-emerald text-right" style="width: 24%;">সর্বমোট খরচ (৳)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aggregatedItems as $index => $item)
            @php
                $catClass = match($item->category) {
                    'মাছ ও মাংস' => 'cat-fish',
                    'শাকসবজি' => 'cat-veg',
                    'কাঁচাবাজার' => 'cat-veg',
                    'মুদিখানা' => 'cat-grocery',
                    'ফলমূল' => 'cat-fruits',
                    default => 'cat-other'
                };
            @endphp
            <tr>
                <td class="text-center font-bold">{{ $index + 1 }}</td>
                <td class="font-black" style="color: #0f172a;">{{ $item->item_name }}</td>
                <td>
                    <span class="cat-badge {{ $catClass }}">{{ $item->category ?: 'অন্যান্য' }}</span>
                </td>
                <td class="text-center font-bold" style="color: #065f46; font-size: 13px;">
                    {{ $item->total_quantity + 0 }} {{ $item->unit }}
                </td>
                <td class="text-right font-black" style="color: #0f172a; font-size: 13px;">
                    ৳ {{ number_format($item->total_amount, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #94a3b8;">কোনো বাজার পণ্য পাওয়া যায়নি।</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th colspan="4" class="text-right">একত্রিত পণ্যের সর্বমোট মূল্য:</th>
                <th class="text-right" style="color: #065f46; font-size: 14px;">৳ {{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Section 2: Date-wise Daily Breakdown (দিন অনুযায়ী ভাউচার ও মেমোর সংক্ষিপ্ত তালিকা) -->
    <div class="section-header" style="margin-top: 30px;">
        <div class="section-title">
            <span style="color: #3730a3;">📅</span>
            <span>২. দিন অনুযায়ী বাজার খরচ ও মেমোর বিবরণ</span>
        </div>
        <span class="section-tag" style="background: #e0e7ff; color: #3730a3; border-color: #c7d2fe;">{{ $expenses->count() }} টি এন্ট্রি</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="th-indigo text-center" style="width: 6%;">#</th>
                <th class="th-indigo" style="width: 18%;">তারিখ</th>
                <th class="th-indigo" style="width: 24%;">দোকান / বাজার (Vendor)</th>
                <th class="th-indigo" style="width: 20%;">মেমো নং / টাইটেল</th>
                <th class="th-indigo" style="width: 14%;">এন্ট্রি কারী</th>
                <th class="th-indigo text-right" style="width: 18%;">টাকার পরিমাণ (৳)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expIdx => $exp)
            <tr>
                <td class="text-center font-bold">{{ $expIdx + 1 }}</td>
                <td class="font-bold">{{ date('d M, Y', strtotime($exp->expense_date)) }}</td>
                <td>
                    @if($exp->vendor_name)
                    <strong style="color: #3730a3;">🏪 {{ $exp->vendor_name }}</strong>
                    @else
                    <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    <span class="font-bold">{{ $exp->title }}</span>
                    @if($exp->memo_no)
                    <div style="font-size: 10px; color: #059669; font-weight: bold;">মেমো: {{ $exp->memo_no }}</div>
                    @endif
                </td>
                <td>{{ $exp->creator->name }}</td>
                <td class="text-right font-black" style="color: #0f172a;">৳ {{ number_format($exp->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">কোনো খরচের ভাউচার পাওয়া যায়নি।</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th colspan="5" class="text-right">মোট খরচের অংক:</th>
                <th class="text-right" style="color: #3730a3; font-size: 14px;">৳ {{ number_format($totalAmount, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Grand Summary Highlight Banner -->
    <div class="grand-banner">
        <div>
            <div class="grand-banner-text">নির্বাচিত সময়সীমার সর্বমোট অনুমোদিত বাজার খরচ:</div>
            <div style="font-size: 11px; color: #d1fae5; margin-top: 2px;">(কথায়: {{ number_format($totalAmount, 2) }} টাকা মাত্র)</div>
        </div>
        <div class="grand-banner-amount">
            ৳ {{ number_format($totalAmount, 2) }}
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="signatures">
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">মেসেঞ্জার / বাজার প্রস্তুতকারী</div>
            <div class="sig-sub">সংশ্লিষ্ট কর্মকর্তা</div>
        </div>
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">যাচাইকারী (পিএ / ম্যানেজার)</div>
            <div class="sig-sub">দাপ্তরিক হিসাব নিরীক্ষক</div>
        </div>
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-title">অনুমোদনকারী (প্রিন্সিপাল মহোদয়)</div>
            <div class="sig-sub">প্রধান নির্বাহী কর্মকর্তা</div>
        </div>
    </div>

</div>

</body>
</html>
