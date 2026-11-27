window.startGoogleMysteryMap = async root => {
    const original = [...root.childNodes];
    const maps = await new Promise((resolve, reject) => {
        if (window.google?.maps?.Map) { resolve(window.google.maps); return; }
        window.mysteryGoogleReady = () => resolve(window.google.maps);
        window.gm_authFailure = () => reject(new Error('Maps authentication failed'));
        const script = document.createElement('script');
        const params = new URLSearchParams({key: root.dataset.googleKey, loading: 'async', callback: 'mysteryGoogleReady', v: 'quarterly'});
        script.src = 'https://maps.googleapis.com/maps/api/js?' + params;
        script.async = true;
        script.onerror = () => reject(new Error('Maps could not load'));
        document.head.append(script);
    });
    const canvas = document.createElement('div');
    canvas.className = 'google-map-canvas';
    root.prepend(canvas);
    const picker = root.hasAttribute('data-map-picker');
    const pins = window.mysteryPins || [];
    let chosen;
    let located;
    const map = new maps.Map(canvas, {center: {lat: 41.39, lng: 2.17}, zoom: 12, mapTypeControl: false, streetViewControl: false, fullscreenControl: true, gestureHandling: 'greedy'});
    root.querySelector('.map-attribution')?.remove();
    root.querySelector('.map-tiles')?.remove();
    const markers = root.querySelector('.map-pins');
    const status = root.querySelector('.map-hint') || document.querySelector('[data-pin-label]');
    const message = text => { if (status) status.textContent = text; };
    const showStories = members => {
        const detail = document.querySelector('[data-map-detail]');
        const list = document.querySelector('.map-story-list');
        if (!detail || !list) return;
        detail.textContent = `${members.length} ${members.length === 1 ? 'story' : 'stories'} in this area`;
        let page = 0;
        const render = () => {
            list.replaceChildren();
            for (const pin of members.slice(page * 20, (page + 1) * 20)) {
                const link = document.createElement('a');
                link.href = pin.url;
                const title = document.createElement('strong'); title.textContent = pin.title;
                const place = document.createElement('span'); place.textContent = pin.place;
                link.append(title, place); list.append(link);
            }
            if (members.length > 20) {
                const controls = document.createElement('div'); controls.className = 'map-list-pages';
                for (const [label, step, disabled] of [['Previous', -1, page === 0], ['Next', 1, (page + 1) * 20 >= members.length]]) {
                    const button = document.createElement('button'); button.type = 'button'; button.textContent = label; button.disabled = disabled;
                    button.onclick = () => { page += step; render(); }; controls.append(button);
                }
                list.append(controls);
            }
        };
        render();
    };
    const choose = point => {
        chosen = {latitude: point.lat(), longitude: point.lng(), title: 'Selected location'};
        document.querySelector('[name="latitude"]').value = chosen.latitude.toFixed(6);
        document.querySelector('[name="longitude"]').value = chosen.longitude.toFixed(6);
        root.dispatchEvent(new Event('input', {bubbles: true}));
        message('Pin selected');
        overlay.draw();
    };
    const overlay = new maps.OverlayView();
    overlay.onAdd = () => {};
    overlay.onRemove = () => markers.replaceChildren();
    overlay.draw = () => {
        const projection = overlay.getProjection();
        if (!projection) return;
        const displayed = picker ? (chosen ? [chosen] : []) : pins;
        const points = displayed.map(pin => {
            const point = projection.fromLatLngToContainerPixel(new maps.LatLng(pin.latitude, pin.longitude));
            return {pin, x: point.x, y: point.y};
        }).filter(point => point.x >= -56 && point.y >= -56 && point.x <= root.clientWidth + 56 && point.y <= root.clientHeight + 56);
        markers.replaceChildren();
        for (const group of window.groupMapPins(points)) {
            const count = group.members.length;
            const button = document.createElement('button'); button.type = 'button';
            button.className = 'map-pin' + (count > 1 ? ' map-cluster' : '');
            button.textContent = count > 1 ? String(count) : '●';
            button.style.left = group.x + 'px'; button.style.top = group.y + 'px';
            button.setAttribute('aria-label', count > 1 ? `${count} stories in this area` : group.members[0].title);
            button.onclick = () => { if (!picker) showStories(group.members); };
            markers.append(button);
        }
        if (located) {
            const point = projection.fromLatLngToContainerPixel(located);
            const dot = document.createElement('span'); dot.className = 'map-current-location';
            dot.style.left = point.x + 'px'; dot.style.top = point.y + 'px'; markers.append(dot);
        }
    };
    overlay.setMap(map);
    const fit = () => {
        if (!pins.length) return;
        const bounds = new maps.LatLngBounds();
        pins.forEach(pin => bounds.extend({lat: pin.latitude, lng: pin.longitude}));
        map.fitBounds(bounds, 60);
        maps.event.addListenerOnce(map, 'idle', () => { if (map.getZoom() > 16) map.setZoom(16); });
    };
    if (!picker) fit();
    if (picker) {
        const lat = document.querySelector('[name="latitude"]').value;
        const lng = document.querySelector('[name="longitude"]').value;
        if (lat !== '' && lng !== '') {
            chosen = {latitude: Number(lat), longitude: Number(lng), title: 'Selected location'};
            map.setCenter({lat: chosen.latitude, lng: chosen.longitude}); map.setZoom(16);
        }
        map.addListener('click', event => choose(event.latLng));
    }
    root.querySelectorAll('[data-map-zoom]').forEach(button => button.onclick = () => map.setZoom(map.getZoom() + Number(button.dataset.mapZoom)));
    root.querySelector('[data-map-reset]')?.addEventListener('click', fit);
    root.querySelector('[data-map-choose-center]')?.addEventListener('click', () => choose(map.getCenter()));
    root.addEventListener('map:clear', () => { chosen = null; overlay.draw(); });
    root.querySelector('[data-map-locate]')?.addEventListener('click', event => {
        if (!window.isSecureContext || !navigator.geolocation) { message('Open this site over HTTPS to use your location.'); return; }
        const button = event.currentTarget; button.disabled = true; message('Finding your location…');
        navigator.geolocation.getCurrentPosition(position => {
            located = new maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(located); map.setZoom(16);
            if (picker) choose(located);
            overlay.draw(); button.disabled = false; message('Location found');
        }, error => {
            button.disabled = false; message(error.code === 1 ? 'Location permission denied. Choose a place manually.' : 'Location unavailable. Try again.');
        }, {enableHighAccuracy: true, timeout: 10000, maximumAge: 60000});
    });
    const observer = new ResizeObserver(() => { maps.event.trigger(map, 'resize'); overlay.draw(); });
    observer.observe(root);
    window.gm_authFailure = () => {
        observer.disconnect(); overlay.setMap(null); canvas.remove(); root.replaceChildren(...original);
        root.removeAttribute('data-google-key'); window.startLocalMysteryMap();
    };
};
