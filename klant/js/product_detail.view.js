// File: js/product_detail.view.js

// --- Constants ---
const MAX_FILE_SIZE = 1000 * 1024 * 1024; // 10 MB (adjust as needed)

// --- DOM Element References ---
// Declare variables, assign them in DOMContentLoaded
let fileInput = null;
let fileUploadArea = null;
let filePreviewArea = null;
let fileNameSpan = null;
let fileSizeSpan = null;
let fileErrorP = null;
let addToCartButton = null;
let quantitySpan = null;
let quantityInput = null;
let mainImage = null;
let thumbnailContainers = null;
let cartNotificationModal = null;
let productIdInput = null; // Reference for product ID
let productTitleElement = null;
let priceElement = null;

// --- State ---
let uploadedFile = null; // Holds the currently selected File object (lives only on this page load)
let isFileRequired = false; // Will be set based on the input attribute

// --- Global Temporary File Storage ---
// This object stores the actual File objects, keyed by the cartKey.
// It persists only as long as the window/tab is open (not across page reloads).
// winkelwagen.js will access this object during checkout.
window.tempCartFiles = window.tempCartFiles || {};

// --- Initialization ---
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements after they are loaded
    fileInput = document.getElementById('bestand');
    fileUploadArea = document.getElementById('fileUploadArea');
    filePreviewArea = document.getElementById('filePreviewArea');
    fileNameSpan = document.getElementById('fileName');
    fileSizeSpan = document.getElementById('fileSize');
    fileErrorP = document.getElementById('fileError');
    addToCartButton = document.getElementById('addToCartButton');
    quantitySpan = document.getElementById('quantityValue');
    quantityInput = document.getElementById('quantity');
    mainImage = document.getElementById('mainImage');
    thumbnailContainers = document.querySelectorAll('.thumbnails > div[id^="thumb-container-"]');
    cartNotificationModal = document.getElementById('cartNotificationModal'); // Assuming this exists for success message
    productIdInput = document.querySelector('input[name="product_id"]');
    productTitleElement = document.querySelector('h2.text-3xl.font-bold'); // Adjust selector if needed
    priceElement = document.querySelector('span.text-2xl.font-bold'); // Adjust selector if needed

    // Check if file input exists and is required
    if (fileInput) {
        isFileRequired = fileInput.hasAttribute('required');
        fileInput.addEventListener('change', handleFileSelect);
    }

    // Initialize accordion functionality
    initAccordion();

    // Initial validation check for Add to Cart button
    validateAddToCartButton();

    // Add listeners for quantity changes to re-validate cart button
    document.querySelectorAll('button[onclick="decreaseQuantity()"], button[onclick="increaseQuantity()"]')
        .forEach(btn => btn.addEventListener('click', validateAddToCartButton));

    // Add listeners for option changes to re-validate cart button (if options affect validation)
    document.querySelectorAll('input[type="checkbox"][name^="opties"]').forEach(checkbox => {
        checkbox.addEventListener('change', validateAddToCartButton);
    });

}); // End DOMContentLoaded

// --- File Handling ---

/**
 * Handles the file selection event from the input.
 * Validates the file and updates the UI.
 * @param {Event} event - The file input change event.
 */
