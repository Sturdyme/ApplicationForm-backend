<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
public function store(Request $request)
{
    $key = 'application-submission:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 3)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'message' => "Too many attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            'retry_after' => $seconds
        ], 429);
    }

   try {
    // 2. Validation (Updated signature to 'file')
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
        'zip'        => 'required|string',
        'drivers_license' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        'resume_file'     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        'terms_accepted'  => 'required',
        'signature'       => 'required|file|mimes:png,jpg,jpeg|max:2048' // Validating as file
    ]);

    // 3. Handle File Uploads
    $licensePath = $request->file('drivers_license')->store('licenses', 'cloudinary');

    $resumePath = $request->hasFile('resume_file') 
        ? $request->file('resume_file')->store('resumes', 'cloudinary') 
        : null;

    // Upload the signature file to Cloudinary
    $signaturePath = $request->file('signature')->store('signatures', 'cloudinary');

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
        'signature'      => $signaturePath, // Now stores a URL instead of a long base64 string
        'terms_accepted' => $request->terms_accepted == '1', 
    ]);

    RateLimiter::clear($key);

    return response()->json([
        'message' => "Application submitted successfully",
        'id'      => $application->id
    ], 201);

    } catch (ValidationException $e) {
        RateLimiter::hit($key, 3600);
        throw $e; 
    } catch (\Exception $e) {
        RateLimiter::hit($key, 3600);
        return response()->json([
            // LOG the error message so you can see it in the console during testing
            'message' => "Submission error: " . $e->getMessage(),
            'attempts_remaining' => RateLimiter::remaining($key, 3)
        ], 500);
    }
}
}