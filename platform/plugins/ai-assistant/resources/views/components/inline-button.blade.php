@php
/**
 * AI Generate Button Component
 * 
 * Usage:
 * <x-plugins.ai-assistant.inline-button 
 *     :route="route('admin.ai-assistant.api.generate-post-title')"
 *     target="#post_name"
 *     :inputs="['context' => '#post_content']"
 *     field="Title"
 *     class="btn-sm"
 * />
 */

$inputs = $inputs ?? [];
$field = $field ?? 'Content';
$class = $class ?? '';
$title = $title ?? "Generate {$field} using AI";

// Convert inputs array to JSON
$inputsJson = json_encode($inputs);
@endphp

<button type="button" 
        class="btn btn-secondary btn-icon ai-generate-btn {{ $class }}"
        data-route="{{ $route }}"
        data-target="{{ $target }}"
        data-inputs='{{ $inputsJson }}'
        title="{{ $title }}"
        {{ $attributes }}>
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" 
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2a10 10 0 1 0 10 10"></path>
        <path d="M8 12l3-3 3 3"></path>
        <path d="M11 9v6"></path>
    </svg>
    <span class="ai-spinner" style="display: none; margin-right: 5px;">
        <i class="fas fa-spinner fa-spin"></i>
    </span>
    <span class="btn-text">{{ $slot ?? "AI {$field}" }}</span>
</button>
