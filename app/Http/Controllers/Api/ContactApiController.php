<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactApiController extends Controller
{
    /**
     * GET /api/contacts
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Contact::latest()->get()
        ]);
    }

    /**
     * POST /api/contacts
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'nullable|boolean'
        ]);

        $contact = Contact::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact berhasil ditambahkan',
            'data' => $contact
        ], 201);
    }

    /**
     * GET /api/contacts/{id}
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    /**
     * PUT /api/contacts/{id}
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'required|boolean'
        ]);

        $contact->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact berhasil diupdate',
            'data' => $contact->fresh()
        ]);
    }

    /**
     * DELETE /api/contacts/{id}
     */
    public function destroy(string $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact tidak ditemukan'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact berhasil dihapus'
        ]);
    }
}