const handleFileSelect = (event) => {
    clearFileError(); // Clear previous errors
    const file = event.target.files[0];

    if (!file) {
        // User cancelled selection, reset if a file was previously selected
        if (uploadedFile) {
            removeFile(); // Reset the UI fully
        }
        return;
    }

    // --- File Validation ---
    const allowedTypesString = fileInput?.accept || ''; // Get accept attribute value
    const allowedTypes = allowedTypesString.split(',').map(t => t.trim().toLowerCase()).filter(Boolean); // Filter out empty strings
    const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
    const fileType = file.type.toLowerCase(); // MIME type

    // 1. Validate Type
    let isValidType = false;
    if (allowedTypes.length > 0) {
        // Check against extension and MIME type in the accept attribute
        isValidType = allowedTypes.some(allowed =>
            (allowed.startsWith('.') && allowed === fileExtension) || // Check extension (e.g., .pdf)
            (!allowed.startsWith('.') && fileType.includes(allowed)) || // Check MIME type part (e.g., image/jpeg contains jpeg)
            (allowed === '.jpg' && fileExtension === '.jpeg') || // Handle jpg/jpeg equivalence
            (allowed === '.jpeg' && fileExtension === '.jpg')
        );
    } else {
        isValidType = true; // No restrictions defined in 'accept' attribute
    }


    if (!isValidType) {
        const displayAllowedTypes = allowedTypes.map(t => t.startsWith('.') ? t.substring(1).toUpperCase() : t.toUpperCase()).join(', ');
        showFileError(`Ongeldig bestandstype. Toegestaan: ${displayAllowedTypes || 'Alle types'}`);
        resetFileInput(); // Clear the invalid selection
        uploadedFile = null; // Ensure state reflects cleared input
        validateAddToCartButton(); // Re-validate button
        return;
    }

    // 2. Validate Size
    if (file.size > MAX_FILE_SIZE) {
        showFileError(`Bestand te groot. Maximaal toegestaan: ${formatFileSize(MAX_FILE_SIZE)}.`);
        resetFileInput(); // Clear the invalid selection
        uploadedFile = null; // Ensure state reflects cleared input
        validateAddToCartButton(); // Re-validate button
        return;
    }

    // --- Update State and UI ---
    uploadedFile = file; // Store the valid File object IN MEMORY for this page session

    if (fileNameSpan) fileNameSpan.textContent = file.name;
    if (fileSizeSpan) fileSizeSpan.textContent = formatFileSize(file.size);

    if (filePreviewArea) filePreviewArea.classList.remove('hidden');
    if (fileUploadArea) fileUploadArea.classList.add('hidden');

    validateAddToCartButton(); // Re-check if cart button can be enabled
};

/**
 * Removes the selected file from the state, clears the preview, and resets the input.
 */
const removeFile = () => {
    uploadedFile = null; // Clear the stored File object state
    resetFileInput();    // Clear the actual input field
    clearFileError();    // Clear any validation errors

    // Reset UI elements
    if (filePreviewArea) filePreviewArea.classList.add('hidden');
    if (fileUploadArea) fileUploadArea.classList.remove('hidden');
    if (fileNameSpan) fileNameSpan.textContent = '';
    if (fileSizeSpan) fileSizeSpan.textContent = '';

    validateAddToCartButton(); // Re-check cart button status
};

/**
 * Resets the file input value. Essential for allowing re-selection of the same file.
 */
const resetFileInput = () => {
    if (fileInput) {
        fileInput.value = ''; // Clear the selected file from the input element
    }
};

/**
 * Displays a file validation error message.
 * @param {string} message - The error message to display.
 */
const showFileError = (message) => {
    if (fileErrorP) {
        fileErrorP.textContent = message;
        fileErrorP.classList.remove('hidden');
    }
    // Optionally add visual feedback to the upload area
    if (fileUploadArea) {
        fileUploadArea.classList.add('border-red-500', 'dark:border-red-400');
        fileUploadArea.classList.remove('border-gray-300', 'dark:border-gray-700', 'hover:border-[#8fe507]');
    }
};

/**
 * Clears the file validation error message and styling.
 */
const clearFileError = () => {
    if (fileErrorP) {
        fileErrorP.textContent = '';
        fileErrorP.classList.add('hidden');
    }
    // Remove error visual feedback
    if (fileUploadArea) {
        fileUploadArea.classList.remove('border-red-500', 'dark:border-red-400');
        fileUploadArea.classList.add('border-gray-300', 'dark:border-gray-700', 'hover:border-[#8fe507]');
    }
};

