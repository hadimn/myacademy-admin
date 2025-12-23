<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f9fafb; padding:20px">

    <h2>Hello {{ $name }}!</h2>

    <p>Please use the following code to verify your account:</p>

    <div style="
        background-color: #f7f7f7;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        margin: 20px 0;
    ">
        <strong style="
            font-size: 32px;
            letter-spacing: 5px;
            color: #1f2937;
        ">
            {{ $otp }}
        </strong>
    </div>

    <p>This code will expire soon.</p>

    <p>Regards,<br>Laravel</p>

</body>
</html>
