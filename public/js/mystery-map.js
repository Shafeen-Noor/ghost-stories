(() => {
    const map = document.querySelector('[data-mystery-map]');
    if (!map) return;
    const pins = window.mysteryPins || [];
    const picker = map.hasAttribute('data-map-picker');
    const tiles = map.querySelector('.map-tiles');
    const markers = map.querySelector('.map-pins');
    let zoom = picker ? 3 : 4;
    let center = project(30, 5);
    let drag;
    let moved = false;
    let selected;
    let frame;
    const tileCache = new Map();
    function project(lat, lon) {
        const sine = Math.sin(Math.max(-85, Math.min(85, lat)) * Math.PI / 180);
        return {x: (lon + 180) / 360, y: .5 - Math.log((1 + sine) / (1 - sine)) / (4 * Math.PI)};
    }
    function unproject(x, y) {
        return {latitude: Math.atan(Math.sinh(Math.PI * (1 - 2 * y))) * 180 / Math.PI, longitude: ((x * 360 - 180 + 540) % 360) - 180};
    }
    function render() {
        if(!map.clientWidth || !map.clientHeight)return;
        center.y = Math.max(0, Math.min(1, center.y)); center.x = (center.x + 1) % 1;
        const n = 2 ** zoom, size = n * 256;
        const left = center.x * size - map.clientWidth / 2, top = center.y * size - map.clientHeight / 2;
        const visible = new Set();
        for (let y = Math.floor(top / 256); y <= Math.floor((top + map.clientHeight) / 256); y++) {
            if (y < 0 || y >= n) continue;
            for (let x = Math.floor(left / 256); x <= Math.floor((left + map.clientWidth) / 256); x++) {
                const wrappedX = ((x % n) + n) % n;
                const key = `${zoom}/${x}/${y}`; visible.add(key);
                let image = tileCache.get(key);
                if (!image) { image = document.createElement('img'); image.alt=''; image.draggable=false; image.src=`https://tile.openstreetmap.org/${zoom}/${wrappedX}/${y}.png`; tileCache.set(key,image); tiles.append(image); }
                image.style.transform=`translate(${Math.round(x * 256 - left)}px,${Math.round(y * 256 - top)}px)`;
            }
        }
        for (const [key, image] of tileCache) if (!visible.has(key)) { image.remove(); tileCache.delete(key); }
        markers.replaceChildren();
        const displayed = picker && selected ? [{...selected, id:'chosen', title:'Selected location'}] : pins;
        displayed.forEach((pin, i) => {
            const point = project(pin.latitude, pin.longitude);
            let dx = point.x - center.x; if (dx > .5) dx -= 1; if (dx < -.5) dx += 1;
            const x = dx * size + map.clientWidth / 2, y = (point.y - center.y) * size + map.clientHeight / 2;
            if (x < -40 || y < -40 || x > map.clientWidth + 40 || y > map.clientHeight + 40) return;
            const button = document.createElement('button'); button.type='button'; button.className='map-pin pin-' + (pin.category || 'selected'); button.style.left=x+'px'; button.style.top=y+'px'; button.textContent=picker?'●':String(i+1); button.title=pin.title; button.setAttribute('aria-label',pin.title);
            button.addEventListener('click', () => { if (!picker) show(pin); }); markers.append(button);
        });
    }
    function show(pin) {
        center=project(pin.latitude,pin.longitude); zoom=Math.max(zoom,6); render();
        const detail = document.querySelector('[data-map-detail]'); if (!detail) return;
        const title=document.createElement('h3'), place=document.createElement('p'), link=document.createElement('a');
        title.textContent=pin.title; place.textContent=pin.place; link.textContent='Read this mystery ↗'; link.href=pin.url; link.className='button'; detail.replaceChildren(title,place,link);
        document.querySelectorAll('[data-map-story]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.mapStory===String(pin.id))));
    }
    map.querySelectorAll('[data-map-zoom]').forEach(button => button.addEventListener('click',()=>{ zoom=Math.max(2,Math.min(15,zoom+Number(button.dataset.mapZoom)));render(); }));
    map.querySelector('[data-map-reset]')?.addEventListener('click',()=>{zoom=4;center=project(30,5);render();});
    function choosePin(point) {
        selected=point;
        document.querySelector('[name="latitude"]').value=point.latitude.toFixed(6);
        document.querySelector('[name="longitude"]').value=point.longitude.toFixed(6);
        document.querySelector('[data-pin-label]').textContent='Map pin: '+point.latitude.toFixed(3)+', '+point.longitude.toFixed(3);
        map.dispatchEvent(new Event('input', {bubbles:true}));render();
    }
    map.querySelector('[data-map-choose-center]')?.addEventListener('click',()=>choosePin(unproject(center.x,center.y)));
    map.addEventListener('map:clear',()=>{selected=null;render();});
    map.addEventListener('pointerdown',event=>{ if(event.target.closest('button,a'))return; drag={x:event.clientX,y:event.clientY,center:{...center}};moved=false; map.setPointerCapture(event.pointerId); });
    map.addEventListener('pointermove',event=>{ if(!drag)return; const dx=event.clientX-drag.x,dy=event.clientY-drag.y; if(Math.abs(dx)+Math.abs(dy)>5)moved=true; const size=256*2**zoom;center={x:drag.center.x-dx/size,y:drag.center.y-dy/size}; if(!frame)frame=requestAnimationFrame(()=>{frame=null;render();}); });
    map.addEventListener('pointerup',event=>{ if(!drag)return;drag=null;if(picker&&!moved){const rect=map.getBoundingClientRect(),size=256*2**zoom;choosePin(unproject(center.x+(event.clientX-rect.left-map.clientWidth/2)/size,center.y+(event.clientY-rect.top-map.clientHeight/2)/size));}render(); });
    map.addEventListener('pointercancel',()=>{drag=null;render();});
    map.addEventListener('keydown',event=>{if(event.target!==map)return;const step=100/(256*2**zoom);if(event.key==='ArrowLeft')center.x-=step;else if(event.key==='ArrowRight')center.x+=step;else if(event.key==='ArrowUp')center.y-=step;else if(event.key==='ArrowDown')center.y+=step;else if(event.key==='+'||event.key==='=')zoom=Math.min(15,zoom+1);else if(event.key==='-')zoom=Math.max(2,zoom-1);else return;event.preventDefault();render();});
    document.querySelectorAll('[data-map-story]').forEach(button=>button.addEventListener('click',()=>{const pin=pins.find(p=>String(p.id)===button.dataset.mapStory);if(pin)show(pin);}));
    if(picker){const lat=document.querySelector('[name="latitude"]').value,lon=document.querySelector('[name="longitude"]').value;if(lat!==''&&lon!==''){selected={latitude:Number(lat),longitude:Number(lon)};center=project(selected.latitude,selected.longitude);zoom=6;}}
    new ResizeObserver(render).observe(map);render();
})();
