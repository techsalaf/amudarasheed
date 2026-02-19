(function($) {
    'use strict';

    /**
     * AI Assistant Provider Model Selection
     * Handles real-time model filtering when provider is selected
     */

    $(document).ready(function() {
        initProviderModelSelection();
    });

    /**
     * Initialize provider and model selection
     */
    function initProviderModelSelection() {
        const $providerSelect = $('select[name="provider_id"]');
        const $modelInput = $('input[name="model"]');
        
        // If we have both provider select and model input, initialize real-time filtering
        if ($providerSelect.length && $modelInput.length) {
            // Create a model dropdown and insert it after the model input
            createModelDropdown($modelInput, $providerSelect);
            
            // Load models when provider changes
            $providerSelect.on('change', function() {
                updateAvailableModels($(this), $modelInput);
            });
            
            // Load initial models if provider is already selected
            if ($providerSelect.val()) {
                updateAvailableModels($providerSelect, $modelInput);
            }
        }
    }

    /**
     * Create a model dropdown and insert it into the DOM
     */
    function createModelDropdown($modelInput, $providerSelect) {
        // Create dropdown container
        const $container = $('<div class="model-selection-container mb-3"></div>');
        
        // Create model select dropdown
        const $modelSelect = $(`
            <select name="model_select" class="form-control model-dropdown" 
                style="display: none;">
                <option value="">-- Auto-select from API key --</option>
            </select>
        `);
        
        // Create button to toggle between dropdown and text input
        const $toggleBtn = $(`
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2 toggle-model-input">
                <i class="fas fa-list"></i> Use Model Dropdown
            </button>
        `);
        
        // Insert after model input
        $modelInput.after($container);
        $container.append($modelSelect);
        $container.append($toggleBtn);
        
        // Handle toggle button
        $toggleBtn.on('click', function(e) {
            e.preventDefault();
            toggleModelInput($modelInput, $modelSelect, $toggleBtn);
        });
        
        // Handle model select change
        $modelSelect.on('change', function() {
            if ($(this).val()) {
                $modelInput.val($(this).val());
            }
        });
    }

    /**
     * Toggle between text input and dropdown for model selection
     */
    function toggleModelInput($modelInput, $modelSelect, $toggleBtn) {
        $modelInput.toggle();
        $modelSelect.toggle();
        
        if ($modelInput.is(':visible')) {
            $toggleBtn.html('<i class="fas fa-list"></i> Use Model Dropdown');
            $modelInput.focus();
        } else {
            $toggleBtn.html('<i class="fas fa-keyboard"></i> Enter Custom Model');
            $modelSelect.focus();
        }
    }

    /**
     * Update available models based on selected provider
     */
    function updateAvailableModels($providerSelect, $modelInput) {
        const providerId = $providerSelect.val();
        
        if (!providerId) {
            $('select.model-dropdown').html('<option value="">-- Select Provider First --</option>');
            return;
        }
        
        // Get the route for fetching models
        // Use relative path to respect base URL/subdirectory installations
        const baseUrl = window.location.origin + window.location.pathname.split('/admincp')[0];
        const routePath = `${baseUrl}/admincp/ai-assistant/api-keys/get-models/${providerId}`;
        
        $.ajax({
            url: routePath,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.models) {
                    populateModelDropdown(response.models, $modelInput.val());
                }
            },
            error: function(xhr) {
                console.error('Failed to fetch available models:', xhr);
                $('select.model-dropdown').html(
                    '<option value="">-- Failed to load models --</option>'
                );
            }
        });
    }

    /**
     * Populate the model dropdown with available models
     */
    function populateModelDropdown(models, selectedModel) {
        const $modelSelect = $('select.model-dropdown');
        
        if (!$modelSelect.length) return;
        
        // Clear existing options except the first one
        $modelSelect.find('option:not(:first)').remove();
        
        // If no models, show helpful message
        if (!models || models.length === 0) {
            const $option = $(`<option value="">-- No API key configured for this provider --</option>`);
            $modelSelect.append($option);
            return;
        }
        
        // Add models to dropdown
        models.forEach(function(model) {
            const $option = $(`<option value="${model}">${model}</option>`);
            if (model === selectedModel) {
                $option.prop('selected', true);
            }
            $modelSelect.append($option);
        });
    }

    /**
     * Expose public API
     */
    window.AiProviderModels = {
        updateModels: updateAvailableModels,
        populateDropdown: populateModelDropdown,
        initSelection: initProviderModelSelection
    };

})(jQuery);
