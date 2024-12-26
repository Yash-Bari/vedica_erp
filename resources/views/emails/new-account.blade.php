<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Vedica ERP</title>
</head>
<body>
    <h1>Welcome to Vedica ERP, {{ $name }}!</h1>
    
    <p>Your account has been successfully created. Here are your login details:</p>
    
    <ul>
        <li><strong>Username:</strong> {{ $username }}</li>
        <li><strong>Temporary Password:</strong> {{ $temporaryPassword }}</li>
    </ul>
    
    <p>Please log in and change your password immediately.</p>
    
    <p>Best regards,<br>Vedica ERP Team</p>
</body>
</html>
