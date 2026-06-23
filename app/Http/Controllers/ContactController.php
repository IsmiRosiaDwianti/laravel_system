<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->get();

        return view(
            'contacts',
            compact('contacts')
        );
    }

    public function create()
    {
        return view('contact_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required'
        ]);

        Contact::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => true
        ]);

        return redirect()
            ->route('contacts')
            ->with(
                'success',
                'Kontak berhasil ditambahkan'
            );
    }

    public function destroy($id)
    {
        Contact::findOrFail($id)
            ->delete();

        return redirect()
            ->route('contacts')
            ->with(
                'success',
                'Kontak berhasil dihapus'
            );
    }
}