<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #1877F2, #0044cc);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-facebook {
            background: white;
            color: #1877F2;
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: 0.3s ease;
        }
        .btn-facebook:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome to Laravel Facebook Login</h1>
        <a href="{{ route('facebook.login') }}" class="btn-facebook">Login with Facebook</a>
    </div>
</body>
</html>
