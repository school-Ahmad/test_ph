// File: js/winkelwagen.js

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Element References ---
    const winkelwagenContainer = document.getElementById('winkelwagen-container');
    const cartErrorModal = document.getElementById('cartErrorModal');
    const cartErrorModalText = document.getElementById('cartErrorModalText');
    const cartSuccessModal = document.getElementById('cartSuccessModal');
    const cartSuccessModalText = document.getElementById('cartSuccessModalText');
    const removeConfirmationModal = document.getElementById('removeConfirmationModal');
    const confirmRemoveButton = removeConfirmationModal?.querySelector('.remove-confirm-button');
    const cancelRemoveButton = removeConfirmationModal?.querySelector('.remove-cancel-button');

    // --- State ---
    let itemKeyToRemove = null;
    let isSubmitting = false;

    // --- LocalStorage Helpers ---
    const getCartFromLocalStorage = () => {
        const cartString = localStorage.getItem('winkelwagen');
        if (!cartString) { return {}; }
        try {
            const cartData = JSON.parse(cartString);
            return (typeof cartData === 'object' && cartData !== null) ? cartData : {};
        } catch (e) {
            console.error("Fout bij parsen winkelwagen JSON:", e);
            // Optioneel: Wis corrupte data
            // localStorage.removeItem('winkelwagen');
            return {}; // Geef leeg object terug bij parsefout
        }
    };

    const saveCartToLocalStorage = (cart) => {
        try {
            if (typeof cart !== 'object' || cart === null) { throw new Error("Ongeldige data."); }
            localStorage.setItem('winkelwagen', JSON.stringify(cart));
        } catch (e) {
            console.error("Fout bij opslaan winkelwagen:", e);
            showErrorModal("Kon winkelwagen niet opslaan.");
        }
    };

    // --- Rendering ---
    const renderCart = () => {
        console.log("renderCart gestart.");
        if (!winkelwagenContainer) { console.error("Container #winkelwagen-container niet gevonden."); return; }

        try {
            const cartItems = getCartFromLocalStorage();
            console.log("Winkelwagen data:", cartItems);

            let cartHTML = '';
            let totalPrice = 0;
            const hasItems = cartItems && typeof cartItems === 'object' && Object.keys(cartItems).length > 0;

            if (!hasItems) {
                // Lege winkelwagen weergave
                cartHTML = `<div class="text-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md"><i class="fas fa-shopping-cart fa-3x text-gray-400 dark:text-gray-500 mb-4"></i><h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Je winkelwagen is leeg</h2><p class="text-gray-500 dark:text-gray-400 mt-2">Voeg producten toe om te bestellen.</p><a href="product.view.php" class="inline-block mt-6 px-5 py-2 bg-[#8fe507] text-white font-semibold rounded-lg hover:bg-[#7bc906] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8fe507] dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">Verder winkelen</a></div>`;
                winkelwagenContainer.innerHTML = cartHTML;
            } else {
                // Winkelwagen met items layout
                console.log("Bouw layout...");
                cartHTML = `
                    <form id="checkout-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8" enctype="multipart/form-data">
                        <div class="lg:col-span-2 space-y-4" id="cart-item-list"><div class="text-center p-4 text-gray-500 italic">Items laden...</div></div>
                        <div class="lg:col-span-1">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 sticky top-20">
                                <!-- Overzicht & Gegevens -->
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4 border-b pb-3 dark:border-gray-700">Overzicht</h2>
                                <div class="flex justify-between items-center mb-4"><span class="text-gray-700 dark:text-gray-300">Subtotaal:</span><span id="total-price" class="font-semibold text-lg text-gray-800 dark:text-white">€0,00</span></div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mt-6 mb-3 pt-4 border-t dark:border-gray-700">Gegevens</h3>
                                <div class="mb-4"><label for="klant_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-mailadres <span class="text-red-500">*</span></label><input type="email" name="klant_email" id="klant_email" placeholder="naam@glr.nl" class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm" required><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Moet eindigen op @glr.nl</p></div>
                                <button type="submit" id="checkout-button" class="w-full mt-6 flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-[#8fe507] hover:bg-[#7bc906] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8fe507] dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition"><span class="button-text">Afrekenen</span><span class="spinner hidden ml-2"></span></button>
                                <div id="checkout-error" class="text-red-500 text-sm mt-2 text-center hidden"></div>
                            </div>
                        </div>
                    </form>
                `;
                winkelwagenContainer.innerHTML = cartHTML;

                // --- Genereer HTML per item ---
                let cartItemListHTML = '';
                try {
                    for (const itemKey in cartItems) {
                        const item = cartItems[itemKey];
                        if (!item || typeof item !== 'object') { continue; }

                        // Prijs & Opties (parsing & HTML generatie zoals voorheen)
                        const priceText = item.display_details?.price_text?.replace('€', '').replace(',', '.') || '0';
                        const validItemPrice = isNaN(parseFloat(priceText)) ? 0 : parseFloat(priceText);
                        totalPrice += validItemPrice * (item.quantity || 1);
                        let optiesHTML = '';
                        if (item.options && typeof item.options === 'object' && Object.keys(item.options).length > 0) {
                            optiesHTML += '<ul class="mt-1 space-y-0.5">';
                            Object.values(item.options).flat().forEach(choice => { // Flat array van keuzes
                                if (typeof choice === 'string') {
                                    optiesHTML += `<li class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-check fa-xs text-green-500 mr-1.5"></i>${escapeHtml(choice)}</li>`;
                                }
                            });
                            optiesHTML += '</ul>';
                        }


                        // BESTAND INPUT genereren
                        let fileHTML = '';
                        const needsFile = item.file && typeof item.file.name === 'string'; // Check of er metadata is

                        if (needsFile) {
                            // Probeer 'accept' types te halen uit metadata, anders fallback
                            const acceptTypes = item.file.type ? MimeToAccept(item.file.type) : '.pdf,.jpg,.jpeg,.png,.ai,.eps,.svg,.zip,.rar';

                            fileHTML = `
                                <div class="mt-3 border-t pt-2 dark:border-gray-700">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-paperclip fa-xs mr-1"></i> Oorspr.: ${escapeHtml(item.file.name)}</p>
                                    <label for="file-input-${itemKey}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload bestand opnieuw <span class="text-red-500">*</span></label>
                                    <input type="file" name="cart_files[${itemKey}]" id="file-input-${itemKey}"
                                           class="block w-full text-sm text-gray-500 dark:text-gray-400 border rounded-md cursor-pointer file:mr-4 file:py-1 file:px-3 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-gray-600 file:text-indigo-700 dark:file:text-indigo-200 hover:file:bg-indigo-100 dark:hover:file:bg-gray-500 dark:bg-gray-700 dark:border-gray-600"
                                           accept="${acceptTypes}" required>
                                    <p class="mt-1 text-xs text-red-500 hidden" id="file-error-${itemKey}"></p>
                                </div>`;
                        }

                        // Item Card HTML
                        const detailPageLink = `/test_ph/klant/views/product_detail.view.php?id=${item.product_id || ''}`;
                        cartItemListHTML += `
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 flex flex-col sm:flex-row gap-4">
                                <!-- Afbeelding -->
                                <div class="w-24 h-24 sm:w-20 sm:h-20 flex-shrink-0 overflow-hidden rounded-md border dark:border-gray-700"><a href="${detailPageLink}"><img src="${item.display_details?.image_url || '/test_ph/uploads/default_image.jpg'}" alt="${escapeHtml(item.display_details?.name)}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='/test_ph/uploads/default_image.jpg';"></a></div>
                                <!-- Details -->
                                <div class="flex-grow">
                                    <div class="flex justify-between items-start"><a href="${detailPageLink}" class="hover:underline"><h3 class="text-md font-semibold text-gray-800 dark:text-white">${escapeHtml(item.display_details?.name)}</h3></a><button class="remove-item-button text-gray-400 hover:text-red-500 dark:hover:text-red-400 ml-2" data-item-key="${itemKey}" aria-label="Verwijder"><i class="fas fa-times"></i></button></div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">Prijs: €${validItemPrice.toFixed(2).replace('.', ',')}</p>
                                    <!-- Hoeveelheid -->
                                    <div class="mt-2 flex items-center space-x-2"><label for="quantity-${itemKey}" class="text-xs font-medium text-gray-700 dark:text-gray-300">Aantal:</label><div class="quantity-control flex items-center border border-gray-300 dark:border-gray-600 rounded"><button type="button" class="quantity-button px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-l" data-action="decrease" data-item-key="${itemKey}">-</button><input type="number" id="quantity-${itemKey}" class="quantity-input w-10 text-center text-sm border-0 bg-transparent focus:ring-0 dark:text-white" data-item-key="${itemKey}" value="${item.quantity || 1}" min="1"><button type="button" class="quantity-button px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-r" data-action="increase" data-item-key="${itemKey}">+</button></div></div>
                                    ${optiesHTML}
                                    ${fileHTML}
                                </div>
                            </div>`;
                    } // Einde for
                } catch (loopError) { console.error("Fout bij renderen item list:", loopError); cartItemListHTML = `<div class="text-red-500 p-4">Fout bij laden items.</div>`; }

                // Plaats gegenereerde item HTML en update totaalprijs
                const cartItemListContainer = winkelwagenContainer.querySelector('#cart-item-list');
                if (cartItemListContainer) { cartItemListContainer.innerHTML = cartItemListHTML; }
                const totalPriceElement = winkelwagenContainer.querySelector('#total-price');
                if (totalPriceElement) { totalPriceElement.textContent = '€' + totalPrice.toFixed(2).replace('.', ','); }

                // Koppel Event Listeners
                attachEventListeners();
            } // Einde else (hasItems)

        } catch (error) {
            console.error("Algemene fout in renderCart:", error);
            winkelwagenContainer.innerHTML = `<div class="text-red-500 p-4">Kon winkelwagen niet laden: ${escapeHtml(error.message)}</div>`;
        }
        console.log("renderCart voltooid.");
    };

    // --- Event Handling ---
    const attachEventListeners = () => {
        const container = document.getElementById('winkelwagen-container');
        if (!container) return;
        container.querySelectorAll('.quantity-input').forEach(i => { i.removeEventListener('change', handleQuantityChange); i.removeEventListener('input', handleQuantityChange); i.addEventListener('change', handleQuantityChange); i.addEventListener('input', handleQuantityChange); });
        container.querySelectorAll('.quantity-button').forEach(b => { b.removeEventListener('click', handleQuantityButtonClick); b.addEventListener('click', handleQuantityButtonClick); });
        container.querySelectorAll('.remove-item-button').forEach(b => { b.removeEventListener('click', handleRemoveItemClick); b.addEventListener('click', handleRemoveItemClick); });
        const checkoutForm = container.querySelector('#checkout-form');
        if (checkoutForm) { checkoutForm.removeEventListener('submit', handleCheckoutSubmit); checkoutForm.addEventListener('submit', handleCheckoutSubmit); }
    };

    const handleQuantityChange = (e) => { /* ... */ }; // Zoals voorheen
    const handleQuantityButtonClick = (e) => { /* ... */ }; // Zoals voorheen
    const handleRemoveItemClick = (e) => { /* ... */ }; // Zoals voorheen

    // --- CHECKOUT SUBMIT MET VERBETERDE FETCH HANDLING ---
    const handleCheckoutSubmit = async (e) => {
        e.preventDefault();
        console.log("Checkout submit.");
        if (isSubmitting) return;

        const checkoutForm = document.getElementById('checkout-form');
        const checkoutButton = document.getElementById('checkout-button');
        const buttonText = checkoutButton?.querySelector('.button-text');
        const spinner = checkoutButton?.querySelector('.spinner');

        // Validatie (E-mail & Vereiste Bestanden)
        const emailInput = document.getElementById('klant_email');
        const email = emailInput?.value.trim();
        if(cartErrorModal) cartErrorModal.classList.add('hidden'); // Verberg oude modaal fouten

        if (!email || !isValidGlrEmail(email)) { showErrorModal('Ongeldig e-mailadres.'); emailInput?.focus(); return; }

        let allRequiredFilesSelected = true;
        const fileInputs = checkoutForm.querySelectorAll('input[type="file"][required]');
        fileInputs.forEach(input => {
            const errorMsgElement = document.getElementById(`file-error-${input.id.replace('file-input-','')}`);
            if(errorMsgElement) errorMsgElement.classList.add('hidden');
            input.classList.remove('border-red-500'); // Reset stijl
            if (input.files.length === 0) {
                allRequiredFilesSelected = false;
                input.classList.add('border-red-500');
                if(errorMsgElement) { errorMsgElement.textContent = 'Bestand vereist.'; errorMsgElement.classList.remove('hidden');}
            }
        });
        if (!allRequiredFilesSelected) { showErrorModal('Selecteer alle vereiste bestanden.'); return; }

        // --- Voorbereiden & Versturen ---
        console.log("Validatie OK, versturen...");
        isSubmitting = true;
        if(buttonText) buttonText.textContent = 'Verwerken...';
        if(spinner) spinner.classList.remove('hidden');
        if(checkoutButton) checkoutButton.disabled = true;

        const formData = new FormData(checkoutForm); // Haal alle data inclusief files
        const cartItems = getCartFromLocalStorage(); // Haal metadata voor referentie
        const cartMetadata = {};
        Object.keys(cartItems).forEach(key => {
            cartMetadata[key] = {
                product_id: cartItems[key].product_id,
                quantity: cartItems[key].quantity,
                options: cartItems[key].options,
                original_filename: cartItems[key].file?.name
            };
        });
        formData.append('cart_metadata', JSON.stringify(cartMetadata));

        try {
            const response = await fetch('/test_ph/klant/logic/winkelwagen.logic.php', { // Controleer dit pad!
                method: 'POST',
                body: formData // Verstuurt automatisch als multipart/form-data
            });

            console.log("Response status:", response.status, response.statusText);

            // --- RESPONSE HANDLING VOLGENS SUGGESTIE ---
            if (!response.ok) { // Check voor HTTP errors (4xx, 5xx)
                let errorJson = null;
                let errorText = '';
                try {
                    // Probeer eerst als JSON te lezen (voorkeur voor API errors)
                    errorJson = await response.json();
                    console.error("Server error response (JSON):", errorJson);
                } catch (e) {
                    // Als JSON faalt, lees als tekst (kan HTML error zijn)
                    try {
                        errorText = await response.text();
                        console.error("Server error response (Text):", errorText);
                    } catch (e2) {
                        console.error("Kon error response body niet lezen.");
                    }
                }
                // Gebruik message uit JSON indien beschikbaar, anders default
                const errorMessage = errorJson?.message || `Serverfout (${response.status}).` + (errorText ? ` Details: ${errorText.substring(0,100)}...` : '');
                throw new Error(errorMessage); // Gooi error om naar catch block te gaan
            }

            // Poging tot parsen van succesvolle response (2xx status)
            // Hier kan de "Unexpected end of JSON input" error optreden
            // als de server een 2xx stuurt met een lege body.
            const responseBody = await response.text(); // Lees eerst als tekst
            if (!responseBody) {
                // Server gaf 2xx maar lege body - Behandel dit als succes maar log het
                console.warn("Server gaf succesvolle status (2xx) maar lege response body.");
                // Simuleer een succesvolle data structuur als de redirect werkt zonder data
                const data = { status: 'success', message: 'Bestelling lijkt succesvol verwerkt (lege response).', bestelling_ids: [] }; // Maak aanname
                // Ga door met succes-logica
                console.log("Checkout succesvol (simulated)!");
                if(cartSuccessModalText) cartSuccessModalText.textContent = data.message;
                if(cartSuccessModal) cartSuccessModal.classList.remove('hidden');
                localStorage.removeItem('winkelwagen');
                setTimeout(() => { window.location.href = `/test_ph/klant/views/index.php`; }, 2000); // Redirect naar home bij gebrek aan IDs
                // STOP verdere uitvoering van de try block hier
                return;

            }

            // Probeer nu de (niet-lege) tekst body te parsen als JSON
            let data;
            try {
                data = JSON.parse(responseBody);
                console.log("Backend JSON response (success):", data);
            } catch (e) {
                console.error("Kon succesvolle response body niet parsen als JSON:", e);
                console.error("Ontvangen tekst body:", responseBody);
                throw new Error("Ongeldige JSON ontvangen van server (ondanks succes status).");
            }


            // Verwerk correct geparsede succes data
            if (data.status === 'success') {
                console.log("Checkout succesvol (JSON data)!");
                if(cartSuccessModalText) cartSuccessModalText.textContent = data.message || 'Bestelling succesvol!';
                if(cartSuccessModal) cartSuccessModal.classList.remove('hidden');
                localStorage.removeItem('winkelwagen');
                setTimeout(() => {
                    const redirectUrl = data.bestelling_ids?.length > 0
                        ? `/test_ph/klant/views/bestelling_bevestiging.view.php?ids=${data.bestelling_ids.join(',')}`
                        : `/test_ph/klant/views/index.php`; // Fallback
                    window.location.href = redirectUrl;
                }, 2000);
            } else {
                // Server gaf 2xx, maar JSON bevatte { status: 'error' }
                console.error("Backend JSON bevatte foutstatus:", data.message);
                throw new Error(data.message || 'Onbekende serverfout in response.');
            }
            // --- EINDE RESPONSE HANDLING ---

        } catch (error) { // Vangt netwerkfouten, JSON parse errors, en gegooidde !response.ok errors
            console.error('Checkout fetch/verwerkingsfout:', error);
            showErrorModal(`Fout: ${error.message}`);
            // Reset knop state
            if(buttonText) buttonText.textContent = 'Afrekenen';
            if(spinner) spinner.classList.add('hidden');
            if(checkoutButton) checkoutButton.disabled = false;
            isSubmitting = false;
        }
    };

    // --- Cart Item Management ---
    const updateCartItemQuantity = (itemKey, quantity) => {
        let cartItems = getCartFromLocalStorage();
        if(cartItems[itemKey]) {
            cartItems[itemKey].quantity = Math.max(1, quantity); // Minimaal 1
            saveCartToLocalStorage(cartItems);
            renderCart(); // Optimalisatie: update alleen prijs & input ipv full render?
        }
    };
    const confirmRemoveItem = () => {
        if (!itemKeyToRemove) return;
        let cartItems = getCartFromLocalStorage();
        if (cartItems[itemKeyToRemove]) {
            delete cartItems[itemKeyToRemove];
            saveCartToLocalStorage(cartItems);
            renderCart();
        }
        hideRemoveConfirmationModal();
    };

    // --- Modal Controls & Validation & Utility ---
    const hideRemoveConfirmationModal = () => { itemKeyToRemove = null; if(removeConfirmationModal) removeConfirmationModal.classList.add('hidden'); };
    const showErrorModal = (message) => { if(cartErrorModal && cartErrorModalText){ cartErrorModalText.textContent = message; cartErrorModal.classList.remove('hidden'); setTimeout(()=>cartErrorModal.classList.add('hidden'), 7000);} else { alert(message);}};
    const isValidGlrEmail = (email) => (email && email.toLowerCase().endsWith('@glr.nl'));
    const escapeHtml = (unsafe) => { /* ... zoals je verbeterde versie ... */ return unsafe }; // Plaats hier je verbeterde escapeHtml functie
    const MimeToAccept = (mimeType) => {
        // Simpele mapping, kan uitgebreid worden
        if (!mimeType) return '*/*';
        if (mimeType.includes('pdf')) return '.pdf,application/pdf';
        if (mimeType.includes('jpeg') || mimeType.includes('jpg')) return '.jpg,.jpeg,image/jpeg';
        if (mimeType.includes('png')) return '.png,image/png';
        if (mimeType.includes('svg')) return '.svg,image/svg+xml';
        if (mimeType.includes('eps') || mimeType.includes('postscript')) return '.eps,application/postscript';
        if (mimeType.includes('illustrator') || mimeType.includes('ai')) return '.ai,application/postscript,application/pdf'; // AI is complex
        if (mimeType.includes('zip')) return '.zip,application/zip';
        // Voeg meer types toe indien nodig
        return mimeType + ',.' + (mimeType.split('/')[1] || '*'); // Algemene fallback
    }

    // --- Initial Setup ---
    console.log("DOM geladen, winkelwagen initialiseren...");
    // Koppel modal listeners (zoals voorheen)
    if (confirmRemoveButton) { confirmRemoveButton.addEventListener('click', confirmRemoveItem); }
    if (cancelRemoveButton) { cancelRemoveButton.addEventListener('click', hideRemoveConfirmationModal); }
    [cartErrorModal, cartSuccessModal, removeConfirmationModal].forEach(modal => { /* ... */ });

    renderCart(); // Render initieel
});

// Zorg dat je deze escape functie opneemt
const escapeHtml = (unsafe) => {
    if (typeof unsafe !== 'string') {
        if (unsafe === null || unsafe === undefined || typeof unsafe === 'object') { return ''; }
        try { unsafe = String(unsafe); } catch (error) { return ''; }
    }
    return unsafe.replace(/&/g, "&").replace(/</g, "<").replace(/>/g, ">").replace(/"/g, "").replace(/'/g, "'");
};