/**
 * Formats file size from bytes to a readable string (KB, MB).
 * @param {number} bytes - File size in bytes.
 * @returns {string} - Formatted file size string.
 */
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.max(0, Math.floor(Math.log(bytes) / Math.log(k)));
    const precision = i < 2 ? 0 : (i < 3 ? 1 : 2);
    return parseFloat((bytes / Math.pow(k, i)).toFixed(precision)) + ' ' + sizes[i];
};


// --- Cart Logic ---

/**
 * Validates if the 'Add to Cart' button should be enabled.
 * Checks if a required file is uploaded (if applicable).
 */
const validateAddToCartButton = () => {
    if (!addToCartButton) return;

    // Condition 1: If file input exists AND is required, a file MUST be currently selected (`uploadedFile` state).
    const fileRequirementMet = !isFileRequired || (isFileRequired && uploadedFile !== null);

    // Condition 2: Add other conditions if necessary (e.g., mandatory options selected)
    // Example: Check if all mandatory option groups have at least one selection
    const mandatoryOptionsMet = true; // Replace with actual check if needed

    addToCartButton.disabled = !(fileRequirementMet && mandatoryOptionsMet);
};


/**
 * Adds the configured product to the cart (localStorage and temporary file storage).
 */
const addToCart = () => {
    // Re-validate before adding
    validateAddToCartButton();
    if (addToCartButton?.disabled) {
        if (isFileRequired && !uploadedFile) {
            alert('Upload een bestand om dit product toe te voegen.');
            if (fileInput) {
                fileInput.focus(); // Focus the input
                showFileError('Een bestand is vereist.'); // Show error near input
            }
        } else {
            alert('Controleer de productopties of hoeveelheid.'); // Generic message for other validations
        }
        return;
    }

    // --- Gather Product Data ---
    const productId = productIdInput ? productIdInput.value : null;
    if (!productId) {
        console.error("Product ID not found in the form.");
        alert("Er is een fout opgetreden. Product ID ontbreekt.");
        return;
    }
    const quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;

    // Gather selected options (store choice values/names)
    let selectedOptions = {};
    document.querySelectorAll('input[type="checkbox"][name^="opties"]:checked').forEach(checkbox => {
        const match = checkbox.name.match(/opties\[(\d+)\]\[\]/); // Get option ID
        if (match && match[1]) {
            const optieId = match[1];
            if (!selectedOptions[optieId]) {
                selectedOptions[optieId] = [];
            }
            selectedOptions[optieId].push(checkbox.value); // Store the choice value (name)
        }
    });

    // --- Prepare file info FOR LOCALSTORAGE (metadata only) ---
    let fileInfoForLocalStorage = null;
    if (uploadedFile) {
        fileInfoForLocalStorage = {
            name: uploadedFile.name,
            size: uploadedFile.size,
            type: uploadedFile.type
            // DO NOT store the File object itself here
        };
    }

    // --- Create Cart Item for LocalStorage ---
    const cartItem = {
        product_id: productId,
        quantity: quantity,
        options: selectedOptions, // Object: { optieId: ["choiceName1", "choiceName2"], ... }
        file: fileInfoForLocalStorage, // Metadata only, or null
        display_details: { // Details for easy display in cart
            name: productTitleElement ? productTitleElement.textContent.trim() : 'Product',
            price_text: priceElement ? priceElement.textContent.trim() : '€0,00', // Base price display text
            image_url: mainImage ? mainImage.src : null // Current main image shown
        }
    };

    // --- Add to LocalStorage Cart & Temporary File Storage ---
    let cart = getCartFromLocalStorage();
    const optionsString = serializeOptionsForCartKey(selectedOptions); // Generate consistent key part for options
    const cartKey = `${productId}_${optionsString}`; // Unique key for this product configuration

    // --- Store/Remove the actual File object in temporary global storage ---
    if (uploadedFile) {
        // Store the actual File object, keyed by cartKey
        window.tempCartFiles[cartKey] = uploadedFile;
        console.log(`File object stored temporarily for key: ${cartKey}`, uploadedFile);
    } else {
        // If no file is selected now, ensure any previously stored file for this key is removed
        delete window.tempCartFiles[cartKey];
        console.log(`No file selected, removed potential temp file for key: ${cartKey}`);
    }

    // --- Update LocalStorage Cart Data ---
    if (cart[cartKey]) {
        // Item with same product ID and options exists, update quantity
        cart[cartKey].quantity += quantity;
        // Update file metadata in localStorage if a new file was uploaded, or remove if no file now
        if (fileInfoForLocalStorage) {
            cart[cartKey].file = fileInfoForLocalStorage;
        } else {
            delete cart[cartKey].file; // Remove file metadata if no file selected
        }
    } else {
        // New item configuration
        cart[cartKey] = cartItem;
    }

    saveCartToLocalStorage(cart);
    showCartNotification(); // Show success feedback to user

    console.log("Item added to cart (localStorage):", cartItem);
    console.log("Current localStorage cart:", cart);
    console.log("Current temporary files (window.tempCartFiles):", window.tempCartFiles);

    // --- Reset Product Page State After Adding ---
    resetQuantity(); // Reset quantity selector to 1
    removeFile();    // Clear the file input and preview on this page
    // Optional: Maybe scroll to top or show a clearer success message
};

