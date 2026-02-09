<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — San Juan CDRMMD</title>
    <!-- Favicon -->
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <style>
        html,body{height:100%}
        body{font-family:Inter,system-ui,Arial,Helvetica;margin:0;color:#0f172a}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.55) saturate(0.95);z-index:-2}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.42));z-index:-1}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{width:100%;max-width:420px;background:linear-gradient(180deg,rgba(255,255,255,0.98),rgba(250,250,250,0.94));padding:32px;border-radius:14px;box-shadow:0 12px 40px rgba(2,6,23,0.18);border:1px solid rgba(15,23,42,0.04)}
        .brand-row{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .brand-logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:18px}
        h2{margin:6px 0 10px;font-size:20px}
        .subtitle{font-size:13px;color:#475569;margin-bottom:14px}
        label{display:block;font-size:13px;margin-top:12px;color:#334155}
        input{width:100%;padding:12px 12px;margin-top:6px;border-radius:10px;border:1px solid #e6eef8;background:#fff;color:#0f172a;outline:none;box-shadow:inset 0 -1px 0 rgba(2,6,23,0.02)}
        input:focus{border-color:#7c3aed;box-shadow:0 4px 18px rgba(124,58,237,0.12)}
        .remember-row{display:flex;align-items:center;gap:8px;margin-top:10px}
        .remember-row input[type="checkbox"]{width:16px;height:16px}
        .btn-primary{margin-top:16px;width:100%;padding:12px;border-radius:10px;border:none;background:linear-gradient(90deg,#2563eb,#7c3aed);color:white;font-weight:700;cursor:pointer;box-shadow:0 8px 20px rgba(37,99,235,0.18)}
        .btn-primary:hover{transform:translateY(-1px)}
        .links{display:flex;justify-content:space-between;margin-top:12px;font-size:14px}
        .links a{color:#2563eb;text-decoration:none}
        .forgot{color:#6b7280;font-size:13px}
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="wrap">
        <div class="card">
            <div class="brand-row">
                <div class="brand-logo">SJ</div>
                <div>
                    <h2>Log in</h2>
                    <div class="subtitle">Access the San Juan CDRMMD inventory system</div>
                </div>
            </div>
            @if($errors->any())
                <div style="color:#b91c1c;margin-bottom:12px">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="/login">
                @csrf
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}">

                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>

                <div class="remember-row">
                    <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" style="font-size:13px;color:#374151">Remember me</label>
                    <div style="flex:1"></div>
                    <a href="#" class="forgot">Forgot password?</a>
                </div>

                <button class="btn-primary" type="submit">Log in</button>
            </form>
            <div class="links">
                <a href="/register">Sign up</a>
                <a href="/">Back</a>
            </div>
        </div>
    </div>
</body>
</html>
