<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuggestedEdit;
use App\Models\CentralItem;
use Illuminate\Support\Facades\Auth;

class SuggestedEditController extends Controller
{
    /**
     * Display the list of pending edit suggestions (Curators and Admins only).
     */
    public function index()
    {
        if (!Auth::user()->isCurator()) {
            abort(403, 'Acesso negado. Apenas curadores ou administradores podem gerenciar sugestões de edições.');
        }

        // Fetch pending suggestions with book and author details
        $suggestions = SuggestedEdit::with(['centralItem', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('catalog.suggested_edits', compact('suggestions'));
    }

    /**
     * Approve a suggested edit and merge metadata into the central catalog.
     */
    public function approve($id)
    {
        if (!Auth::user()->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $suggestion = SuggestedEdit::findOrFail($id);
        $item = $suggestion->centralItem;

        // Apply metadata updates directly from suggestion JSON payload
        $metadata = $suggestion->metadata;
        
        $item->update([
            'title' => $metadata['title'],
            'isbn' => $metadata['isbn'] ?: null,
            'metadata' => $metadata,
        ]);

        // Mark suggestion as approved
        $suggestion->update([
            'status' => 'approved'
        ]);

        return redirect()->route('catalog.suggested_edits')->with('success', 'A sugestão foi aprovada e mesclada com sucesso no Catálogo Central!');
    }

    /**
     * Reject a suggested edit.
     */
    public function reject($id)
    {
        if (!Auth::user()->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $suggestion = SuggestedEdit::findOrFail($id);
        
        // Mark suggestion as rejected
        $suggestion->update([
            'status' => 'rejected'
        ]);

        return redirect()->route('catalog.suggested_edits')->with('success', 'A sugestão de edição foi rejeitada.');
    }
}
