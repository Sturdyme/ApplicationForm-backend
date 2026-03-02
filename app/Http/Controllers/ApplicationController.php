<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'dob' => 'required|string',
            'phone' => 'required|string',
            'position' => 'required|string',
            'employment' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'drivers_license' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'terms_accepted' => 'accepted'
        ]);

        $licensePath = $request->file('drivers_license')->store('license', 'public');

        $resumePath = null;
        if ($request->hasFile('resume_file')) {
            $resumePath = $request->file('resume_file')->store('resume', 'public');
        }

        Application::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'dob' => $request->dob,
            'phone' => $request->phone,
            'position' => $request->position,
            'employment' => $request->employment,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'license_path' => $licensePath,
            'resume_path' => $resumePath,
            'terms_accepted' => $request->boolean('terms_accepted'),
        ]);

        return response()->json([
            'message' => "Application submitted successfully"
        ]);
    }
}
