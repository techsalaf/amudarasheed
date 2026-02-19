<?php

namespace Botble\AiAssistant\Http\Controllers;

use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Services\AiGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiGenerationController
{
    public function __construct(
        protected AiGenerationService $aiService
    ) {}

    /**
     * Generate text content (generic)
     */
    public function generateText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'custom_instruction_id' => 'nullable|integer|exists:ai_custom_instructions,id',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:10|max:4000',
        ]);

        $options = [
            'temperature' => $validated['temperature'] ?? 0.7,
            'max_tokens' => $validated['max_tokens'] ?? 1000,
        ];

        $customInstruction = null;
        if ($validated['custom_instruction_id'] ?? null) {
            $instruction = \Botble\AiAssistant\Models\AiCustomInstruction::find(
                $validated['custom_instruction_id']
            );
            $customInstruction = $instruction?->instruction;
        }

        $result = $this->aiService->generateText(
            $validated['prompt'],
            $options,
            $customInstruction
        );

        if (!$result->success) {
            return response()->json([
                'success' => false,
                'error' => $result->error,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'content' => $result->content,
            'tokens_used' => $result->getTotalTokens(),
            'model' => $result->model,
        ]);
    }

    /**
     * Generate blog post title
     */
    public function generatePostTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context' => 'nullable|string|max:500',
            'lang' => 'nullable|string|max:5',
        ]);

        $prompt = "Generate a catchy, SEO-friendly blog post title. " .
            ($validated['context'] ? "Context: {$validated['context']}" : "");

        return $this->generateContent($prompt, 100, 'Blog Post Title');
    }

    /**
     * Generate blog post content/excerpt
     */
    public function generatePostContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'context' => 'nullable|string|max:1000',
            'lang' => 'nullable|string|max:5',
        ]);

        $prompt = "Write a compelling, engaging blog post based on this title: \"{$validated['title']}\". " .
            ($validated['context'] ? "Additional context: {$validated['context']}" : "") .
            " Make it informative and well-structured with proper formatting.";

        return $this->generateContent($prompt, 2000, 'Blog Post Content');
    }

    /**
     * Generate page title
     */
    public function generatePageTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context' => 'nullable|string|max:500',
        ]);

        $prompt = "Generate a clear, descriptive page title. " .
            ($validated['context'] ? "Page purpose: {$validated['context']}" : "");

        return $this->generateContent($prompt, 80, 'Page Title');
    }

    /**
     * Generate page content
     */
    public function generatePageContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'context' => 'nullable|string|max:1000',
        ]);

        $prompt = "Write clear, professional page content for: \"{$validated['title']}\". " .
            ($validated['context'] ? "Details: {$validated['context']}" : "") .
            " Make it informative and user-friendly with proper structure.";

        return $this->generateContent($prompt, 2000, 'Page Content');
    }

    /**
     * Generate product name/title
     */
    public function generateProductTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:200',
            'keywords' => 'nullable|string|max:500',
        ]);

        $prompt = "Generate an attractive, SEO-friendly product name. " .
            ($validated['category'] ? "Category: {$validated['category']} " : "") .
            ($validated['keywords'] ? "Keywords: {$validated['keywords']}" : "");

        return $this->generateContent($prompt, 100, 'Product Title');
    }

    /**
     * Generate product description
     */
    public function generateProductDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'category' => 'nullable|string|max:200',
            'features' => 'nullable|string|max:1000',
        ]);

        $prompt = "Write a compelling product description for: \"{$validated['title']}\" " .
            ($validated['category'] ? "Category: {$validated['category']} " : "") .
            ($validated['features'] ? "Key features: {$validated['features']}" : "") .
            " Focus on benefits and make it engaging for customers.";

        return $this->generateContent($prompt, 1500, 'Product Description');
    }

    /**
     * Generate product short description
     */
    public function generateProductShortDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
        ]);

        $prompt = "Write a brief, catchy product short description for: \"{$validated['title']}\". " .
            "Keep it under 160 characters, perfect for search engine snippets.";

        return $this->generateContent($prompt, 50, 'Product Short Description');
    }

    /**
     * Generate SEO meta title
     */
    public function generateSeoTitle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_title' => 'required|string|max:500',
            'content_type' => 'nullable|string|max:50', // page, post, product
            'keywords' => 'nullable|string|max:300',
        ]);

        $contentType = $validated['content_type'] ?? 'content';
        $prompt = "Generate an SEO-optimized meta title (50-60 chars) for {$contentType}: " .
            "\"{$validated['content_title']}\" " .
            ($validated['keywords'] ? "Include keywords: {$validated['keywords']}" : "");

        return $this->generateContent($prompt, 50, 'SEO Meta Title');
    }

    /**
     * Generate SEO meta description
     */
    public function generateSeoDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_title' => 'required|string|max:500',
            'content_snippet' => 'nullable|string|max:1000',
            'keywords' => 'nullable|string|max:300',
        ]);

        $prompt = "Generate an SEO meta description (155-160 chars) for: \"{$validated['content_title']}\" " .
            ($validated['content_snippet'] ? "Content preview: {$validated['content_snippet']} " : "") .
            ($validated['keywords'] ? "Include keywords: {$validated['keywords']}" : "") .
            "Make it compelling to encourage clicks from search results.";

        return $this->generateContent($prompt, 60, 'SEO Meta Description');
    }

    /**
     * Generate category name
     */
    public function generateCategoryName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_category' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
        ]);

        $prompt = "Generate a clear, SEO-friendly category name. " .
            ($validated['parent_category'] ? "Parent category: {$validated['parent_category']} " : "") .
            ($validated['description'] ? "Description: {$validated['description']}" : "");

        return $this->generateContent($prompt, 80, 'Category Name');
    }

    /**
     * Generate category description
     */
    public function generateCategoryDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'products_info' => 'nullable|string|max:500',
        ]);

        $prompt = "Write a descriptive category description for: \"{$validated['name']}\" " .
            ($validated['products_info'] ? "Products info: {$validated['products_info']}" : "") .
            "Make it informative and product-focused.";

        return $this->generateContent($prompt, 500, 'Category Description');
    }

    /**
     * Generate tag name
     */
    public function generateTagName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'context' => 'nullable|string|max:500',
        ]);

        $prompt = "Generate a relevant, SEO-friendly tag. " .
            ($validated['context'] ? "Context: {$validated['context']}" : "");

        return $this->generateContent($prompt, 30, 'Tag Name');
    }

    /**
     * Generic content generation helper
     */
    protected function generateContent(string $prompt, int $maxTokens = 1000, string $fieldType = 'Content'): JsonResponse
    {
        try {
            $result = $this->aiService->generateText($prompt, [
                'max_tokens' => min($maxTokens, 4000),
                'temperature' => 0.7,
            ]);

            if (!$result->success) {
                return response()->json([
                    'success' => false,
                    'message' => $result->error ?? 'Failed to generate content',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => trim($result->content),
                'model' => $result->model,
                'tokens' => $result->getTotalTokens(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate image
     */
    public function generateImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'size' => 'nullable|in:256x256,512x512,1024x1024',
        ]);

        $options = [
            'size' => $validated['size'] ?? '1024x1024',
        ];

        $result = $this->aiService->generateImage($validated['prompt'], $options);

        if (!$result->success) {
            return response()->json([
                'success' => false,
                'error' => $result->error,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'image_url' => $result->content,
            'model' => $result->model,
        ]);
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): JsonResponse
    {
        $models = [];
        $apiKeys = AiApiKey::with('provider')
            ->where('is_active', true)
            ->get();

        foreach ($apiKeys as $apiKey) {
            $provider = $apiKey->provider;
            $models[$provider->name] = [
                'provider_id' => $provider->id,
                'provider_name' => $provider->display_name,
                'model' => $apiKey->model,
            ];
        }

        return response()->json($models);
    }
}
