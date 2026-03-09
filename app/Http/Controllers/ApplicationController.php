<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
  public function store(Request $request)
{
  $key = 'application-submission:' . $request->ip();

    // 1. Check if they are already locked out
    if (RateLimiter::tooManyAttempts($key, 3)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'message' => "Too many attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            'retry_after' => $seconds
        ], 429);
    }

    try {
        // 2. Validation
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
            'drivers_license' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'resume_file'     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'terms_accepted'  => 'required',
            'signature'       => 'required|string' 
        ]);

        // 3. Handle File Uploads
       $licensePath = $request->file('drivers_license')->store('licenses', 'cloudinary');

     $resumePath = $request->hasFile('resume_file') 
    ? $request->file('resume_file')->store('resumes', 'cloudinary') 
    : null;

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
            'signature'      => $request->signature, 
            'terms_accepted' => $request->terms_accepted == '1', 
        ]);

        // SUCCESS: Clear the attempts so they can apply for other roles if needed
        RateLimiter::clear($key);

        return response()->json([
            'message' => "Application submitted successfully",
            'id'      => $application->id
        ], 201);

    } catch (ValidationException $e) {
        // If validation fails, it counts as an attempt
        RateLimiter::hit($key, 3600); // 1-hour lockout
        throw $e; // Laravel automatically returns the 422 error for you
    } catch (\Exception $e) {
        // If a database or file error occurs, it counts as an attempt
        RateLimiter::hit($key, 3600);
        return response()->json([
            'message' => "An error occurred during submission. Attempts remaining: " . RateLimiter::remaining($key, 3)
        ], 500);
}
}
}