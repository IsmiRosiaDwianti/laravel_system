<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactApiController extends Controller
{
    /**
     * ============================================================
     *  📋 GET ALL CONTACTS
     *  ============================================================
     *  🔗 URL: GET /api/contacts
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=10&page=1
     * ============================================================
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            
            $contacts = Contact::orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $contacts->items(),
                'pagination' => [
                    'total' => $contacts->total(),
                    'per_page' => $contacts->perPage(),
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📋 GET CONTACT DETAIL
     *  ============================================================
     *  🔗 URL: GET /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function show($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contact tidak ditemukan'
            ], 404);
        }
    }

    /**
     * ============================================================
     *  ➕ CREATE NEW CONTACT
     *  ============================================================
     *  🔗 URL: POST /api/contacts
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "phone": "...", "is_active": true }
     *  ⚠️ Batas Maksimal: 10 kontak
     * ============================================================
     */
    public function store(Request $request)
    {
        try {
            // 🔥 CEK BATAS MAKSIMAL KONTAK (10)
            $maxContacts = 10;
            $currentCount = Contact::count();
            
            if ($currentCount >= $maxContacts) {
                return response()->json([
                    'success' => false,
                    'message' => "Batas maksimal kontak adalah {$maxContacts} kontak. Anda sudah memiliki {$currentCount} kontak.",
                    'data' => [
                        'current' => $currentCount,
                        'max' => $maxContacts,
                        'remaining' => 0
                    ]
                ], 422);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15|unique:contacts,phone',
                'is_active' => 'boolean'
            ]);

            $contact = Contact::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $request->is_active ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact berhasil ditambahkan',
                'data' => $contact
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan contact: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  ✏️ UPDATE CONTACT
     *  ============================================================
     *  🔗 URL: PUT /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "phone": "...", "is_active": true }
     * ============================================================
     */
    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15|unique:contacts,phone,' . $id,
                'is_active' => 'boolean'
            ]);

            $contact->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'is_active' => $request->is_active ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact berhasil diupdate',
                'data' => $contact
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate contact: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🗑️ DELETE CONTACT
     *  ============================================================
     *  🔗 URL: DELETE /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contactName = $contact->name;
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact "' . $contactName . '" berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus contact: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔍 SEARCH CONTACTS
     *  ============================================================
     *  🔗 URL: GET /api/contacts/search
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?q=kata_kunci&per_page=10
     * ============================================================
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $perPage = $request->input('per_page', 10);
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'from' => 0,
                        'to' => 0,
                        'current_page' => 1,
                        'last_page' => 1,
                        'prev_page_url' => null,
                        'next_page_url' => null
                    ]
                ]);
            }
            
            $contacts = Contact::where('name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $contacts->items(),
                'pagination' => [
                    'total' => $contacts->total(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem(),
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'prev_page_url' => $contacts->previousPageUrl(),
                    'next_page_url' => $contacts->nextPageUrl()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📊 GET CONTACT LIMIT INFO
     *  ============================================================
     *  🔗 URL: GET /api/contacts/limit
     *  🔑 Butuh Auth: Sanctum Token
     *  📤 Response: { "current": 5, "max": 10, "remaining": 5, "can_add": true }
     * ============================================================
     */
    public function getLimitInfo()
    {
        try {
            $currentCount = Contact::count();
            $maxContacts = 10;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'current' => $currentCount,
                    'max' => $maxContacts,
                    'remaining' => max(0, $maxContacts - $currentCount),
                    'can_add' => $currentCount < $maxContacts
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil info limit: ' . $e->getMessage()
            ], 500);
        }
    }
}