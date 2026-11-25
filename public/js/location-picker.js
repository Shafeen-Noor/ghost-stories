document.querySelectorAll('.location-picker').forEach((picker) => {
    const country = picker.querySelector('[name="country_code"]');
    const region = picker.querySelector('[name="region_code"]');
    const saved = picker.querySelector('[name="location_id"]');
    const status = picker.querySelector('[data-region-status]');
    let pending;

    function clearRegion() {
        pending?.abort();
        region.replaceChildren(new Option('Country only', ''));
        region.disabled = true;
        status.textContent = '';
    }

    country.addEventListener('change', async () => {
        clearRegion();
        if (saved) saved.value = '';
        if (!country.value) return;
        const selectedCountry = country.value;
        pending = new AbortController();
        status.textContent = 'Loading regions…';

        try {
            const response = await fetch(picker.dataset.regionUrl.replace('__COUNTRY__', encodeURIComponent(selectedCountry)), {
                signal: pending.signal,
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Unable to load regions');
            const data = await response.json();
            if (country.value !== selectedCountry) return;
            Object.entries(data.regions).forEach(([code, name]) => region.add(new Option(name, code)));
            region.disabled = Object.keys(data.regions).length === 0;
            status.textContent = region.disabled ? 'You can select this country without a region.' : '';
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Regions could not be loaded. You can still select this country.';
            }
        }
    });

    saved?.addEventListener('change', () => {
        if (!saved.value) return;
        country.value = '';
        clearRegion();
    });
});
