<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan - {{ $notaId }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.35;
        }
        /* Tombol print melayang (hanya muncul di layar) */
        .no-print-bar {
            position: fixed;
            top: 12px;
            right: 12px;
            z-index: 9999;
            display: flex;
            gap: 8px;
            background: white;
            padding: 8px 12px;
            border-radius: 9999px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            border: 1px solid #cbd5e1;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-print {
            background-color: #4f46e5;
            color: white;
        }
        .btn-back {
            background-color: #e2e8f0;
            color: #334155;
        }

        /* Lembar A4 (210mm x 297mm) */
        .a4-sheet {
            width: 210mm;
            height: 297mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            box-sizing: border-box;
        }

        /* Setengah lembar: A5 Landscape (210mm x 148.5mm) */
        .receipt-half {
            height: 148.5mm;
            max-height: 148.5mm;
            padding: 9mm 14mm 6mm 14mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Garis pemotong tengah */
        .cut-divider {
            position: absolute;
            top: 148.5mm;
            left: 0;
            right: 0;
            height: 0;
            border-top: 1.5px dashed #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .cut-divider-badge {
            background: #f8fafc;
            border: 1px dashed #94a3b8;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 12px;
            border-radius: 9999px;
            letter-spacing: 0.5px;
            transform: translateY(-50%);
            text-transform: uppercase;
        }

        /* Header Nota */
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 7px;
            margin-bottom: 7px;
        }
        .brand-info h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .brand-info p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #475569;
        }
        .brand-info .contact {
            margin-top: 2px;
            font-size: 9px;
            font-weight: 600;
            color: #334155;
        }

        .meta-info {
            text-align: right;
        }
        .meta-badge {
            display: inline-block;
            background: #0f172a;
            color: white;
            font-size: 8.5px;
            font-weight: 800;
            padding: 2.5px 8px;
            border-radius: 4px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .badge-pelanggan {
            background: #047857; /* Emerald */
        }
        .badge-toko {
            background: #1e3a8a; /* Deep Blue */
        }
        .meta-nota-no {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .meta-date {
            font-size: 9.5px;
            color: #475569;
            margin-top: 1px;
        }

        /* Detail Bar (Kasir & Status Bayar) */
        .info-bar {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 5px 8px;
            margin-bottom: 8px;
            font-size: 9.5px;
        }
        .info-item strong {
            color: #0f172a;
        }

        /* Tabel Produk */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 6px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .items-table .unit-title {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
        }
        .items-table .unit-imei {
            font-family: ui-monospace, monospace;
            font-size: 9px;
            color: #64748b;
        }

        /* Footer & Totaling */
        .receipt-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-top: 4px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
        .payment-status-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 8px;
            font-size: 9px;
        }
        .status-pill {
            display: inline-block;
            font-weight: 800;
            padding: 1.5px 6px;
            border-radius: 3px;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .pill-cash { background: #d1fae5; color: #065f46; }
        .pill-credit { background: #f3e8ff; color: #6b21a8; }
        .pill-qris { background: #dbeafe; color: #1e40af; }
        .pill-transfer { background: #e0e7ff; color: #3730a3; }

        .totals-box {
            width: 50mm;
            flex-shrink: 0;
            font-size: 9.5px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 1.5px 0;
            color: #475569;
        }
        .totals-row.grand-total {
            border-top: 1.5px solid #0f172a;
            margin-top: 3px;
            padding-top: 3px;
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Tanda Tangan & Catatan */
        .signatures-terms {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px dashed #cbd5e1;
        }
        .terms {
            max-width: 60%;
            font-size: 8px;
            color: #64748b;
            line-height: 1.25;
        }
        .terms ul {
            margin: 2px 0 0 0;
            padding-left: 12px;
        }
        .signature-cols {
            display: flex;
            gap: 20px;
            text-align: center;
        }
        .sig-box {
            width: 25mm;
            font-size: 8.5px;
        }
        .sig-space {
            height: 12mm;
        }
        .sig-name {
            font-weight: 700;
            border-top: 1px solid #94a3b8;
            padding-top: 1px;
            color: #1e293b;
        }

        @media print {
            body {
                background: white;
            }
            .no-print-bar {
                display: none !important;
            }
            .a4-sheet {
                margin: 0;
                box-shadow: none;
                width: 210mm;
                height: 297mm;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol cetak & navigasi -->
    <div class="no-print-bar">
        <button class="btn-action btn-print" onclick="window.print()">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Nota A4 (2 Rangkap)
        </button>
        <a href="{{ route('tenant.pos.index') }}" class="btn-action btn-back">
            Kembali ke Kasir
        </a>
    </div>

    <!-- Halaman Kertas A4 -->
    <div class="a4-sheet">

        <!-- ================= BAGIAN ATAS: UNTUK PELANGGAN ================= -->
        <div class="receipt-half">
            <div>
                <!-- Header -->
                <div class="receipt-header">
                    <div class="brand-info">
                        <h1>{{ $tenant->nama_toko }}</h1>
                        <p>{{ $tenant->alamat ?? 'Ketapang, Kalimantan Barat' }}</p>
                        <p class="contact">Telepon / WhatsApp: {{ $tenant->no_telp ?? '-' }}</p>
                    </div>
                    <div class="meta-info">
                        <span class="meta-badge badge-pelanggan">LEMBAR 1: ASLI (PELANGGAN)</span>
                        <div class="meta-nota-no">#{{ $notaId }}</div>
                        <div class="meta-date">{{ $createdAt->format('d/m/Y - H:i:s') }} WIB</div>
                    </div>
                </div>

                <!-- Info Bar -->
                <div class="info-bar">
                    <div class="info-item">Kasir: <strong>{{ $kasirName }}</strong></div>
                    <div class="info-item">Pelanggan: <strong>Umum / Retail</strong></div>
                    <div class="info-item">
                        Metode: 
                        @if($paymentMethod === 'credit')
                            <strong style="color: #6b21a8;">KREDIT LEASING ({{ $creditProviders[$creditProvider] ?? ucfirst($creditProvider ?? 'Leasing') }})</strong>
                        @elseif($paymentMethod === 'cash')
                            <strong style="color: #065f46;">TUNAI (CASH)</strong>
                        @elseif($paymentMethod === 'qris')
                            <strong style="color: #1e40af;">QRIS</strong>
                        @elseif($paymentMethod === 'transfer')
                            <strong style="color: #3730a3;">TRANSFER BANK</strong>
                        @else
                            <strong>{{ strtoupper($paymentMethod) }}</strong>
                        @endif
                    </div>
                </div>

                <!-- Tabel Item Transaksi -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">No</th>
                            <th style="text-align: left;">Deskripsi Handphone & Identitas IMEI</th>
                            <th style="width: 50px; text-align: center;">Qty</th>
                            <th style="width: 90px; text-align: right;">Harga Satuan</th>
                            <th style="width: 75px; text-align: right;">Potongan</th>
                            <th style="width: 100px; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $t)
                            <tr>
                                <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                                <td>
                                    <div class="unit-title">{{ $t->inventory?->product?->brand }} {{ $t->inventory?->product?->tipe_hp }}</div>
                                    <div class="unit-imei">IMEI: {{ $t->inventory?->imei ?? '-' }}</div>
                                </td>
                                <td style="text-align: center; font-weight: 600;">1 unit</td>
                                <td style="text-align: right;">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                                <td style="text-align: right; color: #dc2626;">
                                    {{ $t->discount_amount > 0 ? '-Rp '.number_format($t->discount_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">
                                    Rp {{ number_format($t->final_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bagian Bawah Nota 1: Summary + Tanda Tangan -->
            <div>
                <div class="receipt-summary">
                    <div class="payment-status-box">
                        <div style="font-weight: 700; margin-bottom: 2px;">RINCIAN KETENTUAN PEMBAYARAN:</div>
                        @if($paymentMethod === 'credit')
                            <div>Mitra Pembiayaan: <span class="status-pill pill-credit">{{ $creditProviders[$creditProvider] ?? ucfirst($creditProvider) }}</span></div>
                            <div style="margin-top: 3px;">
                                Status Uang Muka: 
                                @if($isDp && $dpAmount > 0)
                                    <strong style="color: #1d4ed8;">DP Diterima Rp {{ number_format($dpAmount, 0, ',', '.') }} (LUNAS)</strong>
                                @else
                                    <strong style="color: #475569;">TANPA DP (0 RUPIAH)</strong>
                                @endif
                            </div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                                Sisa tagihan diangsur langsung ke pihak leasing sesuai akad resmi yang disetujui.
                            </div>
                        @elseif($paymentMethod === 'cash')
                            <div>Status: <span class="status-pill pill-cash">LUNAS TUNAI</span> (Rp {{ number_format($finalTotal, 0, ',', '.') }})</div>
                        @elseif($paymentMethod === 'mixed')
                            <div>Status: <span class="status-pill pill-cash">PEMBAYARAN CAMPUR</span></div>
                            <div style="font-size: 8.5px; margin-top: 1px;">
                                Cash: Rp {{ number_format($payCash, 0, ',', '.') }} | QRIS: Rp {{ number_format($payQris, 0, ',', '.') }} | TF: Rp {{ number_format($payTransfer, 0, ',', '.') }}
                            </div>
                        @else
                            <div>Status: <span class="status-pill pill-qris">LUNAS {{ strtoupper($paymentMethod) }}</span></div>
                        @endif
                    </div>

                    <div class="totals-box">
                        <div class="totals-row">
                            <span>Subtotal Bruto:</span>
                            <span>Rp {{ number_format($gross, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="totals-row" style="color: #dc2626;">
                                <span>Total Diskon:</span>
                                <span>-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="totals-row grand-total">
                            <span>TOTAL NOTA:</span>
                            <span>Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
                        </div>
                        @if($paymentMethod === 'credit' && $isDp)
                            <div class="totals-row" style="color: #1d4ed8; font-weight: 700; margin-top: 2px;">
                                <span>DP Dibayar:</span>
                                <span>Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="signatures-terms">
                    <div class="terms">
                        <strong>Ketentuan Garansi &amp; Layanan:</strong>
                        <ul>
                            <li>Garansi resmi berlaku di Service Center resmi masing-masing brand di seluruh Indonesia.</li>
                            <li>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan uang tanpa persetujuan toko.</li>
                            <li>Simpan nota pembelian fisik ini sebagai bukti sah klaim garansi.</li>
                        </ul>
                    </div>
                    <div class="signature-cols">
                        <div class="sig-box">
                            <div>Penerima / Pembeli,</div>
                            <div class="sig-space"></div>
                            <div class="sig-name">( ............................ )</div>
                        </div>
                        <div class="sig-box">
                            <div>Kasir Toko,</div>
                            <div class="sig-space"></div>
                            <div class="sig-name">{{ $kasirName }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= GARIS POTONG TENGAH ================= -->
        <div class="cut-divider">
            <span class="cut-divider-badge">✂ POTONG DI SINI (Gunting Tengah Lembar A4) ✂</span>
        </div>

        <!-- ================= BAGIAN BAWAH: UNTUK ARSIP TOKO ================= -->
        <div class="receipt-half">
            <div>
                <!-- Header -->
                <div class="receipt-header">
                    <div class="brand-info">
                        <h1>{{ $tenant->nama_toko }}</h1>
                        <p>{{ $tenant->alamat ?? 'Ketapang, Kalimantan Barat' }}</p>
                        <p class="contact">Telepon / WhatsApp: {{ $tenant->no_telp ?? '-' }}</p>
                    </div>
                    <div class="meta-info">
                        <span class="meta-badge badge-toko">LEMBAR 2: ARSIP KASIR &amp; TOKO</span>
                        <div class="meta-nota-no">#{{ $notaId }}</div>
                        <div class="meta-date">{{ $createdAt->format('d/m/Y - H:i:s') }} WIB</div>
                    </div>
                </div>

                <!-- Info Bar -->
                <div class="info-bar">
                    <div class="info-item">Kasir: <strong>{{ $kasirName }}</strong></div>
                    <div class="info-item">Arsip: <strong>Administrasi Penjualan &amp; Stok</strong></div>
                    <div class="info-item">
                        Metode: 
                        @if($paymentMethod === 'credit')
                            <strong style="color: #6b21a8;">KREDIT LEASING ({{ $creditProviders[$creditProvider] ?? ucfirst($creditProvider ?? 'Leasing') }})</strong>
                        @elseif($paymentMethod === 'cash')
                            <strong style="color: #065f46;">TUNAI (CASH)</strong>
                        @elseif($paymentMethod === 'qris')
                            <strong style="color: #1e40af;">QRIS</strong>
                        @elseif($paymentMethod === 'transfer')
                            <strong style="color: #3730a3;">TRANSFER BANK</strong>
                        @else
                            <strong>{{ strtoupper($paymentMethod) }}</strong>
                        @endif
                    </div>
                </div>

                <!-- Tabel Item Transaksi -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">No</th>
                            <th style="text-align: left;">Deskripsi Handphone & Identitas IMEI</th>
                            <th style="width: 50px; text-align: center;">Qty</th>
                            <th style="width: 90px; text-align: right;">Harga Satuan</th>
                            <th style="width: 75px; text-align: right;">Potongan</th>
                            <th style="width: 100px; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $t)
                            <tr>
                                <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                                <td>
                                    <div class="unit-title">{{ $t->inventory?->product?->brand }} {{ $t->inventory?->product?->tipe_hp }}</div>
                                    <div class="unit-imei">IMEI: {{ $t->inventory?->imei ?? '-' }}</div>
                                </td>
                                <td style="text-align: center; font-weight: 600;">1 unit</td>
                                <td style="text-align: right;">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                                <td style="text-align: right; color: #dc2626;">
                                    {{ $t->discount_amount > 0 ? '-Rp '.number_format($t->discount_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">
                                    Rp {{ number_format($t->final_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bagian Bawah Nota 2: Summary + Tanda Tangan Arsip -->
            <div>
                <div class="receipt-summary">
                    <div class="payment-status-box">
                        <div style="font-weight: 700; margin-bottom: 2px;">AUDIT KEUANGAN TOKO:</div>
                        @if($paymentMethod === 'credit')
                            <div>Mitra Pembiayaan: <span class="status-pill pill-credit">{{ $creditProviders[$creditProvider] ?? ucfirst($creditProvider) }}</span></div>
                            <div style="margin-top: 3px;">
                                Setoran DP Kasir: 
                                @if($isDp && $dpAmount > 0)
                                    <strong style="color: #1d4ed8;">Wajib Setor Kas: Rp {{ number_format($dpAmount, 0, ',', '.') }}</strong>
                                @else
                                    <strong style="color: #475569;">Tanpa DP (0 Kas Tunai)</strong>
                                @endif
                            </div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                                Klaim pencairan unit diajukan ke kantor perwakilan leasing bersangkutan.
                            </div>
                        @elseif($paymentMethod === 'cash')
                            <div>Penerimaan: <span class="status-pill pill-cash">MASUK LACI KASIR</span> (Rp {{ number_format($finalTotal, 0, ',', '.') }})</div>
                        @elseif($paymentMethod === 'mixed')
                            <div>Penerimaan: <span class="status-pill pill-cash">CAMPUR</span></div>
                            <div style="font-size: 8.5px; margin-top: 1px;">
                                Fisik Laci: Rp {{ number_format($payCash, 0, ',', '.') }} | Bank/QRIS: Rp {{ number_format($payQris + $payTransfer, 0, ',', '.') }}
                            </div>
                        @else
                            <div>Penerimaan: <span class="status-pill pill-qris">REKENING / QRIS TOKO</span></div>
                        @endif
                    </div>

                    <div class="totals-box">
                        <div class="totals-row">
                            <span>Subtotal Bruto:</span>
                            <span>Rp {{ number_format($gross, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="totals-row" style="color: #dc2626;">
                                <span>Total Diskon:</span>
                                <span>-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="totals-row grand-total">
                            <span>TOTAL NOTA:</span>
                            <span>Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
                        </div>
                        @if($paymentMethod === 'credit' && $isDp)
                            <div class="totals-row" style="color: #1d4ed8; font-weight: 700; margin-top: 2px;">
                                <span>DP Kasir:</span>
                                <span>Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="signatures-terms">
                    <div class="terms">
                        <strong>Catatan Administrasi Internal Toko:</strong>
                        <ul>
                            <li>Stok fisik unit IMEI di atas otomatis terpotong (Sold) dari database inventori.</li>
                            <li>Nota arsip ini disimpan bersama rekap lembar tutup shift harian kasir.</li>
                        </ul>
                    </div>
                    <div class="signature-cols">
                        <div class="sig-box">
                            <div>Penerima / Pembeli,</div>
                            <div class="sig-space"></div>
                            <div class="sig-name">( ............................ )</div>
                        </div>
                        <div class="sig-box">
                            <div>Kasir / Admin Toko,</div>
                            <div class="sig-space"></div>
                            <div class="sig-name">{{ $kasirName }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
