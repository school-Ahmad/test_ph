<?php
// views/add_item.view.php

// Zorg dat $view_data beschikbaar is (komt van de logic file)
if (!isset($view_data)) {
    $view_data = ['message' => '', 'message_type' => ''];
}

// BELANGRIJK: Gebruik __DIR__ voor robuuste includes binnen dezelfde map

?>
<?php include __DIR__ . '/header.view.php'; ?>
<!-- Dit is de content die binnen de <main> tag van sidebar.view.php wordt geplaatst -->
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50 py-8">
    <div class="flex md:flex-row">
        <?php include __DIR__ . '/sidebar.view.php'; ?>

        <div class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    Nieuw Machine Toevoegen
                </h1>
                <p class="text-gray-600 text-lg">Voeg een nieuw machine toe aan je collectie</p>
            </div>

            <!-- Form Container -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-6">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <h2 class="text-xl font-semibold text-white">Item Details</h2>
                    </div>
                </div>

                <div class="p-8">
                    <?php if (!empty($view_data['message'])): ?>
                        <div class="mb-6 p-4 rounded-xl border-l-4 <?php echo ($view_data['message_type'] === 'success') ? 'bg-emerald-50 border-emerald-400 text-emerald-700' : 'bg-red-50 border-red-400 text-red-700'; ?>">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <?php if ($view_data['message_type'] === 'success'): ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    <?php else: ?>
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    <?php endif; ?>
                                </svg>
                                <?php echo htmlspecialchars($view_data['message']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?page=add_item" method="POST" enctype="multipart/form-data" class="space-y-8">
                        <!-- Titel Field -->
                        <div class="space-y-2">
                            <label for="title" class="flex items-center text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Titel
                            </label>
                            <div class="relative">
                                <input type="text" id="title" name="title" required
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                       placeholder="Voer de titel in..."
                                       class="w-full px-4 py-3 pl-12 border-2 border-gray-200 rounded-xl
                                                  focus:border-purple-500 focus:ring-4 focus:ring-purple-100
                                                  transition-all duration-200 text-gray-900 placeholder-gray-400
                                                  hover:border-purple-300"/>
                                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Beschrijving Field -->
                        <div class="space-y-2">
                            <label for="description" class="flex items-center text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Beschrijving
                            </label>
                            <div class="relative">
                                    <textarea id="description" name="description" rows="5" required
                                              placeholder="Voer een gedetailleerde beschrijving in..."
                                              class="w-full px-4 py-3 pl-12 border-2 border-gray-200 rounded-xl
                                                     focus:border-purple-500 focus:ring-4 focus:ring-purple-100
                                                     transition-all duration-200 text-gray-900 placeholder-gray-400
                                                     hover:border-purple-300 resize-none"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                <svg class="absolute left-4 top-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Afbeelding Field -->
                        <div class="space-y-2">
                            <label for="image" class="flex items-center text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Afbeelding
                            </label>

                            <!-- File Upload Area -->
                            <div class="relative">
                                <input type="file" id="image" name="image" accept="image/*" required class="hidden">
                                <div id="uploadArea" class="border-2 border-dashed border-purple-300 rounded-xl p-8 text-center cursor-pointer
                                                               hover:border-purple-400 hover:bg-purple-50 transition-all duration-200">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-700">Klik om een afbeelding te uploaden</p>
                                            <p class="text-sm text-gray-500 mt-1">of sleep een bestand hierheen</p>
                                        </div>
                                        <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Bestand Selecteren
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 flex items-center mt-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Ondersteunde formaten: JPG, JPEG, PNG, GIF (Max 5MB)
                            </p>

                            <!-- Selected File Display -->
                            <div id="fileDisplay" class="mt-4 hidden">
                                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p id="fileName" class="font-medium text-gray-900"></p>
                                                <p class="text-sm text-gray-500">Klaar om te uploaden</p>
                                            </div>
                                        </div>
                                        <button type="button" id="removeFileBtn"
                                                class="p-2 text-red-500 hover:bg-red-100 rounded-lg transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6 border-t border-gray-100">
                            <button type="submit"
                                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 px-6 rounded-xl
                                               font-semibold text-lg shadow-lg hover:shadow-xl
                                               hover:from-purple-700 hover:to-indigo-700
                                               transform hover:-translate-y-0.5 transition-all duration-200
                                               focus:outline-none focus:ring-4 focus:ring-purple-300">
                                    <span class="flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Item Toevoegen
                                    </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center">
                <p class="text-gray-500 text-sm">Vul alle vereiste velden in om je item succesvol toe te voegen</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const uploadArea = document.getElementById('uploadArea');
        const fileDisplay = document.getElementById('fileDisplay');
        const fileNameSpan = document.getElementById('fileName');
        const removeFileBtn = document.getElementById('removeFileBtn');

        // Click handler voor upload area
        uploadArea.addEventListener('click', function() {
            imageInput.click();
        });

        // Drag and drop handlers
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('border-purple-500', 'bg-purple-100');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-purple-500', 'bg-purple-100');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('border-purple-500', 'bg-purple-100');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                showSelectedFile(files[0]);
            }
        });

        // Event listener voor wanneer een bestand wordt geselecteerd
        imageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                showSelectedFile(this.files[0]);
            } else {
                hideSelectedFile();
            }
        });

        // Event listener voor de verwijderknop
        removeFileBtn.addEventListener('click', function() {
            imageInput.value = '';
            hideSelectedFile();
        });

        function showSelectedFile(file) {
            fileNameSpan.textContent = file.name;
            fileDisplay.classList.remove('hidden');
            uploadArea.style.display = 'none';
        }

        function hideSelectedFile() {
            fileNameSpan.textContent = '';
            fileDisplay.classList.add('hidden');
            uploadArea.style.display = 'block';
        }
    });
</script>