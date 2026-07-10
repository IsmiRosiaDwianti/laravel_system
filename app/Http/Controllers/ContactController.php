<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        
        $totalContacts = Contact::count();
        $totalActive = Contact::where('is_active', true)->count();
        $totalInactive = Contact::where('is_active', false)->count();
        
        $contacts = Contact::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        
        return view('contacts', compact(
            'contacts',
            'totalContacts',
            'totalActive',
            'totalInactive'
        ));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        return redirect()->route('contacts');
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        try {
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

            return redirect()
                ->route('contacts')
                ->with('success', 'Kontak "' . $contact->name . '" berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        }
        
        return redirect()->route('contacts');
    }

    /**
     * Update the specified contact in storage.
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

            return redirect()
                ->route('contacts')
                ->with('success', 'Kontak "' . $contact->name . '" berhasil diupdate');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contactName = $contact->name;
            $contact->delete();

            return redirect()
                ->route('contacts')
                ->with('success', 'Kontak "' . $contactName . '" berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ================================================================
    // 🔍 SEARCH METHODS
    // ================================================================

    /**
     * ============================================================
     *  🔍 SEARCH CONTACTS (AJAX)
     *  ============================================================
     *  🔗 URL: GET /contacts/search
     *  🔑 Butuh Auth: Session (web)
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
            
            // 🔥 SEARCH BERDASARKAN NAMA ATAU NOMOR TELEPON
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
     *  🔍 API SEARCH CONTACTS
     *  ============================================================
     *  🔗 URL: GET /api/contacts/search
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?q=kata_kunci&per_page=10&page=1
     * ============================================================
     */
    public function apiSearch(Request $request)
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
            
            // 🔥 SEARCH BERDASARKAN NAMA ATAU NOMOR TELEPON
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

    // ================================================================
    // 📡 API METHODS - UNTUK POSTMAN / MOBILE APP
    // ================================================================

    /**
     * ============================================================
     *  📡 API: GET ALL CONTACTS
     *  ============================================================
     *  🔗 URL: GET /api/contacts
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=10&page=1
     * ============================================================
     */
    public function apiIndex(Request $request)
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
     *  📡 API: GET CONTACT DETAIL
     *  ============================================================
     *  🔗 URL: GET /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function apiShow($id)
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
     *  📡 API: CREATE CONTACT
     *  ============================================================
     *  🔗 URL: POST /api/contacts
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "phone": "...", "is_active": true }
     * ============================================================
     */
    public function apiStore(Request $request)
    {
        try {
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
     *  📡 API: UPDATE CONTACT
     *  ============================================================
     *  🔗 URL: PUT /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "phone": "...", "is_active": true }
     * ============================================================
     */
    public function apiUpdate(Request $request, $id)
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
     *  📡 API: DELETE CONTACT
     *  ============================================================
     *  🔗 URL: DELETE /api/contacts/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function apiDestroy($id)
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
}