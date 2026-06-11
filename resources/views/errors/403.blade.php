<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 20px;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h1 {
            font-size: 100px;
            color: #d9534f;
        }
        h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #333;
        }
        p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        .error-details {
            font-size: 16px;
            color: #999;
            margin-bottom: 20px;
        }
        a, button {
            display: inline-block;
            margin: 10px;
            padding: 12px 25px;
            font-size: 16px;
            color: #fff;
            background: #344767;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
        }
        a:hover, button:hover {
            background: #2c3e50;
        }
        .icon {
            font-size: 50px;
            color: #d9534f;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>403</h1>
        <h2>Access Denied</h2>
        <p>Sorry, you don't have permission to access this page.</p>
        <p class="error-details">If you believe this is an error, please contact your administrator.</p>
        <button onclick="history.back()">🔙 Go Back</button>
        <a href="{{route('admin.dashboard')}}">🏠 Return to Homepage</a>
    </div>
</body>
</html>
