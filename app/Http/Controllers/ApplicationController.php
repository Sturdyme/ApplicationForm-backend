<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
  public function store(Request $request)
{
    // 1. Validation - Match the keys used in React's formData.append()
    $request->validate([
        'first_name' => 'required|string',
        'last_name'  => 'required|string',
        'email'      => 'required|email',
        'dob'        => 'required|string',
        'phone'      => 'required|string',
        'position'   => 'required|string',
        'employment' => 'required|string',
        'address'    => 'required|string',
        'city'       => 'required|string',
        'state'      => 'required|string',
        'zip'        => 'required|string', // Changed from zipcode to zip
        'drivers_license' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'resume_file'     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        'terms_accepted'  => 'required', // '1' or '0' from React
        'signature'       => 'required|string' 
    ]);

    // 2. Handle File Uploads (Drivers License)
    $licensePath = $request->file('drivers_license')->store('licenses', 'public');

    // Handle Optional Resume
    $resumePath = null;
    if ($request->hasFile('resume_file')) {
        $resumePath = $request->file('resume_file')->store('resumes', 'public');
    }

    // 3. Handle the Signature (Optional: Save as File instead of long string)
    // If you want to keep the string, just use $request->signature.
    // If you want to save as PNG, use the base64_decode logic we discussed.
    $signatureData = $request->signature; 

    // 4. Create Record
    $application = Application::create([
        'first_name'     => $request->first_name,
        'last_name'      => $request->last_name,
        'email'          => $request->email,
        'dob'            => $request->dob,
        'phone'          => $request->phone,
        'position'       => $request->position,
        'employment'     => $request->employment,
        'address'        => $request->address,
        'city'           => $request->city,
        'state'          => $request->state,
        'zip'            => $request->zip,
        'license_path'   => $licensePath,
        'resume_path'    => $resumePath,
        'signature'      => $signatureData, 
        'terms_accepted' => $request->terms_accepted == '1', 
    ]);

    return response()->json([
        'message' => "Application submitted successfully",
        'id'      => $application->id
    ], 201);
}
}
