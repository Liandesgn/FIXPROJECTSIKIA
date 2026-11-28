@php
    $ddRequired = $ddRequired ?? true;
    $ddHint     = $ddHint ?? null;

    $fieldClass = 'w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    $konfigurasi = [
        'placeholder' => $ddPlaceholder,
        'required'    => $ddRequired,
        'levels'      => $ddLevels,
        'hidden'      => array_map(fn ($h) => ['name' => $h['name'], 'level' => $h['level']], $ddHidden),
        'selected'    => array_map('strval', $ddSelected),
        'message'     => 'Harap pilih ' . mb_strtolower($ddLabel) . ' sampai selesai.',
    ];
@endphp

<div data-drill class="relative">
    <label for="{{ $ddId }}" class="{{ $labelClass }}">
        {{ $ddLabel }}
        @if ($ddRequired)
            <span class="text-[#C9517E]">*</span>
        @endif
    </label>

    {{-- Kolom yang diklik --}}
    <button type="button" id="{{ $ddId }}" data-drill-btn aria-haspopup="listbox" aria-expanded="false"
            class="{{ $fieldClass }} mt-1.5 flex items-center justify-between gap-3 text-left">
        <span data-drill-text class="min-w-0 flex-1 truncate text-slate-400">{{ $ddPlaceholder }}</span>
        <svg class="h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    {{-- Daftar pilihan bertingkat --}}
    <div data-drill-panel class="mt-2 hidden overflow-hidden rounded-xl border border-[#E99AB7] bg-white shadow-sm">
        <div class="flex items-start gap-2 border-b border-[#F1D5E0] bg-[#FCE7F0] px-3 py-2">
            <button type="button" data-drill-back
                    class="hidden shrink-0 rounded-lg border-2 border-[#E99AB7] bg-white px-2.5 py-1 text-xs font-bold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                ‹ Kembali
            </button>
            <div class="min-w-0 flex-1">
                <p data-drill-crumb class="hidden truncate text-xs font-semibold text-slate-600"></p>
                <p data-drill-title class="text-sm font-bold text-[#542D3D]"></p>
            </div>
        </div>

        <ul data-drill-list class="max-h-64 space-y-0.5 overflow-y-auto p-1.5"></ul>
    </div>

    {{-- Nilai yang dikirim ke BE --}}
    @foreach ($ddHidden as $h)
        <input type="hidden" name="{{ $h['name'] }}" value="{{ $h['value'] ?? '' }}" data-drill-hidden="{{ $h['name'] }}">
    @endforeach

    <p data-drill-msg class="{{ $errorClass }} hidden">{{ $konfigurasi['message'] }}</p>

    @foreach ($ddHidden as $h)
        @error($h['name'])
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    @endforeach

    @if ($ddHint)
        <p class="mt-1 text-xs font-medium text-slate-600">{{ $ddHint }}</p>
    @endif

    <script type="application/json" data-drill-config>{!! json_encode($konfigurasi, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                if (window.__sikiaDrill) return;
                window.__sikiaDrill = true;

                var CHEVRON = '<svg class="h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>';
                var CHECK   = '<svg class="h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>';

                function init(root) {
                    var cfg    = JSON.parse(root.querySelector('[data-drill-config]').textContent);
                    var btn    = root.querySelector('[data-drill-btn]');
                    var textEl = root.querySelector('[data-drill-text]');
                    var panel  = root.querySelector('[data-drill-panel]');
                    var back   = root.querySelector('[data-drill-back]');
                    var crumb  = root.querySelector('[data-drill-crumb]');
                    var title  = root.querySelector('[data-drill-title]');
                    var list   = root.querySelector('[data-drill-list]');
                    var msg    = root.querySelector('[data-drill-msg]');

                    var levels = cfg.levels;
                    var last   = levels.length - 1;
                    var cache  = {};
                    var committed = [];   // pilihan yang sudah final
                    var working   = [];   // pilihan yang sedang dipilih di dalam daftar
                    var depth = 0;
                    var seq   = 0;

                    function hiddenInput(name) {
                        return root.querySelector('input[data-drill-hidden="' + name + '"]');
                    }

                    // Ambil daftar pilihan untuk satu tingkat (statis atau dari server)
                    function getItems(i, parentValue) {
                        var lv = levels[i];

                        if (lv.items) {
                            return Promise.resolve(lv.items.filter(function (it) {
                                return i === 0 || it.parent === '' || String(it.parent) === String(parentValue);
                            }));
                        }

                        var key = i + ':' + parentValue;
                        if (cache[key]) return Promise.resolve(cache[key]);

                        var sep = lv.url.indexOf('?') === -1 ? '?' : '&';

                        return fetch(lv.url + sep + encodeURIComponent(lv.param) + '=' + encodeURIComponent(parentValue), {
                            headers: { Accept: 'application/json' }
                        })
                            .then(function (r) {
                                if (!r.ok) throw new Error('gagal memuat');
                                return r.json();
                            })
                            .then(function (data) {
                                var items = data.map(function (d) {
                                    return {
                                        value: String(d[lv.map.value]),
                                        label: (lv.map.prefix || '') + d[lv.map.label]
                                    };
                                });
                                cache[key] = items;
                                return items;
                            });
                    }

                    // Tampilkan hasil akhir di kolom dan isi input tersembunyi
                    function apply() {
                        var ok = committed.length === levels.length;
                        var teks = ok
                            ? committed.map(function (x) { return x.label; }).join(' › ')
                            : cfg.placeholder;

                        textEl.textContent = teks;
                        textEl.classList.toggle('text-slate-400', !ok);
                        textEl.classList.toggle('text-[#542D3D]', ok);
                        btn.title = ok ? teks : '';

            cfg.hidden.forEach(function (h) {
                var input = hiddenInput(h.name);

                if (input) {
                    input.value = ok && committed[h.level]
                        ? committed[h.level].value
                        : '';

                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            });

                        if (ok) msg.classList.add('hidden');
                    }

                    function render() {
                        var mySeq = ++seq;
                        var lv = levels[depth];
                        var parent = depth === 0 ? '' : working[depth - 1].value;

                        back.classList.toggle('hidden', depth === 0);
                        crumb.classList.toggle('hidden', depth === 0);
                        crumb.textContent = depth === 0
                            ? ''
                            : working.slice(0, depth).map(function (x) { return x.label; }).join(' › ');
                        title.textContent = lv.title;

                        list.innerHTML = '<li class="px-3 py-3 text-sm text-slate-500">Memuat...</li>';

                        getItems(depth, parent).then(function (items) {
                            if (mySeq !== seq) return;

                            list.innerHTML = '';

                            if (!items.length) {
                                list.innerHTML = '<li class="px-3 py-3 text-sm text-slate-500">Data belum tersedia.</li>';
                                return;
                            }

                            items.forEach(function (it) {
                                var aktif = working[depth] && String(working[depth].value) === String(it.value);

                                var li = document.createElement('li');
                                var b = document.createElement('button');
                                b.type = 'button';
                                b.disabled = !!it.disabled;
                                b.className = 'flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9517E] '
                                    + (it.disabled ? 'cursor-not-allowed text-slate-400 ' : 'text-[#542D3D] hover:bg-[#FCE7F0] ')
                                    + (aktif ? 'bg-[#FCE7F0] font-bold' : 'font-medium');

                                var span = document.createElement('span');
                                span.className = 'min-w-0 flex-1 break-words';
                                span.textContent = it.label;
                                b.appendChild(span);

                                if (!it.disabled) {
                                    b.insertAdjacentHTML('beforeend', depth < last ? CHEVRON : (aktif ? CHECK : ''));
                                }

                                b.addEventListener('click', function () { pilih(it); });

                                li.appendChild(b);
                                list.appendChild(li);
                            });
                        }).catch(function () {
                            if (mySeq !== seq) return;
                            list.innerHTML = '<li class="px-3 py-3 text-sm text-red-600">Gagal memuat pilihan. Coba lagi.</li>';
                        });
                    }

                    function pilih(it) {
                        working = working.slice(0, depth);
                        working.push(it);

                        if (depth < last) {
                            depth += 1;
                            render();
                        } else {
                            committed = working.slice();
                            apply();
                            close();
                        }
                    }

                    function open() {
                        working = committed.slice();
                        depth = committed.length === levels.length ? last : 0;
                        render();
                        panel.classList.remove('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                    }

                    function close() {
                        panel.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                    }

                    btn.addEventListener('click', function () {
                        if (panel.classList.contains('hidden')) open(); else close();
                    });

                    back.addEventListener('click', function () {
                        if (depth > 0) {
                            depth -= 1;
                            render();
                        }
                    });

                    // PERBAIKAN: klik pada pilihan membuat tombolnya terhapus dari halaman,
                    // jadi "apakah klik ada di dalam kolom" harus dicek dari jalur klik aslinya,
                    // bukan dari elemen yang sudah tidak ada.
                    document.addEventListener('click', function (e) {
                        var jalur = typeof e.composedPath === 'function' ? e.composedPath() : [];
                        var didalam = jalur.length
                            ? jalur.indexOf(root) !== -1
                            : (root.contains(e.target) || !e.target.isConnected);

                        if (!didalam) close();
                    });

                    root.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            close();
                            btn.focus();
                        }
                    });

                    // Cegah simpan kalau pilihan belum lengkap
                    var form = root.closest('form');
                    if (form && cfg.required) {
                        var utama = cfg.hidden.reduce(function (a, b) { return b.level > a.level ? b : a; });

                        form.addEventListener('submit', function (e) {
                            var input = hiddenInput(utama.name);
                            if (input && input.value === '') {
                                e.preventDefault();
                                msg.classList.remove('hidden');
                                open();
                                btn.focus();
                            }
                        });
                    }

                    // Pulihkan pilihan lama (halaman Edit atau setelah validasi gagal)
                    (async function () {
                        var sel = (cfg.selected || []).map(String);
                        if (sel.length !== levels.length || sel.some(function (v) { return v === ''; })) return;

                        try {
                            var path = [];
                            var parent = '';

                            for (var i = 0; i <= last; i++) {
                                var items = await getItems(i, parent);
                                var it = items.find(function (x) { return String(x.value) === sel[i]; });
                                if (!it) return;
                                path.push(it);
                                parent = it.value;
                            }

                            committed = path;
                            apply();
                        } catch (e) {
                            /* nilai dari server tetap dipakai */
                        }
                    })();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('[data-drill]').forEach(init);
                });
            })();
        </script>
    @endpush
@endonce