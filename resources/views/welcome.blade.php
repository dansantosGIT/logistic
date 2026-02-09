<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CDRMMD LOGISTICS</title>
    <!-- Favicon: using single PNG at /images/favi.png -->
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <style>
        html,body{height:100%}
        body{margin:0;font-family:Inter,system-ui,Segoe UI,Roboto,Arial;background-color:#111;color:#fff}
        /* Background image: place your image at public/images/welcome-bg.jpg */
        .bg{
            position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6);z-index:-2
        }
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.35),rgba(2,6,23,0.45));z-index:-1}
        .center{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{max-width:900px;text-align:center}
        h1{font-size:2rem;margin:0 0 18px;font-weight:700}
        .btns{display:flex;gap:12px;justify-content:center}
        a.btn{padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:600}
        a.login{background:transparent;border:2px solid rgba(255,255,255,0.9);color:white}
        a.signup{background:#10b981;color:white;border:2px solid #10b981}
        @media(min-width:640px){h1{font-size:2.5rem}}
        .stats{display:flex;gap:10px;justify-content:center;margin-top:16px}
        .stat{background:rgba(255,255,255,0.06);padding:10px 12px;border-radius:8px;color:#fff;min-width:120px;text-align:center}
        .stat .num{font-weight:800;font-size:18px}
        .stat .label{font-size:12px;color:rgba(255,255,255,0.8);margin-top:4px}

        /* toast */
        .toast{position:fixed;right:20px;top:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 12px 30px rgba(2,6,23,0.12);opacity:0;transform:translateY(-8px);transition:opacity .18s,transform .18s;z-index:220}
        .toast.show{opacity:1;transform:translateY(0)}
        .toast .close{margin-left:12px;cursor:pointer;color:rgba(255,255,255,0.95)}
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <main class="center">
        <div class="card">
            <h1>San Juan CDRMMD - Logistics</h1>
            <div class="btns" style="margin-top:8px">
                <a href="/login" class="btn login">Log in</a>
                <a href="/register" class="btn signup">Sign up</a>
            </div>

            <!-- simplified welcome: keep calls-to-action only -->
        </div>
    </main>

        <script>
            (function(){
                const toast = document.getElementById('welcome-toast');
                if(toast){
                    setTimeout(()=> toast.classList.add('show'), 60);
                    const t = setTimeout(()=> toast.classList.remove('show'), 4200);
                    const closer = document.getElementById('welcome-toast-close');
                    closer && closer.addEventListener('click', ()=>{ clearTimeout(t); toast.classList.remove('show'); });
                }
            })();
        </script>

    </body>
    </html>