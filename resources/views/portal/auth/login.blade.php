<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reward Portal Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar: #0d1b2a;
            --white: #ffffff;
            --black: #000000;
            --muted: #6b7280;
            --border: #eeeeee;
            --shadow: 0 18px 45px rgba(0, 0, 0, 0.16);
        }

        * {
            box-sizing: border-box;
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--sidebar);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--black);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: 22px;
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .login-logo {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: var(--sidebar);
            color: var(--white);
            display: grid;
            place-items: center;
            font-size: 22px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: var(--black);
        }

        p {
            color: var(--muted);
            margin-top: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: var(--black);
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid #dcdcdc;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: var(--sidebar);
            box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.12);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            color: var(--muted);
        }

        .remember input {
            width: auto;
            height: auto;
        }

        .btn {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: var(--sidebar);
            color: var(--white);
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #08111d;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-logo">🎁</div>

    <h1>Reward Portal</h1>
    <p>Login with your email and password to manage your portal account.</p>

    <form method="POST" action="{{ route('portal.login.submit') }}">
        @csrf

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <label class="remember">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>