// --- Quantity Controls ---
const increaseQuantity = () => {
    if (quantitySpan && quantityInput) {
        let currentQuantity = parseInt(quantityInput.value, 10);
        currentQuantity++;
        quantitySpan.textContent = currentQuantity;
        quantityInput.value = currentQuantity;
        validateAddToCartButton(); // Re-check button status (though quantity usually doesn't affect it)
    }
};

const decreaseQuantity = () => {
    if (quantitySpan && quantityInput) {
        let currentQuantity = parseInt(quantityInput.value, 10);
        if (currentQuantity > 1) {
            currentQuantity--;
            quantitySpan.textContent = currentQuantity;
            quantityInput.value = currentQuantity;
            validateAddToCartButton(); // Re-check button status
        }
    }
};

const resetQuantity = () => {
    if (quantitySpan && quantityInput) {
        quantitySpan.textContent = '1';
        quantityInput.value = '1';
        validateAddToCartButton(); // Ensure button state is correct after reset
    }
};

// --- Image Gallery ---
const changeImage = (src, index) => {
    if (mainImage) {
        mainImage.src = src;
    }
    // Update active thumbnail styling
    if (thumbnailContainers) {
        thumbnailContainers.forEach((container, i) => {
            const img = container.querySelector('img');
            if (i === index) {
                container.classList.add('ring-2', 'ring-[#8fe507]');
                if (img) img.classList.add('opacity-100');
                if (img) img.classList.remove('opacity-70'); // Adjust opacity class names if different
                // Optional: Scroll the active thumbnail into view if the container is scrollable
                // container.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                container.classList.remove('ring-2', 'ring-[#8fe507]');
                if (img) img.classList.remove('opacity-100');
                if (img) img.classList.add('opacity-70'); // Adjust opacity class names if different
            }
        });
    }
};

// --- Accordion ---
const initAccordion = () => {
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        // Derive content ID and find elements
        const contentId = button.id.replace('-button', '-content');
        const content = document.getElementById(contentId);
        const icon = button.querySelector('.accordion-icon');

        if (!content) {
            console.warn(`Accordion content not found for button #${button.id}`);
            return;
        }

        // Set initial state (closed) using max-height for transition
        content.style.maxHeight = '0';
        content.style.overflow = 'hidden'; // Ensure content is clipped
        content.classList.remove('open'); // Use class for semantic state if needed
        icon?.classList.remove('rotate-180');

        button.addEventListener('click', () => {
            const isOpen = content.style.maxHeight !== '0px';

            if (isOpen) {
                // Close this one
                content.style.maxHeight = '0';
                content.classList.remove('open');
                icon?.classList.remove('rotate-180');
            } else {
                // Open this one: set max-height to its scroll height for smooth transition
                content.style.maxHeight = content.scrollHeight + 'px';
                content.classList.add('open');
                icon?.classList.add('rotate-180');
            }
        });

        // Optional: Recalculate max-height on window resize if accordion is open
        // This handles cases where content size changes due to viewport changes
        window.addEventListener('resize', () => {
            if (content.style.maxHeight !== '0px') { // If open
                // Temporarily remove max-height to measure natural height, then re-apply
                content.style.transition = 'none'; // Disable transition during resize calculation
                content.style.maxHeight = 'none';
                const scrollHeight = content.scrollHeight;
                content.style.maxHeight = '0'; // Force reflow
                requestAnimationFrame(() => { // Wait for next frame
                    content.style.transition = ''; // Re-enable transition
                    content.style.maxHeight = scrollHeight + 'px';
                });
            }
        });
    });
};

