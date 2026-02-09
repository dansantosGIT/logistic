<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up — San Juan CDRMMD</title>
    <!-- Favicon -->
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <style>
        html,body{height:100%}
        body{font-family:Inter,system-ui,Arial,Helvetica;margin:0;color:#111}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6);z-index:-2}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(255,255,255,0.06),rgba(2,6,23,0.25));z-index:-1}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{width:100%;max-width:480px;background:rgba(255,255,255,0.95);padding:28px;border-radius:12px;box-shadow:0 6px 24px rgba(15,23,42,0.08)}
        h2{margin:0 0 12px;font-size:20px}
        label{display:block;font-size:13px;margin-top:12px}
        input{width:100%;padding:10px;margin-top:6px;border-radius:6px;border:1px solid #e5e7eb}
        button{margin-top:16px;width:100%;padding:10px;border-radius:8px;border:none;background:#10b981;color:white;font-weight:700}
        .links{display:flex;justify-content:space-between;margin-top:12px}
        a{color:#2563eb;text-decoration:none}
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="wrap">
        <div class="card">
            <h2>Create account</h2>
            @if($errors->any())
                <div style="color:#b91c1c;margin-bottom:12px">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="/register">
                @csrf
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}">

                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}">

                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>

                <button type="submit">Sign up</button>
            </form>
            <div class="links">
                <a href="/login">Log in</a>
                <a href="/">Back</a>
            </div>
        </div>
    </div>
</body>
</html>
