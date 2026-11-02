/**
 * Scanner IMEI — Opsi A: native BarcodeDetector API + fallback ZXing.
 *
 * Kenapa native: BarcodeDetector di Chrome Android decode di platform
 * (bukan JS loop per frame), jadi jauh lebih cepat dan hemat baterai
 * dibanding html5-qrcode yang mendekode SEMUA symbology tiap frame.
 *
 * Aturan yang dipakai:
 *  - hanya format 1D (barcode label IMEI): EAN_13, CODE_128, CODE_39, ITF, CODABAR
 *  - decode sepenuhnya di HP — nol beban server
 *  - ZXing hanya dimuat kalau BarcodeDetector tidak tersedia
 */
(function (global) {
    'use strict';

    var FORMATS = ['ean_13', 'ean_8', 'code_128', 'code_39', 'itf', 'codabar'];

    var ImeiScanner = {
        /** mode: 'single' (POS: stop setelah 1 hasil) | 'multi' (Inventori: terus scan) */
        create: function (elementId, mode, onResult, onStatus) {
            var video = null, stream = null, detector = null, fallback = null;
            var raf = null, lastValue = '', busy = false, stopped = false;

            function status(msg) { if (onStatus) onStatus(msg); }

            function supported() {
                return 'BarcodeDetector' in global && global.BarcodeDetector.getSupportedFormats;
            }

            async function nativeStart() {
                var formats = await global.BarcodeDetector.getSupportedFormats();
                var use = FORMATS.filter(function (f) { return formats.indexOf(f) !== -1; });
                if (!use.length) throw new Error('browser tidak mendukung barcode 1D');
                detector = new global.BarcodeDetector({ formats: use });
                status('Mode native (cepat) · arahkan ke barcode IMEI');
                loop();
            }

            async function fallbackStart() {
                if (!global.ZXing) throw new Error('library fallback belum termuat');
                fallback = new global.ZXing.BrowserMultiFormatReader();
                // Batasi ke symbology 1D supaya tidak membuang waktu decode QR/2D.
                var hints = new Map();
                hints.set(global.ZXing.DecodeHintType.POSSIBLE_FORMATS,
                    [global.ZXing.BarcodeFormat.EAN_13, global.ZXing.BarcodeFormat.CODE_128,
                     global.ZXing.BarcodeFormat.CODE_39, global.ZXing.BarcodeFormat.ITF,
                     global.ZXing.BarcodeFormat.CODABAR]);
                hints.set(global.ZXing.DecodeHintType.TRY_HARDER, false);
                fallback.hints = hints;
                status('Mode kompatibilitas · arahkan ke barcode IMEI');
                loopFallback();
            }

            function loop() {
                if (stopped) return;
                raf = requestAnimationFrame(loop);
                if (busy || !video || !detector) return;
                busy = true;
                detector.detect(video).then(function (codes) {
                    if (!busy) return;
                    busy = false;
                    for (var i = 0; i < codes.length; i++) {
                        var raw = codes[i].rawValue;
                        if (!raw) continue;
                        if (raw === lastValue) return;
                        lastValue = raw;
                        emit(raw);
                        return;
                    }
                }).catch(function () { busy = false; });
            }

            function loopFallback() {
                if (stopped) return;
                raf = requestAnimationFrame(loopFallback);
                if (busy || !video || !fallback) return;
                busy = true;
                try {
                    fallback.decodeFromVideoElement(video, function (result) {
                        busy = false;
                        if (!result) return;
                        var raw = result.text || (result.getText && result.getText());
                        if (!raw || raw === lastValue) return;
                        lastValue = raw;
                        emit(raw);
                    }, function () { busy = false; });
                } catch (e) { busy = false; }
            }

            function emit(raw) {
                if (navigator.vibrate) navigator.vibrate(60);
                onResult(raw);
                if (mode === 'single') stop();
            }

            async function start() {
                stopped = false;
                status('Meminta izin kamera...');
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                        audio: false
                    });
                } catch (e) {
                    status('Kamera tidak bisa dibuka: ' + (e && e.message ? e.message : e));
                    throw e;
                }
                video = document.createElement('video');
                video.setAttribute('playsinline', '');
                video.muted = true;
                video.srcObject = stream;
                document.getElementById(elementId).appendChild(video);
                await video.play();

                if (supported()) { await nativeStart(); return; }
                status('Memuat scanner cadangan...');
                await loadScript('https://unpkg.com/@zxing/browser@0.1.5/umd/index.min.js');
                await fallbackStart();
            }

            async function stop() {
                stopped = true;
                if (raf) cancelAnimationFrame(raf);
                raf = null;
                if (fallback && fallback.reset) { try { fallback.reset(); } catch (e) {} }
                fallback = null; detector = null;
                if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
                stream = null;
                if (video && video.parentNode) video.parentNode.removeChild(video);
                video = null; lastValue = ''; busy = false;
                status('');
            }

            return {
                start: start,
                stop: stop,
                resetLast: function () { lastValue = ''; },
                isNative: function () { return !!detector; }
            };
        }
    };

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = src; s.async = true;
            s.onload = resolve; s.onerror = function () { reject(new Error('gagal memuat ' + src)); };
            document.head.appendChild(s);
        });
    }

    global.ImeiScanner = ImeiScanner;
})(window);
