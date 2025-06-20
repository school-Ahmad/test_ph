document.addEventListener('DOMContentLoaded', function() {
// Edit Modal
const editModal = document.getElementById('editModal');
const closeEditModal = document.getElementById('closeEditModal');
const cancelEditButton = document.getElementById('cancelEdit');
const addEditOptieButton = document.getElementById('addEditOptie');
const editProductOptiesContainer = document.getElementById('editProductOpties');
const currentMediaPreview = document.getElementById('currentMediaPreview');

if (closeEditModal) {
closeEditModal.addEventListener('click', function() {
editModal.classList.add('hidden');
});
}
if (cancelEditButton) {
cancelEditButton.addEventListener('click', function() {
editModal.classList.add('hidden');
});
}

if (addEditOptieButton) {
addEditOptieButton.addEventListener('click', function(e) {
e.preventDefault(); // Prevent form submission
const optieGroupDiv = document.createElement('div');
optieGroupDiv.classList.add('flex', 'items-center', 'space-x-2', 'mb-2'); // Added mb-2 for spacing

const optieNaamLabel = document.createElement('label');
optieNaamLabel.classList.add('block', 'text-black', 'font-medium', 'mr-2'); // Added mr-2 for spacing
optieNaamLabel.textContent = 'Optie Naam:';

const optieNaamInput = document.createElement('input');
optieNaamInput.type = 'text';
optieNaamInput.name = 'product_opties[naam][]'; // Array notation for option name
optieNaamInput.classList.add('border', 'rounded', 'w-1/3', 'p-2', 'focus:outline-none', 'focus:ring-2', 'focus:ring-purple-500');
optieNaamInput.placeholder = 'Naam (e.g., Kleur)';
optieNaamInput.required = true;

const optieWaardenLabel = document.createElement('label');
optieWaardenLabel.classList.add('block', 'text-black', 'font-medium', 'mr-2'); // Added mr-2 for spacing
optieWaardenLabel.textContent = 'Optie Waarden:';

const optieWaardenInput = document.createElement('input');
optieWaardenInput.type = 'text';
optieWaardenInput.name = 'product_opties[waarden][]'; // Array notation for option values
optieWaardenInput.classList.add('border', 'rounded', 'w-1/2', 'p-2', 'focus:outline-none', 'focus:ring-2', 'focus:ring-purple-500');
optieWaardenInput.placeholder = 'Waarden (comma-separated, e.g., Rood,Blauw,Groen)';
optieWaardenInput.required = true;

const removeButton = document.createElement('button');
removeButton.type = 'button';
removeButton.innerHTML = '×'; // X symbol
removeButton.classList.add('text-red-500', 'hover:text-red-700', 'font-bold', 'text-xl');
removeButton.addEventListener('click', function() {
optieGroupDiv.remove();
});

optieGroupDiv.appendChild(optieNaamLabel);
optieGroupDiv.appendChild(optieNaamInput);
optieGroupDiv.appendChild(optieWaardenLabel);
optieGroupDiv.appendChild(optieWaardenInput);
optieGroupDiv.appendChild(removeButton);
editProductOptiesContainer.appendChild(optieGroupDiv);
});
}

// Function to open the edit modal
const editButtons = document.querySelectorAll('.edit-btn');
editButtons.forEach(button => {
button.addEventListener('click', function() {
const productId = this.dataset.id;
const categorieId = this.dataset.categorie;
const naam = this.dataset.naam;
const beschrijving = this.dataset.beschrijving;
const prijs = this.dataset.prijs;
const opties = JSON.parse(this.dataset.opties); // Parse product options JSON
let media = this.dataset.media;
console.log("Raw media data from dataset:", media); // Debugging log

try {
media = JSON.parse(media || '[]'); // Parse media array, default to empty array if null
} catch (e) {
console.error("Error parsing media JSON:", e);
media = []; // Ensure media is an empty array in case of parsing error
}
console.log("Parsed media data:", media); // Debugging log


document.getElementById('editProductId').value = productId;
document.getElementById('editCategorie').value = categorieId;
document.getElementById('editNaam').value = naam;
document.getElementById('editBeschrijving').value = beschrijving;
document.getElementById('editPrijs').value = prijs;

// Clear existing options
editProductOptiesContainer.innerHTML = '';

// Populate existing product options
if (opties && opties.length > 0) {
opties.forEach(optie => {
const optieGroupDiv = document.createElement('div');
optieGroupDiv.classList.add('flex', 'items-center', 'space-x-2', 'mb-2');

const optieNaamLabel = document.createElement('label');
optieNaamLabel.classList.add('block', 'text-black', 'font-medium', 'mr-2');
optieNaamLabel.textContent = 'Optie Naam:';

const optieNaamInput = document.createElement('input');
optieNaamInput.type = 'text';
optieNaamInput.name = 'product_opties[naam][]';
optieNaamInput.classList.add('border', 'rounded', 'w-1/3', 'p-2', 'focus:outline-none', 'focus:ring-2', 'focus:ring-purple-500');
optieNaamInput.value = optie_naam; // Set existing option name
optieNaamInput.required = true;

const optieWaardenLabel = document.createElement('label');
optieWaardenLabel.classList.add('block', 'text-black', 'font-medium', 'mr-2');
optieWaardenLabel.textContent = 'Optie Waarden:';

const optieWaardenInput = document.createElement('input');
optieWaardenInput.type = 'text';
optieWaardenInput.name = 'product_opties[waarden][]';
optieWaardenInput.classList.add('border', 'rounded', 'w-1/2', 'p-2', 'focus:outline-none', 'focus:ring-2', 'focus:ring-purple-500');
optieWaardenInput.value = optie.keuzes.join(','); // Set existing option values (comma-separated)
optieWaardenInput.required = true;

const removeButton = document.createElement('button');
removeButton.type = 'button';
removeButton.innerHTML = '×';
removeButton.classList.add('text-red-500', 'hover:text-red-700', 'font-bold', 'text-xl');
removeButton.addEventListener('click', function() {
optieGroupDiv.remove();
});

optieGroupDiv.appendChild(optieNaamLabel);
optieGroupDiv.appendChild(optieNaamInput);
optieGroupDiv.appendChild(optieWaardenLabel);
optieGroupDiv.appendChild(optieWaardenInput);
optieGroupDiv.appendChild(removeButton);
editProductOptiesContainer.appendChild(optieGroupDiv);
});
}

// Clear existing media preview
currentMediaPreview.innerHTML = '';

// Populate current media preview and add delete buttons
if (media && media.length > 0) {
media.forEach(mediaItem => {
const mediaDiv = document.createElement('div');
mediaDiv.classList.add('relative', 'inline-block'); // relative for positioning delete button

let mediaElement;
if (mediaItem.type.startsWith('image')) {
mediaElement = document.createElement('img');
mediaElement.src = mediaItem.url;
mediaElement.classList.add('max-w-[100px]', 'max-h-[100px]');
} else if (mediaItem.type.startsWith('video')) {
mediaElement = document.createElement('video');
mediaElement.src = mediaItem.url;
mediaElement.controls = true;
mediaElement.classList.add('max-w-[100px]', 'max-h-[100px]');
}

if (mediaElement) {
const deleteMediaButton = document.createElement('button');
deleteMediaButton.type = 'button';
deleteMediaButton.innerHTML = '×';
deleteMediaButton.classList.add('absolute', 'top-0', 'right-0', 'text-white', 'bg-red-500', 'hover:bg-red-700', 'rounded-full', 'w-5', 'h-5', 'flex', 'items-center', 'justify-center', 'text-xs');
deleteMediaButton.addEventListener('click', function() {
// Create a hidden input to send media to be deleted
const deleteMediaInput = document.createElement('input');
deleteMediaInput.type = 'hidden';
deleteMediaInput.name = 'delete_media[]'; // Send as array to handle multiple deletions
deleteMediaInput.value = mediaItem.id; // Assuming mediaItem has an 'id'
document.querySelector('#editModal form').appendChild(deleteMediaInput);
mediaDiv.remove(); // Remove media preview from modal visually
});

mediaDiv.appendChild(mediaElement);
mediaDiv.appendChild(deleteMediaButton);
currentMediaPreview.appendChild(mediaDiv);
}
});
}


editModal.classList.remove('hidden');
});
});


// Delete Modal
const deleteModal = document.getElementById('deleteModal');
const closeDeleteModal = document.getElementById('closeDeleteModal');
const cancelDeleteButton = document.getElementById('cancelDelete');
const deleteButtons = document.querySelectorAll('.delete-btn');

if (closeDeleteModal) {
closeDeleteModal.addEventListener('click', function() {
deleteModal.classList.add('hidden');
});
}
if (cancelDeleteButton) {
cancelDeleteButton.addEventListener('click', function() {
deleteModal.classList.add('hidden');
});
}

deleteButtons.forEach(button => {
button.addEventListener('click', function() {
const productId = this.dataset.id;
document.getElementById('deleteProductId').value = productId;
deleteModal.classList.remove('hidden');
});
});


// Success Modal
const successModal = document.getElementById('successModal');
const closeSuccessModal = document.getElementById('closeSuccessModal');
const closeSuccessButton = document.getElementById('closeSuccessButton');

const urlParams = new URLSearchParams(window.location.search);
const successAction = urlParams.get('success');

if (successAction === 'edit') {
document.getElementById('successMessage').textContent = 'Product succesvol bewerkt.';
successModal.classList.remove('hidden');
// Clear the success parameter from the URL for cleaner navigation
history.replaceState({}, document.title, window.location.pathname);
} else if (successAction === 'delete') {
document.getElementById('successMessage').textContent = 'Product succesvol verwijderd.';
successModal.classList.remove('hidden');
// Clear the success parameter from the URL for cleaner navigation
history.replaceState({}, document.title, window.location.pathname);
}

if (closeSuccessModal) {
closeSuccessModal.addEventListener('click', function() {
successModal.classList.add('hidden');
});
}
if (closeSuccessButton) {
closeSuccessButton.addEventListener('click', function() {
successModal.classList.add('hidden');
});
}
});