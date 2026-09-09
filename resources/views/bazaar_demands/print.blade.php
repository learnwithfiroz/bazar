<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বাজারের শপিং ডিমান্ড প্রিন্ট - {{ $demand->title }}</title>
    
    <!-- SolaimanLipi & Google Fonts -->
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">
    
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'SolaimanLipi', 'Hind Siliguri', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #0f172a;
            background: #fff;
            margin: 20px;
            line-height: 1.5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .no-print {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .btn-print {
            background: #4338ca;
            color: white;
            border: none;
            padding: 9px 22px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
        }
        .btn-close {
            background: #475569;
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-family: inherit;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #0f172a;
            font-weight: 800;
        }
        .header h2 {
            margin: 4px 0 0 0;
            font-size: 15px;
            color: #4338ca;
            font-weight: 700;
        }
        .meta-grid {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .checkbox-cell {
            width: 30px;
            height: 20px;
            border: 2px solid #64748b;
            display: inline-block;
            border-radius: 4px;
        }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 10px; font-size: 12px; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Action Bar -->
    <div class="no-print">
        <div>
            <strong>🛍️ বাজার শপিং চেকলিস্ট প্রিন্ট</strong> ({{ $demand->title }})
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">🖨️ প্রিন্ট করুন</button>
            <button onclick="window.close()" class="btn-close">বন্ধ করুন</button>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>প্রিন্সিপাল মহোদয়ের দপ্তর / এক্সিকিউটিভ পরিবার</h1>
        <h2>দৈনিক বাজার শপিং চেকলিস্ট ও ডিমান্ড</h2>
        <p>তালিকা: {{ $demand->title }} • তারিখ: {{ date('d F, Y (l)', strtotime($demand->target_date)) }}</p>
    </div>

    <!-- Metadata Grid -->
    <div class="meta-grid">
        <div><strong>তৈরি করেছেন:</strong> {{ $demand->creator->name }}</div>
        <div><strong>মেসেঞ্জার:</strong> {{ $demand->assignee ? $demand->assignee->name : 'নির্ধারণ করা হয়নি' }}</div>
        <div><strong>মোট আইটেম:</strong> {{ $demand->items->count() }} টি</div>
        <div><strong>অগ্রগতি:</strong> {{ $demand->purchased_count }}/{{ $demand->total_items_count }} কেনা হয়েছে</div>
    </div>

    <!-- Checklist Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">টিক (✓)</th>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 40%;">পণ্যের নাম ও বিবরণ</th>
                <th style="width: 20%;">ক্যাটাগরি</th>
                <th style="width: 24%;" class="text-center">প্রয়োজনীয় পরিমাণ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demand->items as $idx => $item)
            <tr>
                <td class="text-center">
                    @if($item->is_purchased)
                    <strong style="color: #15803d; font-size: 14px;">✓</strong>
                    @else
                    <div class="checkbox-cell"></div>
                    @endif
                </td>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong style="{{ $item->is_purchased ? 'text-decoration: line-through; color: #64748b;' : '' }}">{{ $item->item_name }}</strong>
                    @if($item->notes)
                    <div style="font-size: 10px; color: #64748b;">নোট: {{ $item->notes }}</div>
                    @endif
                </td>
                <td>{{ $item->category }}</td>
                <td class="text-center font-bold">{{ $item->quantity + 0 }} {{ $item->unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($demand->notes)
    <div style="margin-top: 15px; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 11px;">
        <strong>বিশেষ নির্দেশনা:</strong> {{ $demand->notes }}
    </div>
    @endif

    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 11px;">
        <div>তৈরি কারীর স্বাক্ষর: _________________</div>
        <div>মেসেঞ্জারের স্বাক্ষর: _________________</div>
    </div>

</div>

</body>
</html>
