(() => {
    const form = document.querySelector('[data-publish-review]');
    if (!form) return;
    const dialog = form.querySelector('[data-review-dialog]');
    const confirm = form.querySelector('[data-confirm-publish]');
    const status = form.querySelector('[data-review-status]');
    const pinDialog = form.querySelector('[data-pin-dialog]');
    let reviewed = false;
    let imageUrls = [];
    form.addEventListener('input', () => { reviewed=false;confirm.disabled=true;form.querySelector('[name="country_code"]').setCustomValidity(''); });
    async function review() {
        form.querySelector('.story-editor').dispatchEvent(new Event('story:sync'));
        const countryInput=form.querySelector('[name="country_code"]'), placeInput=form.querySelector('[name="location_id"]');
        countryInput.setCustomValidity(!countryInput.value && !placeInput?.value ? 'Choose a country or an existing place.' : '');
        if (!form.reportValidity()) return;
        const body=form.querySelector('[name="body"]').value;
        if(body.length<20||body.length>30000){form.querySelector('[data-editor-status]').textContent='Write between 20 and 30,000 characters before previewing.';form.querySelector('[data-rich-editor]').focus();return;}
        reviewed=false;confirm.disabled=true;status.textContent='Preparing your story…';
        form.querySelector('[data-review-body]').replaceChildren();
        form.querySelector('[data-review-title]').textContent=form.querySelector('[name="title"]').value;
        const category=form.querySelector('[name="category"]');
        const place=form.querySelector('[name="location_id"]');
        const country=form.querySelector('[name="country_code"]');
        const region=form.querySelector('[name="region_code"]');
        const location=place?.value ? place.selectedOptions[0].textContent : [region.value ? region.selectedOptions[0].textContent : '',country.value ? country.selectedOptions[0].textContent : ''].filter(Boolean).join(', ');
        form.querySelector('[data-review-meta]').textContent=category.selectedOptions[0].textContent+' / '+location;
        if(!dialog.open)dialog.showModal();
        imageUrls.forEach(url=>URL.revokeObjectURL(url));imageUrls=[];
        const gallery=form.querySelector('[data-review-gallery]');gallery.replaceChildren();
        form.querySelectorAll('[data-existing-photo]').forEach(image=>{
            const remove=form.querySelector('[name="remove_gallery[]"][value="'+image.dataset.existingPhoto+'"]');
            if(!remove?.checked){const copy=document.createElement('img');copy.src=image.src;copy.alt=image.alt;gallery.append(copy);}
        });
        for(const file of form.querySelector('[name="gallery[]"]').files){
            if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>4*1024*1024){status.textContent='Choose JPG, PNG or WebP photos up to 4 MB each. Go back to change the attachment.';return;}
            const image=document.createElement('img'),url=URL.createObjectURL(file);imageUrls.push(url);image.src=url;image.alt=file.name;gallery.append(image);
        }
        if(gallery.children.length>6){status.textContent='Keep up to six photos. Go back to remove an attachment.';return;}
        try{
            const response=await fetch(form.querySelector('.story-editor').dataset.previewUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({body})});
            if(!response.ok)throw new Error('Preview unavailable');
            const data=await response.json();
            if(!dialog.open)return;
            form.querySelector('[data-review-body]').innerHTML=data.html;
            status.textContent='Ready to review. Publish only when you are happy with it.';reviewed=true;confirm.disabled=false;
        }catch{status.textContent='Preview could not load. Your writing is still here. Return to editing and try again.';}
    }
    form.querySelector('[data-review-story]').addEventListener('click',review);
    form.querySelector('[data-close-review]').addEventListener('click',()=>dialog.close());
    form.addEventListener('submit',event=>{if(!reviewed||!dialog.open){event.preventDefault();review();}});
    form.querySelector('[data-open-pin]').addEventListener('click',()=>pinDialog.showModal());
    form.querySelector('[data-close-pin]').addEventListener('click',()=>pinDialog.close());
    form.querySelector('[data-clear-pin]').addEventListener('click',()=>{
        form.querySelector('[name="latitude"]').value='';form.querySelector('[name="longitude"]').value='';form.querySelector('[data-pin-label]').textContent='No story pin selected.';form.querySelector('[data-mystery-map]').dispatchEvent(new Event('map:clear'));reviewed=false;
    });
})();
