/**
 * INFORA Wilayah Dropdown Manager
 *
 * Reusable client-side controller that manages cascading Indonesian administrative
 * region dropdowns (Provinsi -> Kabupaten/Kota -> Kecamatan -> Kelurahan/Desa -> Kode Pos).
 * Eliminates repetitive fetch logic and boilerplate event listeners across forms and modals.
 *
 * Exposes window.InforaWilayah
 */

window.InforaWilayah = (function () {
    // In-memory cache for API results
    const cache = new Map();

    const config = {
        routes: {
            provinsi: '/wilayah/dropdown/provinsi',
            kabupaten: '/wilayah/dropdown/kabupaten',
            kecamatan: '/wilayah/dropdown/kecamatan',
            kelurahan: '/wilayah/dropdown/kelurahan',
        },
    };

    /**
     * Helper fetch wrapper with in-memory caching
     *
     * @param {string} url
     * @returns {Promise<any>}
     */
    async function cachedFetch(url) {
        if (cache.has(url)) {
            return cache.get(url);
        }

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            cache.set(url, data);
            return data;
        } catch (error) {
            console.error('[InforaWilayah] Gagal mengambil data wilayah:', error);
            throw error;
        }
    }

    /**
     * Low-level fetchers
     */
    function fetchProvinsi() {
        return cachedFetch(config.routes.provinsi);
    }

    function fetchKabupaten(provinsiId) {
        if (!provinsiId) return Promise.resolve([]);
        return cachedFetch(`${config.routes.kabupaten}?provinsi_id=${encodeURIComponent(provinsiId)}`);
    }

    function fetchKecamatan(kabupatenId) {
        if (!kabupatenId) return Promise.resolve([]);
        return cachedFetch(`${config.routes.kecamatan}?kabupaten_id=${encodeURIComponent(kabupatenId)}`);
    }

    function fetchKelurahan(kecamatanId) {
        if (!kecamatanId) return Promise.resolve([]);
        return cachedFetch(`${config.routes.kelurahan}?kecamatan_id=${encodeURIComponent(kecamatanId)}`);
    }

    function clearCache() {
        cache.clear();
    }

    /**
     * Helper to get SearchableSelect instance by element ID
     */
    function getSelect(id) {
        if (!id) return null;
        return window.SearchableSelect ? window.SearchableSelect.getInstance(id) : null;
    }

    /**
     * Resolve configuration options from a prefix or custom object
     */
    function resolveOptions(opts) {
        if (typeof opts === 'string') {
            opts = { prefix: opts };
        }
        opts = opts || {};
        const prefix = opts.prefix ? opts.prefix + '_' : '';

        return {
            prefix: opts.prefix || '',
            provinsiId: opts.provinsiId || (prefix + 'provinsi_id'),
            kabupatenId: opts.kabupatenId || (prefix + 'kabupaten_id'),
            kecamatanId: opts.kecamatanId || (prefix + 'kecamatan_id'),
            kelurahanId: opts.kelurahanId || (prefix + 'kelurahan_id'),
            postalCodeId: opts.postalCodeId || (prefix + 'postal_code'),
            provinceTextId: opts.provinceTextId || (prefix + 'province'),
            cityTextId: opts.cityTextId || (prefix + 'city'),
            districtTextId: opts.districtTextId || (prefix + 'district'),
            villageTextId: opts.villageTextId || (prefix + 'village'),
            syncTextInputs: opts.syncTextInputs !== false,
            autoPostalCode: opts.autoPostalCode !== false,
            onProvinsiChange: opts.onProvinsiChange || null,
            onKabupatenChange: opts.onKabupatenChange || null,
            onKecamatanChange: opts.onKecamatanChange || null,
            onKelurahanChange: opts.onKelurahanChange || null,
        };
    }

    /**
     * Otomatiskan cascading event listener untuk set dropdown wilayah
     *
     * @param {Object|string} options
     */
    function bindCascade(options) {
        const resolved = resolveOptions(options);

        document.addEventListener('searchable-select:change', function (e) {
            const inputId = e.target?.id;
            if (!inputId) return;

            const val = e.detail?.value;
            const text = e.detail?.text;
            const item = e.detail?.item;

            // 1. Ketika Provinsi berubah
            if (inputId === resolved.provinsiId) {
                if (resolved.syncTextInputs) {
                    const provText = document.getElementById(resolved.provinceTextId);
                    const cityText = document.getElementById(resolved.cityTextId);
                    const distText = document.getElementById(resolved.districtTextId);
                    const villText = document.getElementById(resolved.villageTextId);

                    if (provText) provText.value = text || '';
                    if (cityText) cityText.value = '';
                    if (distText) distText.value = '';
                    if (villText) villText.value = '';
                }

                const kabSelect = getSelect(resolved.kabupatenId);
                const kecSelect = getSelect(resolved.kecamatanId);
                const kelSelect = getSelect(resolved.kelurahanId);

                if (kabSelect) {
                    kabSelect.setOptions([]);
                    kabSelect.clear(false);
                }
                if (kecSelect) {
                    kecSelect.setOptions([]);
                    kecSelect.clear(false);
                    kecSelect.setDisabled(true);
                }
                if (kelSelect) {
                    kelSelect.setOptions([]);
                    kelSelect.clear(false);
                    kelSelect.setDisabled(true);
                }

                if (!val) {
                    if (kabSelect) kabSelect.setDisabled(true);
                    if (resolved.onProvinsiChange) resolved.onProvinsiChange(null, null);
                    return;
                }

                if (kabSelect) {
                    kabSelect.setDisabled(false);
                    kabSelect.setLoading(true);
                }

                fetchKabupaten(val)
                    .then((data) => {
                        const targetKab = getSelect(resolved.kabupatenId);
                        if (targetKab) {
                            targetKab.setOptions(data);
                            targetKab.setLoading(false);
                            targetKab.setDisabled(false);
                        }
                        if (resolved.onProvinsiChange) resolved.onProvinsiChange(val, data);
                    })
                    .catch((err) => {
                        const targetKab = getSelect(resolved.kabupatenId);
                        if (targetKab) targetKab.setLoading(false);
                    });
            }

            // 2. Ketika Kabupaten/Kota berubah
            else if (inputId === resolved.kabupatenId) {
                if (resolved.syncTextInputs) {
                    const cityText = document.getElementById(resolved.cityTextId);
                    const distText = document.getElementById(resolved.districtTextId);
                    const villText = document.getElementById(resolved.villageTextId);

                    if (cityText) cityText.value = text || '';
                    if (distText) distText.value = '';
                    if (villText) villText.value = '';
                }

                const kecSelect = getSelect(resolved.kecamatanId);
                const kelSelect = getSelect(resolved.kelurahanId);

                if (kecSelect) {
                    kecSelect.setOptions([]);
                    kecSelect.clear(false);
                }
                if (kelSelect) {
                    kelSelect.setOptions([]);
                    kelSelect.clear(false);
                    kelSelect.setDisabled(true);
                }

                if (!val) {
                    if (kecSelect) kecSelect.setDisabled(true);
                    if (resolved.onKabupatenChange) resolved.onKabupatenChange(null, null);
                    return;
                }

                if (kecSelect) {
                    kecSelect.setDisabled(false);
                    kecSelect.setLoading(true);
                }

                fetchKecamatan(val)
                    .then((data) => {
                        const targetKec = getSelect(resolved.kecamatanId);
                        if (targetKec) {
                            targetKec.setOptions(data);
                            targetKec.setLoading(false);
                            targetKec.setDisabled(false);
                        }
                        if (resolved.onKabupatenChange) resolved.onKabupatenChange(val, data);
                    })
                    .catch((err) => {
                        const targetKec = getSelect(resolved.kecamatanId);
                        if (targetKec) targetKec.setLoading(false);
                    });
            }

            // 3. Ketika Kecamatan berubah
            else if (inputId === resolved.kecamatanId) {
                if (resolved.syncTextInputs) {
                    const distText = document.getElementById(resolved.districtTextId);
                    const villText = document.getElementById(resolved.villageTextId);

                    if (distText) distText.value = text || '';
                    if (villText) villText.value = '';
                }

                const kelSelect = getSelect(resolved.kelurahanId);

                if (kelSelect) {
                    kelSelect.setOptions([]);
                    kelSelect.clear(false);
                }

                if (!val) {
                    if (kelSelect) kelSelect.setDisabled(true);
                    if (resolved.onKecamatanChange) resolved.onKecamatanChange(null, null);
                    return;
                }

                if (kelSelect) {
                    kelSelect.setDisabled(false);
                    kelSelect.setLoading(true);
                }

                fetchKelurahan(val)
                    .then((data) => {
                        const targetKel = getSelect(resolved.kelurahanId);
                        if (targetKel) {
                            targetKel.setOptions(data);
                            targetKel.setLoading(false);
                            targetKel.setDisabled(false);
                        }
                        if (resolved.onKecamatanChange) resolved.onKecamatanChange(val, data);
                    })
                    .catch((err) => {
                        const targetKel = getSelect(resolved.kelurahanId);
                        if (targetKel) targetKel.setLoading(false);
                    });
            }

            // 4. Ketika Kelurahan/Desa berubah
            else if (inputId === resolved.kelurahanId) {
                if (resolved.syncTextInputs) {
                    const villText = document.getElementById(resolved.villageTextId);
                    if (villText) villText.value = text || '';
                }

                if (resolved.autoPostalCode && val && item && item.kode_pos) {
                    const postalInput = document.getElementById(resolved.postalCodeId);
                    if (postalInput) postalInput.value = item.kode_pos;
                }

                if (resolved.onKelurahanChange) resolved.onKelurahanChange(val, item);
            }
        });
    }

    /**
     * Memuat dan mengisi (populate) rangkaian dropdown wilayah secara berantai
     * Cocok digunakan pada modal Edit saat form pertama kali dimuat.
     *
     * @param {Object|string} options
     * @param {Object} values - { provinsi_id, kabupaten_id, kecamatan_id, kelurahan_id, postal_code, province, city, district, village }
     * @returns {Promise<void>}
     */
    async function populateCascade(options, values) {
        const resolved = resolveOptions(options);
        values = values || {};

        const provId = values.provinsi_id || '';
        const kabId = values.kabupaten_id || '';
        const kecId = values.kecamatan_id || '';
        const kelId = values.kelurahan_id || '';

        const provSelect = getSelect(resolved.provinsiId);
        const kabSelect = getSelect(resolved.kabupatenId);
        const kecSelect = getSelect(resolved.kecamatanId);
        const kelSelect = getSelect(resolved.kelurahanId);

        // 1. Set Nilai Provinsi
        if (provSelect) {
            const provName = values.province || values.provinsi?.nama || '';
            const provKode = values.provinsi?.kode || '';
            provSelect.setValue(provId, provName, provKode, false);
        }

        // 2. Load dan Set Nilai Kabupaten
        if (provId && kabSelect) {
            kabSelect.setDisabled(false);
            kabSelect.setLoading(true);

            try {
                const kabData = await fetchKabupaten(provId);
                kabSelect.setOptions(kabData, kabId);
                kabSelect.setLoading(false);

                // 3. Load dan Set Nilai Kecamatan
                if (kabId && kecSelect) {
                    kecSelect.setDisabled(false);
                    kecSelect.setLoading(true);

                    const kecData = await fetchKecamatan(kabId);
                    kecSelect.setOptions(kecData, kecId);
                    kecSelect.setLoading(false);

                    // 4. Load dan Set Nilai Kelurahan
                    if (kecId && kelSelect) {
                        kelSelect.setDisabled(false);
                        kelSelect.setLoading(true);

                        const kelData = await fetchKelurahan(kecId);
                        kelSelect.setOptions(kelData, kelId);
                        kelSelect.setLoading(false);
                    } else if (kelSelect) {
                        kelSelect.setOptions([]);
                        kelSelect.clear(false);
                        kelSelect.setDisabled(true);
                    }
                } else if (kecSelect) {
                    kecSelect.setOptions([]);
                    kecSelect.clear(false);
                    kecSelect.setDisabled(true);
                }
            } catch (error) {
                if (kabSelect) kabSelect.setLoading(false);
                if (kecSelect) kecSelect.setLoading(false);
                if (kelSelect) kelSelect.setLoading(false);
                console.error('[InforaWilayah] Gagal mempopulasi cascade wilayah:', error);
            }
        } else {
            if (kabSelect) {
                kabSelect.setOptions([]);
                kabSelect.clear(false);
                kabSelect.setDisabled(true);
            }
            if (kecSelect) {
                kecSelect.setOptions([]);
                kecSelect.clear(false);
                kecSelect.setDisabled(true);
            }
            if (kelSelect) {
                kelSelect.setOptions([]);
                kelSelect.clear(false);
                kelSelect.setDisabled(true);
            }
        }
    }

    /**
     * Memulihkan opsi bertingkat saat terjadi validasi error (old values)
     *
     * @param {Object|string} options
     * @param {Object} oldValues - { provinsi_id, kabupaten_id, kecamatan_id, kelurahan_id }
     */
    async function restoreOnError(options, oldValues) {
        if (!oldValues || !oldValues.provinsi_id) return;
        return populateCascade(options, oldValues);
    }

    return {
        fetchProvinsi,
        fetchKabupaten,
        fetchKecamatan,
        fetchKelurahan,
        clearCache,
        bindCascade,
        populateCascade,
        restoreOnError,
    };
})();
