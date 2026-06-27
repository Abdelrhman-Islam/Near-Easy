<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'message' => 'required|string'
        ]);

        $isStudent = User::where('email', $validated['email'])->exists();

        if ($isStudent) {
            Mail::mailer('support')
                ->raw("New Support Ticket:\n\n" . 
                      "Name: {$validated['name']}\n" . 
                      "Email: {$validated['email']}\n\n" . 
                      "Message:\n{$validated['message']}", function ($message) use ($validated) {
                
                $message->to('support@nearandeasy.com')
                        ->from('support@nearandeasy.com', 'Near&Easy Technical Support')
                        ->subject('New Support Ticket from ' . $validated['name']) 
                        ->replyTo($validated['email'], $validated['name']);
            });
        } else {
            Mail::mailer('contact')
                ->raw("New Contact Message:\n\n" . 
                      "Name: {$validated['name']}\n" . 
                      "Email: {$validated['email']}\n\n" . 
                      "Message:\n{$validated['message']}", function ($message) use ($validated) {
                
                $message->to('contact@nearandeasy.com')
                        ->from('contact@nearandeasy.com', 'Near&Easy Contact')
                        ->subject('New Contact Message from ' . $validated['name'])
                        ->replyTo($validated['email'], $validated['name']);
            });
        }

        return response()->json(['success' => true, 'message' => 'Message processed successfully!']);
    }
}