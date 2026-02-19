(function($) {
    'use strict';

    /**
     * AI Inline Generator
     * Handles AJAX requests for AI-powered content generation across admin panel
     */

    $(document).ready(function() {
        // Initialize AI button handlers
        initAiGenerationButtons();
        
        // Auto-inject AI buttons into common content fields
        setupAutoFieldDetection();
    });

    /**
     * Auto-detect and inject AI buttons into content fields
     */
    function setupAutoFieldDetection() {
        // Target common content fields across all plugins
        var contentSelectors = [
            'textarea[name="content"]',
            'textarea[name="name"]',
            'textarea[name="title"]',
            'textarea[name="description"]',
            'textarea[name="short_description"]',
            'textarea[name="excerpt"]',
            '.editor-field',
            '.summernote',
            '.ck-editor',
            '[contenteditable="true"]'
        ];

        contentSelectors.forEach(function(selector) {
            $(selector).each(function() {
                var $field = $(this);
                
                // Skip if buttons already added
                if ($field.data('ai-buttons-added')) {
                    return;
                }
                
                // Determine field type and create appropriate buttons
                createAiButtonsForField($field);
                $field.data('ai-buttons-added', true);
            });
        });
    }

    /**
     * Create AI buttons for a specific field
     */
    function createAiButtonsForField($field) {
        // Get field name to determine content type
        var fieldName = $field.attr('name') || '';
        var $wrapper = $field.closest('.form-group, .mb-3, .field-wrapper');
        
        if (!$wrapper.length) {
            $wrapper = $field.parent();
        }

        // Create button container
        var $buttonContainer = $('<div class="ai-buttons-container" style="margin-top: 8px;"></div>');
        
        // Determine which generation endpoints to create buttons for
        var buttons = [];
        
        if (fieldName.includes('title') || fieldName.includes('name')) {
            buttons.push({
                label: '<i class="fas fa-wand-magic-sparkles"></i> Generate Title',
                route: '/admincp/ai-assistant/api/generate-post-title',
                inputs: 'title,keywords'
            });
        } else if (fieldName.includes('description') || fieldName.includes('short_description')) {
            buttons.push({
                label: '<i class="fas fa-wand-magic-sparkles"></i> Generate Description',
                route: '/admincp/ai-assistant/api/generate-product-description',
                inputs: 'title,description,keywords'
            });
        } else if (fieldName.includes('content') || fieldName.includes('body')) {
            buttons.push({
                label: '<i class="fas fa-wand-magic-sparkles"></i> Generate Content',
                route: '/admincp/ai-assistant/api/generate-post-content',
                inputs: 'title,content,keywords'
            });
        }
        
        // Add generic text generation for any field
        buttons.push({
            label: '<i class="fas fa-wand-magic-sparkles"></i> AI Generate',
            route: '/admincp/ai-assistant/api/generate-text',
            inputs: 'title,content'
        });

        // Create and add buttons
        buttons.forEach(function(btn) {
            var $btn = $('<button type="button" class="btn btn-sm btn-outline-primary ai-generate-btn" ' +
                'data-route="' + btn.route + '" ' +
                'data-inputs="' + btn.inputs + '" ' +
                'data-target="' + fieldName + '" ' +
                'style="margin-right: 5px;" title="Generate with AI">' +
                btn.label +
                '</button>');
            
            $buttonContainer.append($btn);
        });

        // Insert buttons after the field
        $field.after($buttonContainer);
    }

    /**
     * Initialize all AI generation button click handlers
     */
    function initAiGenerationButtons() {
        $(document).on('click', '.ai-generate-btn', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const route = $btn.data('route');
            const targetSelector = $btn.data('target');
            const inputData = $btn.data('inputs');

            if (!route) {
                showError('No generation route configured');
                return;
            }

            // Prevent duplicate clicks
            if ($btn.hasClass('generating')) {
                return;
            }

            // Gather input data from form fields
            const requestData = gatherInputData(inputData);

            // Show loading state
            showLoading($btn);

            // Make AJAX request
            $.ajax({
                url: route,
                type: 'GET',
                data: requestData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Update target field
                        updateTargetField(targetSelector, response.data);

                        // Show success notification
                        showSuccess('Content generated successfully!');
                    } else {
                        showError(response.message || response.error || 'Generation failed');
                    }
                },
                error: function(xhr, status, error) {
                    const errorMessage = getErrorMessage(xhr);
                    showError(errorMessage);
                    console.error('AI Generation Error:', error);
                },
                complete: function() {
                    // Hide loading state
                    hideLoading($btn);
                }
            });
        });
    }

    /**
     * Gather input data from form fields based on input configuration
     */
    function gatherInputData(inputsConfig) {
        const data = {};

        if (typeof inputsConfig === 'object') {
            $.each(inputsConfig, function(outputKey, inputSelector) {
                const $input = $(inputSelector);
                if ($input.length) {
                    let value = $input.val();

                    // Handle different input types
                    if ($input.is('textarea') || $input.is('input[type="text"]')) {
                        value = $input.val();
                    } else if ($input.is('[contenteditable]')) {
                        value = $input.html();
                    } else if ($input.hasClass('ck-editor')) {
                        // CKEditor
                        if (window.CKEDITOR && CKEDITOR.instances) {
                            const editorName = $input.attr('name');
                            if (CKEDITOR.instances[editorName]) {
                                value = CKEDITOR.instances[editorName].getData();
                            }
                        }
                    } else if ($input.hasClass('summernote')) {
                        // Summernote
                        value = $input.summernote('code');
                    }

                    data[outputKey] = value.substring(0, 500); // Limit to 500 chars for context
                }
            });
        }

        return data;
    }

    /**
     * Update target field with generated content
     */
    function updateTargetField(targetSelector, content) {
        const $target = $(targetSelector);

        if (!$target.length) {
            console.warn('Target field not found:', targetSelector);
            return;
        }

        // Determine field type and update accordingly
        if ($target.is('input[type="text"]') || $target.is('textarea:not(.summernote):not(.ck-editor)')) {
            $target.val(content).trigger('change');
        } else if ($target.hasClass('summernote')) {
            // Update Summernote editor
            $target.summernote('code', content);
        } else if ($target.hasClass('ck-editor') || $target.closest('.ck-editor').length) {
            // Update CKEditor
            const editorName = $target.attr('name');
            if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances[editorName]) {
                CKEDITOR.instances[editorName].setData(content);
            }
        } else if ($target.is('[contenteditable]')) {
            // Update contenteditable element
            $target.html(content).trigger('change');
        }

        // Trigger change event for form watchers
        $target.trigger('change').trigger('blur');
    }

    /**
     * Show loading state on button
     */
    function showLoading($btn) {
        $btn.addClass('generating disabled');
        $btn.prop('disabled', true);

        // Add loading spinner if not present
        if (!$btn.find('.ai-spinner').length) {
            $btn.prepend('<span class="ai-spinner"><i class="fas fa-spinner fa-spin"></i> </span>');
        } else {
            $btn.find('.ai-spinner').show();
        }

        // Change button text
        const originalText = $btn.data('original-text') || $btn.html();
        $btn.data('original-text', originalText);
        $btn.find('.btn-text').text('Generating...');
    }

    /**
     * Hide loading state on button
     */
    function hideLoading($btn) {
        $btn.removeClass('generating disabled');
        $btn.prop('disabled', false);

        // Hide loading spinner
        $btn.find('.ai-spinner').hide();

        // Restore button text
        const originalText = $btn.data('original-text');
        if (originalText) {
            $btn.find('.btn-text').text('Generate');
        }
    }

    /**
     * Show success notification
     */
    function showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message, 'AI Assistant');
        } else if (typeof notify !== 'undefined') {
            notify.success(message);
        } else {
            alert(message);
        }
    }

    /**
     * Show error notification
     */
    function showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message, 'AI Assistant Error');
        } else if (typeof notify !== 'undefined') {
            notify.error(message);
        } else {
            alert('Error: ' + message);
        }
    }

    /**
     * Extract error message from AJAX response
     */
    function getErrorMessage(xhr) {
        try {
            const response = JSON.parse(xhr.responseText);
            return response.message || response.error || 'An error occurred';
        } catch (e) {
            return 'Failed to generate content (Server error)';
        }
    }

    /**
     * Expose public API for advanced usage
     */
    window.AiGenerator = {
        generate: function(route, targetSelector, inputData) {
            const $btn = $('<button class="ai-generate-btn"></button>')
                .data('route', route)
                .data('target', targetSelector)
                .data('inputs', inputData);

            $btn.click();
        },

        updateField: function(selector, content) {
            updateTargetField(selector, content);
        },

        showSuccess: showSuccess,
        showError: showError
    };

})(jQuery);
