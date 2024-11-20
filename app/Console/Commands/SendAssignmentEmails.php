<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\AssignmentController;
use Illuminate\Support\Facades\Auth;

class SendAssignmentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-assignment-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for the latest assignments to all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve all users
        $users = User::all();

        foreach ($users as $user) {
            try {
                // Temporarily authenticate as the user
                Auth::login($user);

                // Call the function to send the latest assignment email
                $controller = new AssignmentController();
                $controller->sendLatestAssignmentEmail();

                // Log success
                $this->info('Assignment email sent successfully to user: ' . $user->email);

                // Log out after sending
                Auth::logout();
            } catch (\Exception $e) {
                // Log the error for this user
                $this->error('Failed to send email to user: ' . $user->email . ' | Error: ' . $e->getMessage());
            }
        }
    }
}
