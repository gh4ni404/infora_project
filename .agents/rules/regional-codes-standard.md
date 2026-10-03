# Administrative Regional Codes Standard (KEMENDAGRI)

## 1. Mandatory Usage of Kemendagri Standard (Not BPS)
- All administrative regional entity codes across the INFORA platform (Provinsi, Kabupaten/Kota, Kecamatan, and Kelurahan/Desa) **MUST** adhere to official codes issued by the **Ministry of Home Affairs of the Republic of Indonesia (Kementerian Dalam Negeri / Kemendagri)**.
- Strictly prohibited from using statistical region codes (Wilkerstat) from the Central Bureau of Statistics (Badan Pusat Statistik / BPS) as primary regional entity codes.

## 2. Warning on Code Discrepancies (BPS vs Kemendagri)
BPS and Kemendagri regional codes frequently differ significantly at the kabupaten/kota level. Never assume the two code systems are identical:
- **Sulawesi Selatan (Provinsi Code 73):**
  - Kabupaten Bone: Kemendagri = `7308` (BPS = `7311`)
  - Kabupaten Maros: Kemendagri = `7309` (BPS = `7308`)
  - Kabupaten Pangkajene dan Kepulauan: Kemendagri = `7310` (BPS = `7309`)
  - Kabupaten Barru: Kemendagri = `7311` (BPS = `7310`)
  - Kabupaten Luwu Timur: Kemendagri = `7324` (BPS = `7325`)
- **DKI Jakarta (Provinsi Code 31):**
  - Kota Administrasi Jakarta Pusat: Kemendagri = `3171` (BPS = `3173`)
  - Kota Administrasi Jakarta Utara: Kemendagri = `3172` (BPS = `3175`)
  - Kota Administrasi Jakarta Barat: Kemendagri = `3173` (BPS = `3174`)
  - Kota Administrasi Jakarta Selatan: Kemendagri = `3174` (BPS = `3171`)
  - Kota Administrasi Jakarta Timur: Kemendagri = `3175` (BPS = `3172`)

## 3. Official Validation References
When mapping or seeding new regional data:
1. Use the official BPS-Kemendagri bridging service on the BPS SIG portal:
   - URL: `https://sig.bps.go.id/bridging-kode/index`
   - Endpoint: `/rest-bridging-dagri/getwilayah?level={level}&parent={parent_code}`
   - Always extract the **`kode_dagri`** attribute; never use `kode_bps`.
2. Or consult official decrees: Kepmendagri No. 100.1.1-6117 / Permendagri No. 72 Year 2019 / Permendagri No. 137 Year 2017.

## 4. Storage Format & Prefix Consistency
- Store regional codes as clean numeric strings without punctuation or periods (e.g. Kemendagri `73.08` is stored as `7308`).
- Ensure the first 2 digits of kabupaten/kota codes always match the parent provinsi code.

## 5. Kelurahan vs Desa Standards & Verification (Tingkat 4)
- Tingkat 4 Kemendagri regional codes consist of 10 digits (e.g. `7308182003`):
  - 2 digits: Provinsi (`73`)
  - 2 digits: Kabupaten/Kota (`08`)
  - 2 digits: Kecamatan (`18`)
  - 4 digits: Kelurahan/Desa entity:
    - **Kelurahan** starts with digit `1` (e.g. `1001` -> `7308181001` Pompanua).
    - **Desa** starts with digit `2` (e.g. `2003` -> `7308182003` Welado).
- When seeding or validating kecamatan sub-regions, **ALWAYS** verify the complete list of kelurahan/desa from the official bridging endpoint:
  `https://sig.bps.go.id/rest-bridging-dagri/getwilayah?level=desa&parent={dotted_kecamatan_code}`
- Never truncate or skip intermediate kelurahan/desa (such as Welado, Pinceng Pute, Labissa), as doing so shifts sequential numbering for subsequent regions.

---

## 6. Client-Side Cascading Regional Dropdown Standard (InforaWilayah)
- All administrative forms requiring cascading regional selection (Provinsi ➔ Kabupaten/Kota ➔ Kecamatan ➔ Kelurahan/Desa) **MUST** use the native client module `window.InforaWilayah` from `resources/js/infora-wilayah.js`.
- **Strict Prohibition of Duplicate Raw Fetch Implementations:**
  - Never write standalone `fetch('/api/wilayah/...')` calls, nested manual `change` event listeners, or ad-hoc local in-template cache stores in Blade views.
- **Canonical InforaWilayah API Utilization:**
  - `InforaWilayah.bindCascade(config)` for cascading dropdown chain initialization and automatic postal code field synchronization.
  - `InforaWilayah.populateCascade(config, values)` for instant population of Edit/Modal forms without AJAX race conditions.
  - `InforaWilayah.restoreOnError(config, oldValues)` for seamless tiered state recovery upon server validation failure (`$errors->any()`).
