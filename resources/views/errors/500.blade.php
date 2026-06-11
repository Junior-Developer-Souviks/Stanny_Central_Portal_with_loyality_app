<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            height: 100vh;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-box {
            text-align: center;
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }

        .error-code {
            font-size: 100px;
            font-weight: bold;
            color: #dc3545;
        }

        h1 {
            margin: 15px 0;
            color: #333;
            font-size: 28px;
        }

        p {
            color: #777;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: #007bff;
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            transition: .3s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="error-box">

    <div class="icon">
        ⚠️
    </div>

    <div class="error-code">
        500
    </div>

    <h1>Internal Server Error</h1>

    <p>
        Something went wrong on our server.

    </p>

    <a href="/" class="btn">
        Go To Homepage
    </a>

</div>

</body>
</html>