function posScanner() {
    return {
        scanning: false, scanStatus: '', manualImei: '', error: null, success: null, scanner: null,
        cart: [],
        trxUsed: {{ $transactionUsed ?? 0 }},
        upsell: false, processing: false, checkoutError: null, waLink: null,
        discountType: 'nominal', discountValue: 0,
        method: 'cash', pay: { cash: 0, qris: 0, transfer: 0 },

        init() {
            this.$watch('scanning', (v) => { if (!v) this.stopScan(); });
            // sinkron ulang saat online kembali / halaman dibuka
            window.addEventListener('online', () => this.flushQueue());
            if (navigator.onLine) this.flushQueue();
        },

        rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); },

        // --- antrian offline: transaksi gagal kirim (internet putus) disimpan di
        // localStorage, dikirim ulang otomatis saat koneksi kembali.
        queueKey() { return 'pos_offline_queue_' + {{ $shift?->id ?? 0 }}; },
        loadQueue() { try { return JSON.parse(localStorage.getItem(this.queueKey()) || '[]'); } catch (e) { return []; } },
        saveQueue(q) { try { localStorage.setItem(this.queueKey(), JSON.stringify(q)); } catch (e) {} },
        enqueue(payload) { const q = this.loadQueue(); q.push(payload); this.saveQueue(q); return q.length; },
        async flushQueue() {
            const q = this.loadQueue();
            if (!q.length || !navigator.onLine) return;
            const left = [];
            for (const payload of q) {
                try {
                    const res = await fetch('{{ route('tenant.pos.checkout') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify(payload),
                    });
                    const body = await res.json().catch(() => ({}));
                    if (res.ok && body.ok) { this.trxUsed = body.transaction_used ?? this.trxUsed; }
                    else { left.push(payload); }
                } catch (e) { left.push(payload); }
            }
            this.saveQueue(left);
            if (q.length && !left.length) { this.success = 'Sinkron: ' + q.length + ' transaksi offline terkirim.'; }
            else if (left.length) { this.error = left.length + ' transaksi masih menunggu koneksi.'; }
        },

        gross() { return this.cart.reduce((s, c) => s + (Number(c.price_sell) || 0), 0); },
        discountAmount() {
            const g = this.gross();
            if (!g) return 0;
            const v = Number(this.discountValue) || 0;
            if (v <= 0) return 0;
            return this.discountType === 'percent' ? Math.round(g * Math.min(100, v) / 100) : Math.min(v, g);
        },
        total() { return Math.max(0, this.gross() - this.discountAmount()); },

        setMethod(m) {
            this.method = m;
            this.pay = { cash: 0, qris: 0, transfer: 0 };
        },
        methodLabel() {
            return { cash: 'Cash', qris: 'QRIS', transfer: 'Transfer', mixed: 'Campur (multi)' }[this.method] || this.method;
        },

        async startScan() {
            this.error = null; this.success = null; this.scanning = true;
            this.scanStatus = 'Meminta izin kamera...';
            await this.$nextTick();
            try {
                this.scanner = ImeiScanner.create('scanner-region', 'single',
                    (raw) => this.onDetected(raw),
                    (msg) => { this.scanStatus = msg; });
                await this.scanner.start();
                this.scanStatus = 'Kamera aktif — arahkan ke barcode IMEI.';
            } catch (e) { this.scanning = false; this.scanStatus = ''; this.error = 'Kamera tidak bisa dibuka: ' + (e?.message || e); }
        },

        async stopScan() {
            this.scanning = false;
            if (this.scanner) { await this.scanner.stop(); this.scanner = null; }
            this.scanStatus = '';
        },

        onDetected(text) {
            const imei = String(text).replace(/\D/g, '');
            if (!imei) return;
            this.stopScan();
            this.lookup(imei);
        },

        lookupManual() {
            const imei = this.manualImei.replace(/\D/g, '');
            if (!imei) { this.error = 'Isi nomor IMEI dulu.'; return; }
            this.lookup(imei);
        },

        async lookup(imei) {
            this.error = null; this.success = null;
            try {
                const res = await fetch('{{ route('tenant.pos.lookup') }}?imei=' + encodeURIComponent(imei), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const body = await res.json().catch(() => ({}));
                if (!res.ok) { this.error = body.message || ('IMEI ' + imei + ' tidak ditemukan di stok toko ini.'); return; }
                this.addToCart(body);
            } catch (e) { this.error = 'Gagal memeriksa IMEI: ' + (e?.message || e); }
        },

        addToCart(item) {
            if (this.cart.find(c => c.inventory_id === item.inventory_id)) { this.error = 'IMEI ' + item.imei + ' sudah ada di nota.'; return; }
            this.cart.push({ inventory_id: item.inventory_id, imei: item.imei, tipe_hp: item.tipe_hp, brand: item.brand, price_sell: item.price_sell });
            this.manualImei = '';
            this.success = 'Ditambahkan: ' + item.tipe_hp + (item.brand ? ' ' + item.brand : '');
        },

        removeItem(i) { this.cart.splice(i, 1); },
        clearCart() { this.cart = []; this.discountValue = 0; this.pay = { cash: 0, qris: 0, transfer: 0 }; },

        confirmCheckout() {
            if (!this.cart.length) return;
            if (this.method === 'mixed' && (this.pay.cash + this.pay.qris + this.pay.transfer) !== this.total()) {
                this.error = 'Jumlah breakdown pembayaran harus sama dengan total.'; return;
            }
            this.checkoutError = null; this.waLink = null; this.upsell = true;
        },
        closeUpsell() { this.upsell = false; this.checkoutError = null; },

        async processCheckout() {
            this.processing = true; this.checkoutError = null;
            try {
                const res = await fetch('{{ route('tenant.pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        items: this.cart.map(c => c.inventory_id),
                        discount_type: this.discountType,
                        discount_value: this.discountValue,
                        payment_method: this.method,
                        payment: this.pay,
                    }),
                });
                const body = await res.json().catch(() => ({}));
                if (!res.ok || !body.ok) {
                    this.checkoutError = body.message || 'Checkout gagal.';
                    this.waLink = body.wa_link || null;
                    return;
                }
                this.upsell = false;
                this.cart = []; this.discountValue = 0; this.pay = { cash: 0, qris: 0, transfer: 0 };
                if (typeof body.transaction_used === 'number') this.trxUsed = body.transaction_used;
                this.success = body.message + ' · Nota ' + (body.nota_id || '') + ' · Total ' + this.rupiah(body.total_price)
                    + (body.transaction_limit ? ' · transaksi ' + body.transaction_used + '/' + body.transaction_limit : '');
                if (navigator.vibrate) navigator.vibrate([40, 60, 40]);
            } catch (e) {
                // internet putus / server tak terjangkau: simpan ke antrian offline
                this.enqueue({
                    items: this.cart.map(c => c.inventory_id),
                    discount_type: this.discountType,
                    discount_value: this.discountValue,
                    payment_method: this.method,
                    payment: this.pay,
                    queued_at: new Date().toISOString(),
                });
                this.upsell = false;
                this.cart = []; this.discountValue = 0; this.pay = { cash: 0, qris: 0, transfer: 0 };
                this.success = 'Tersimpan di antrian offline. Akan dikirim otomatis saat internet kembali.';
                if (navigator.vibrate) navigator.vibrate([40, 60, 40]);
            } finally { this.processing = false; }
        },
    };
}
