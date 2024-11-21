<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Assignment Notification</title>
</head>
<body>
    <h3>You have a new assignment:</h3>
    <p><strong>Plate Number:</strong> {{ $assignment->plate_number }}</p>
    <p><strong>Customer Name:</strong> {{ $assignment->customername }}</p>
    <p><strong>Location:</strong> {{ $assignment->location }}</p>
    <p><strong>Assignment Type:</strong> {{ $assignment->case_reported }}</p>
    <p><strong>Assigned By:</strong> {{ $assignment->assigned_by }}</p>
    <p><strong>Status:</strong> {{ $assignment->status ?? 'pending' }}</p>
</body>
</html>
