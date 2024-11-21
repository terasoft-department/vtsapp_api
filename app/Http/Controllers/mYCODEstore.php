Future<void> _fetchAssignments() async {
    try {
      final token = await storage.read(key: 'token');

      if (token == null) {
        _showToast('Token not found');
        setState(() {
          isLoading = false;
        });
        return;
      }

      final response = await http.get(
        Uri.parse('http://147.79.101.245:8082/api/assignments'),
        headers: {
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final jsonResponse = json.decode(response.body);
        setState(() {
          assignments = jsonResponse['assignments'] ?? [];
          filteredAssignments = assignments;
          isLoading = false;
        });

        // Retrieve the logged-in user's email (stored in local storage)
        final String loggedInEmail = await storage.read(key: 'email') ?? '';

        // Iterate over the assignments to check for the matching 'user_email'
        for (var assignment in assignments) {
          if (assignment['user_email'] != null &&
              assignment['user_email'] == loggedInEmail) {
            // Send an email notification to the customer email if user_email matches
            _sendEmail(
              assignment['customer_email'], // Customer's email address
              'New Assignment Notification', // Subject
              'You have a new assignment: ${assignment['case_reported']} located at ${assignment['location']}', // Body
            );
          }
        }
      } else {
        _showToast('Error fetching assignments');
        setState(() {
          isLoading = false;
        });
      }
    } catch (error) {
      _showToast('An error occurred');
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _sendEmail(String recipient, String subject, String body) async {
    const String username = 'nyemamudhihir@gmail.com'; // Your Gmail address
    const String password = 'cvesfvevajxlcyfm'; // Your Gmail app password

    // Set up the Gmail SMTP server
    final smtpServer = gmail(username, password);

    // Create the email message using the mailer alias
    final message = mailer.Message()
      ..from =
          mailer.Address(username, 'Assignments App') // Sender's email and name
      ..recipients.add(recipient) // Recipient's email
      ..subject = subject // Subject of the email
      ..text = body; // Body of the email

    // Try sending the email
    try {
      final sendReport = await send(message, smtpServer); // Send the email
      print('Message sent: ${sendReport.toString()}');
    } on MailerException catch (e) {
      print('Message not sent: ${e.toString()}');
    }
  }




 public function index()
{
    try {
        // Retrieve assignments for the logged-in user where status is null
        $assignments = Assignment::with('customer', 'user') // Eager load the customer and user relationship
            ->where('user_id', Auth::id()) // Filter by the logged-in user's user_id
            ->whereNull('status') // Only include assignments where status is null
            ->orderBy('assignment_id', 'desc') // Order by assignment_id descending
            ->get();

        // Map the assignments to include the customer name and days passed since created_at
        $assignments = $assignments->map(function ($assignment) {
            // Calculate the days passed since the assignment was created
            $daysPassed = Carbon::parse($assignment->created_at)->diffInDays(Carbon::now());

            return [
                'assignment_id' => $assignment->assignment_id,
                'user_id' => $assignment->user_id,
                'status' => $assignment->status,
                'plate_number' => $assignment->plate_number,
                'customer_phone' => $assignment->customer_phone,
                'location' => $assignment->location,
                'case_reported' => $assignment->case_reported,
                'customer_debt' => $assignment->customer_debt,
                'assigned_by' => $assignment->assigned_by,
                'customername' => $assignment->customer->customername ?? 'N/A', // Get customer name, or 'N/A' if not available
                'created_at' => $assignment->created_at->format('m-d-Y'),
                'email' => $assignment->user->email ?? 'N/A',
                'days_passed' => $daysPassed, // Add the days passed field
            ];
        });

        // Return the assignments as JSON
        return response()->json([
            'status' => 'success',
            'assignments' => $assignments,
        ], 200);
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Error fetching assignments: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch assignments',
        ], 500);
    }
}
