<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'message' => 'required|string'
        ]);

        Mail::raw("You have received a new contact message:\n\n" . 
                  "Name: {$validated['name']}\n" . 
                  "Email: {$validated['email']}\n\n" . 
                  "Message:\n{$validated['message']}", function ($message) use ($validated) {
            $message->to('contact@nearandeasy.com') 
                    ->subject('New Contact Message from ' . $validated['name'])
                    ->replyTo($validated['email'], $validated['name']);
        });

        return response()->json(['success' => true, 'message' => 'Message sent successfully!']);
    }
}