// --- LocalStorage Cart Helpers ---
const getCartFromLocalStorage = () => {
    try {
        const cartString = localStorage.getItem('winkelwagen');
        return cartString ? JSON.parse(cartString) : {};
    } catch (e) {
        console.error("Error reading cart from localStorage:", e);
        return {}; // Return empty object on error
    }
};

const saveCartToLocalStorage = (cart) => {
    try {
        localStorage.setItem('winkelwagen', JSON.stringify(cart));
    } catch (e) {
        console.error("Error saving cart to localStorage:", e);
        // Optionally inform the user if storage quota is exceeded
        // alert("Kon winkelwagen niet opslaan. Mogelijk is de opslagruimte vol.");
    }
};

/**
 * Creates a consistent string representation of selected options for use as a key part.
 * Ensures consistent order by sorting option IDs and choice values.
 * @param {object} options - The selected options object { optieId: ["choice1", "choice2"], ... }.
 * @returns {string} - A serialized string of options (e.g., "1:choiceA,choiceB;3:choiceC").
 */
const serializeOptionsForCartKey = (options) => {
    if (!options || Object.keys(options).length === 0) {
        return 'no-options'; // Use a specific string for no options
    }
    // Get option IDs (keys), sort them numerically or alphabetically
    const sortedOptionIds = Object.keys(options).sort();

    return sortedOptionIds.map(optieId => {
        // Get choices for this option, ensure it's an array, sort values
        const values = Array.isArray(options[optieId]) ? [...options[optieId]].sort() : [options[optieId]];
        return `${optieId}:${values.join(',')}`; // Join sorted values with comma
    }).join(';'); // Join different options with semicolon
};

// --- UI Feedback ---
const showCartNotification = () => {
    // Use the existing modal or create a simple notification mechanism
    if (cartNotificationModal) {
        // Simple example: Make it visible and fade out
        const textElement = cartNotificationModal.querySelector('span'); // Adjust selector if needed
        if (textElement) textElement.textContent = 'Product toegevoegd aan winkelwagen!';

        cartNotificationModal.classList.remove('hidden', 'opacity-0', 'translate-y-full'); // Make visible
        cartNotificationModal.classList.add('opacity-100', 'translate-y-0');

        setTimeout(() => {
            cartNotificationModal.classList.remove('opacity-100', 'translate-y-0');
            cartNotificationModal.classList.add('opacity-0', 'translate-y-full');
            // Optional: Add 'hidden' back after transition ends
            // setTimeout(() => cartNotificationModal.classList.add('hidden'), 500); // Match transition duration
        }, 3000); // Hide after 3 seconds
    } else {
        console.log("Cart notification modal not found. Item added silently.");
    }
};


// --- Make functions globally accessible if using inline onclick ---
// It's generally better to attach listeners purely in JS (as done in DOMContentLoaded),
// but if you still have inline `onclick="..."`, these need to be global.
window.handleFileSelect = handleFileSelect;
window.removeFile = removeFile;
window.addToCart = addToCart;
window.increaseQuantity = increaseQuantity;
window.decreaseQuantity = decreaseQuantity;
window.changeImage = changeImage;
// window.updateOptions = updateOptions; // If needed