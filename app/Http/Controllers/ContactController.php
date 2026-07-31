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
        $maxContacts = 10; // Maksimal kontak yang diizinkan
        
        $contacts = Contact::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        
        return view('contacts', compact(
            'contacts',
            'totalContacts',
            'totalActive',
            'totalInactive',
            'perPage',
            'maxContacts'
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
            // 🔥 CEK APAKAH SUDAH MENCAPAI BATAS MAKSIMAL (10 KONTAK)
            $currentCount = Contact::count();
            $maxContacts = 10;
            
            if ($currentCount >= $maxContacts) {
                return redirect()
                    ->route('contacts')
                    ->with('error', '❌ Batas maksimal kontak adalah ' . $maxContacts . ' kontak. Anda sudah memiliki ' . $currentCount . ' kontak.');
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

            return redirect()
                ->route('contacts')
                ->with('success', '✅ Kontak "' . $contact->name . '" berhasil ditambahkan');

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
                ->with('success', '✅ Kontak "' . $contact->name . '" berhasil diupdate');

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
                ->with('success', '✅ Kontak "' . $contactName . '" berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Search contacts (AJAX)
     * GET /contacts/search
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
     * API: Get contacts limit info
     * GET /api/contacts/limit
     */
    public function getLimitInfo()
    {
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
    }
}