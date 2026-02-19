<?php

namespace Botble\AiAssistant\Http\Controllers;

use Botble\AiAssistant\Models\AiCustomInstruction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiCustomInstructionController
{
    /**
     * Show instructions list
     */
    public function index()
    {
        $instructions = AiCustomInstruction::orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return view('plugins/ai-assistant::admin.instructions.index', [
            'instructions' => $instructions,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('plugins/ai-assistant::admin.instructions.create');
    }

    /**
     * Store new instruction
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ai_custom_instructions',
            'instruction' => 'required|string|min:10',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        AiCustomInstruction::create([
            'name' => $validated['name'],
            'instruction' => $validated['instruction'],
            'description' => $validated['description'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('ai-assistant.instructions.index')
            ->with('success', 'Custom instruction created successfully');
    }

    /**
     * Show edit form
     */
    public function edit(AiCustomInstruction $instruction)
    {
        return view('plugins/ai-assistant::admin.instructions.edit', [
            'instruction' => $instruction,
        ]);
    }

    /**
     * Update instruction
     */
    public function update(Request $request, AiCustomInstruction $instruction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ai_custom_instructions,name,' . $instruction->id,
            'instruction' => 'required|string|min:10',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $instruction->update($validated);

        return redirect()
            ->route('ai-assistant.instructions.index')
            ->with('success', 'Custom instruction updated successfully');
    }

    /**
     * Delete instruction
     */
    public function destroy(AiCustomInstruction $instruction): RedirectResponse
    {
        $instruction->delete();

        return redirect()
            ->route('ai-assistant.instructions.index')
            ->with('success', 'Custom instruction deleted successfully');
    }

    /**
     * Get active instructions for dropdown
     */
    public function getActive()
    {
        $instructions = AiCustomInstruction::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json($instructions);
    }
}
