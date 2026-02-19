/**
 * AI Assistant Frontend Integration
 * Handles inline content generation with UI components
 */

class AiAssistant {
    constructor() {
        this.apiEndpoint = '/admin/ai-assistant/api';
        this.customInstructions = [];
        this.isGenerating = false;
        this.init();
    }

    init() {
        // Load custom instructions
        this.loadCustomInstructions();
        
        // Add event listeners for AI buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.ai-generate-btn')) {
                this.handleGenerateClick(e);
            }
        });
    }

    /**
     * Load custom instructions for dropdowns
     */
    async loadCustomInstructions() {
        try {
            const response = await fetch(`${this.apiEndpoint}/custom-instructions`);
            this.customInstructions = await response.json();
        } catch (error) {
            console.error('Failed to load custom instructions:', error);
        }
    }

    /**
     * Handle generate button click
     */
    async handleGenerateClick(event) {
        const button = event.target.closest('.ai-generate-btn');
        const fieldId = button.dataset.fieldId;
        const fieldType = button.dataset.fieldType || 'description';
        const fieldElement = document.getElementById(fieldId);

        if (!fieldElement) {
            console.error('Field element not found:', fieldId);
            return;
        }

        // Show modal
        this.showGenerationModal(fieldElement, fieldType);
    }

    /**
     * Show generation modal
     */
    showGenerationModal(fieldElement, fieldType) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'aiGenerationModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-magic"></i> Generate Content
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="aiGenerationForm">
                            <div class="mb-3">
                                <label class="form-label">Prompt *</label>
                                <textarea class="form-control" id="aiPrompt" rows="4" 
                                    placeholder="Describe what you want to generate..." required></textarea>
                                <small class="form-hint">Be specific about the tone, length, and content style</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Custom Instruction</label>
                                <select class="form-control" id="aiCustomInstruction">
                                    <option value="">-- None --</option>
                                    ${this.customInstructions.map(inst => 
                                        `<option value="${inst.id}">${inst.name}</option>`
                                    ).join('')}
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Temperature</label>
                                        <input type="number" id="aiTemperature" class="form-control" 
                                            value="0.7" step="0.1" min="0" max="2">
                                        <small class="form-hint">0 = deterministic, 2 = creative</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Tokens</label>
                                        <input type="number" id="aiMaxTokens" class="form-control" 
                                            value="500" min="10" max="4000">
                                    </div>
                                </div>
                            </div>

                            <div id="aiGenerationStatus" class="alert d-none" role="alert"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="aiGenerateBtn">
                            <i class="fas fa-wand-magic-sparkles"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);

        document.getElementById('aiGenerateBtn').addEventListener('click', () => {
            this.generateContent(fieldElement, fieldType, modal);
        });

        bootstrapModal.show();

        // Cleanup on modal close
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Generate content via API
     */
    async generateContent(fieldElement, fieldType, modalElement) {
        const prompt = document.getElementById('aiPrompt').value;
        const customInstructionId = document.getElementById('aiCustomInstruction').value;
        const temperature = parseFloat(document.getElementById('aiTemperature').value);
        const maxTokens = parseInt(document.getElementById('aiMaxTokens').value);
        const statusDiv = document.getElementById('aiGenerationStatus');

        if (!prompt.trim()) {
            this.showStatus(statusDiv, 'Please enter a prompt', 'warning');
            return;
        }

        if (this.isGenerating) {
            return;
        }

        this.isGenerating = true;
        this.showStatus(statusDiv, 'Generating content...', 'info');
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating content...';

        try {
            const response = await fetch(`${this.apiEndpoint}/generate-text`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    prompt,
                    custom_instruction_id: customInstructionId || null,
                    temperature,
                    max_tokens: maxTokens,
                    field_type: fieldType,
                }),
            });

            const data = await response.json();

            if (!data.success) {
                this.showStatus(statusDiv, data.error || 'Generation failed', 'danger');
                return;
            }

            // Populate field
            if (fieldElement.tagName === 'TEXTAREA') {
                fieldElement.value = data.content;
                fieldElement.dispatchEvent(new Event('change', { bubbles: true }));
            } else if (fieldElement.tagName === 'INPUT') {
                fieldElement.value = data.content;
                fieldElement.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                fieldElement.textContent = data.content;
            }

            this.showStatus(statusDiv, 
                `✓ Content generated (${data.tokens_used} tokens used via ${data.model})`, 'success');

            // Auto-close after 3 seconds
            setTimeout(() => {
                bootstrap.Modal.getInstance(modalElement).hide();
            }, 2000);
        } catch (error) {
            console.error('Generation error:', error);
            this.showStatus(statusDiv, 'Network error: ' + error.message, 'danger');
        } finally {
            this.isGenerating = false;
        }
    }

    /**
     * Show status message
     */
    showStatus(element, message, type) {
        element.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-warning', 'alert-danger');
        element.classList.add(`alert-${type}`);
        element.innerHTML = message;
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Add AI button to a field
     */
    static addGenerateButton(fieldId, fieldType = 'description') {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-primary ai-generate-btn ms-2';
        button.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Generate';
        button.dataset.fieldId = fieldId;
        button.dataset.fieldType = fieldType;

        const parent = field.parentElement;
        if (parent) {
            parent.appendChild(button);
        }
    }
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', () => {
    if (!window.aiAssistant) {
        window.aiAssistant = new AiAssistant();
    }
});
