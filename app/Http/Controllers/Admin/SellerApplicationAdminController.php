<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SellerApplicationStatusMail;
use App\Models\User;
use App\Models\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SellerApplicationAdminController extends Controller
{
    public function index()
    {
        $applications = SellerApplication::latest()->paginate(20);
        $title = 'Seller Applications';

        return view('admin.seller-applications.index', compact('applications', 'title'));
    }

    public function show(SellerApplication $sellerApplication)
    {
        $title = 'Seller Application Detail';
        return view('admin.seller-applications.show', [
            'application' => $sellerApplication,
            'title' => $title,
        ]);
    }

    public function approve(SellerApplication $sellerApplication)
    {
        if ($sellerApplication->status !== 'pending') {
            return back()->with('error', 'Application is already reviewed.');
        }

        $credentials = null;

        // Create or reuse user account for seller
        $user = User::where('email', $sellerApplication->email)->first();
        if (!$user) {
            $tempPassword = Str::random(12);
            $user = User::create([
                'name' => $sellerApplication->store_name ?: $sellerApplication->name,
                'email' => $sellerApplication->email,
                'password' => bcrypt($tempPassword),
            ]);
            $credentials = [
                'email' => $user->email,
                'password' => $tempPassword,
            ];
        }

        // Assign seller role if available
        if ($user && method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('seller');
            } catch (\Throwable $e) {
                // Ignore role assignment errors to not block approval
            }
        }

        // Send verification email automatically if not verified
        if ($user && method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                // Ignore send errors to avoid blocking approval flow
            }
        }

        $sellerApplication->update([
            'status' => 'approved',
            'approved_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        try {
            Mail::to($sellerApplication->email)->send(
                new SellerApplicationStatusMail($sellerApplication, 'approved', null, $credentials)
            );
        } catch (\Throwable $e) {
            // Log but do not block admin flow
        }

        return back()->with('success', 'Application approved and email sent to applicant.');
    }

    public function reject(SellerApplication $sellerApplication, Request $request)
    {
        if ($sellerApplication->status !== 'pending') {
            return back()->with('error', 'Application is already reviewed.');
        }

        $message = $request->input('reason');

        $sellerApplication->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        try {
            Mail::to($sellerApplication->email)->send(
                new SellerApplicationStatusMail($sellerApplication, 'rejected', $message)
            );
        } catch (\Throwable $e) {
            // Log but do not block admin flow
        }

        return back()->with('success', 'Application rejected and email sent to applicant.');
    }
}
