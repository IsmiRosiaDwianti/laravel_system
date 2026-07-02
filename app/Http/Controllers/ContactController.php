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
        
        // Hitung statistik
        $totalContacts = Contact::count();
        $totalActive = Contact::where('is_active', true)->count();
        $totalInactive = Contact::where('is_active', false)->count();
        
        // Gunakan paginate() BUKAN get()
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
     * Tidak dipakai karena pakai modal
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
     * Support AJAX untuk modal edit
     */
    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        
        // Jika request dari AJAX, return JSON
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        }
        
        // Jika bukan AJAX, redirect ke contacts
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
}