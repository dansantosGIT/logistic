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
        *{box-sizing:border-box}
        body{font-family:Inter,system-ui,Arial,Helvetica;margin:0;color:#111;background:#071024}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.45);z-index:-2}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.45),rgba(2,6,23,0.75));z-index:-1}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{width:100%;max-width:720px;background:linear-gradient(180deg,#ffffff, #fbfbfd);padding:28px;border-radius:12px;box-shadow:0 10px 40px rgba(2,6,23,0.45);border:1px solid rgba(2,6,23,0.04)}
        h2{margin:0 0 12px;font-size:22px;color:#063350}
        .subtitle{color:#374151;font-size:13px;margin-bottom:10px}
        label{display:block;font-size:13px;margin-top:12px;color:#0f172a}
        .req{color:#b91c1c;margin-left:6px}
        input, select, .file-input{width:100%;padding:12px;margin-top:6px;border-radius:8px;border:1px solid #e6eef5;background:#fff}
        input[type=file]{padding:6px}
        .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start}
        .field{margin-top:12px;min-width:0}
        button{margin-top:18px;width:100%;padding:12px;border-radius:10px;border:none;background:#0ea5a0;color:white;font-weight:700;box-shadow:0 6px 18px rgba(14,165,160,0.12)}
        .links{display:flex;justify-content:space-between;margin-top:12px}
        a{color:#0f62fe;text-decoration:none}
        @media (max-width:720px){
            .card{padding:18px}
            .two-col{grid-template-columns:1fr}
        }
        .radio-center{display:flex;gap:28px;justify-content:center;align-items:flex-start;padding-top:6px}
        .role-option{display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;padding:6px 8px;border-radius:8px}
        .role-option input{width:22px;height:22px}
        .role-text{font-size:15px;color:#0f172a}
        input[type=file]{display:block}
        .links{margin-top:18px}
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
            @if(session('success'))
                <div id="success-msg" style="background:#ecfccb;padding:12px;border-radius:8px;margin-bottom:12px">{{ session('success') }}</div>
            @endif
            <form method="POST" action="/register" enctype="multipart/form-data" novalidate>
                @csrf
                <p class="subtitle">Please fill in the details below to request an account. Fields marked with <span class="req">*</span> are required.</p>

                <div class="two-col">
                    <div class="field">
                        <label for="name">Full name <span class="req">*</span></label>
                        <input id="name" name="name" type="text" required aria-required="true" value="{{ old('name') }}">
                    </div>

                    <div class="field">
                        <label for="phone">Phone <span class="req">*</span></label>
                        <input id="phone" name="phone" type="text" required aria-required="true" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="two-col">
                    <div class="field">
                        <label for="department">Department <span class="req">*</span></label>
                        <select id="department" name="department" required aria-required="true">
                            <option value="">Select department</option>
                            <option value="CEDOC" {{ old('department')=='CEDOC' ? 'selected' : '' }}>CEDOC</option>
                            <option value="Admin & Training" {{ old('department')=='Admin & Training' ? 'selected' : '' }}>Admin & Training</option>
                            <option value="Research and Planning" {{ old('department')=='Research and Planning' ? 'selected' : '' }}>Research and Planning</option>
                            <option value="Operations" {{ old('department')=='Operations' ? 'selected' : '' }}>Operations</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Account type <span class="req">*</span></label>
                        <div class="radio-center" role="radiogroup" aria-required="true">
                            <label class="role-option">
                                <input type="radio" name="role" value="requestor" {{ old('role','requestor')=='requestor' ? 'checked' : '' }}>
                                <div class="role-text">Requestor</div>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="role" value="admin" {{ old('role')=='admin' ? 'checked' : '' }}>
                                <div class="role-text">Admin</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="ops-role-section" class="field" style="margin-top:12px; display: none;">
                    <label>Operations sub-role <span class="req">*</span></label>
                    <div class="radio-center" role="radiogroup" aria-required="false" style="justify-content:center;">
                        <label class="role-option" style="flex-direction:row;gap:12px">
                            <input type="radio" name="ops_role" value="Alpha" {{ old('ops_role')=='Alpha' ? 'checked' : '' }} {{ old('department')!='Operations' ? 'disabled' : '' }}>
                            <div class="role-text">Alpha</div>
                        </label>
                        <label class="role-option" style="flex-direction:row;gap:12px">
                            <input type="radio" name="ops_role" value="Bravo" {{ old('ops_role')=='Bravo' ? 'checked' : '' }} {{ old('department')!='Operations' ? 'disabled' : '' }}>
                            <div class="role-text">Bravo</div>
                        </label>
                        <label class="role-option" style="flex-direction:row;gap:12px">
                            <input type="radio" name="ops_role" value="Charlie" {{ old('ops_role')=='Charlie' ? 'checked' : '' }} {{ old('department')!='Operations' ? 'disabled' : '' }}>
                            <div class="role-text">Charlie</div>
                        </label>
                    </div>
                </div>

                <div class="field">
                    <label for="avatar">Profile photo <small style="color:#6b7280">(optional, JPG/PNG, max 2MB)</small></label>
                    <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg" class="file-input">
                </div>

                <div class="field">
                    <label for="email">Email <span class="req">*</span></label>
                    <input id="email" name="email" type="email" required aria-required="true" value="{{ old('email') }}">
                </div>

                <div class="two-col">
                    <div class="field">
                        <label for="password">Password <span class="req">*</span></label>
                        <input id="password" name="password" type="password" required aria-required="true" maxlength="8">
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm password <span class="req">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required aria-required="true" maxlength="8">
                    </div>
                </div>

                <input type="hidden" name="website" value=""> {{-- honeypot field, should remain empty --}} 

                @if(env('RECAPTCHA_SITEKEY'))
                    <div style="margin-top:12px">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITEKEY') }}"></div>
                    </div>
                @endif

                <button type="submit" id="signup-btn" disabled>Sign up</button>
            </form>
            <div class="links">
                <a href="/login">Log in</a>
                <a href="/">Back</a>
            </div>
        </div>
    </div>
</body>
@if(env('RECAPTCHA_SITEKEY'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<script>
    // client-side validation: enable submit only when form valid and passwords match
    (function(){
        var form = document.querySelector('form');
        var signupBtn = document.getElementById('signup-btn');
        var pwd = document.getElementById('password');
        var pwdConfirm = document.getElementById('password_confirmation');
        var requiredSelectors = 'input[required], select[required], input[name="role"]';
        var dept = document.getElementById('department');
        var opsSection = document.getElementById('ops-role-section');

        function isOpsRoleSelected(){
            return !!form.querySelector('input[name="ops_role"]:checked');
        }

        function passwordsMatch(){
            return pwd.value === pwdConfirm.value && pwd.value.length > 0;
        }

        function isRoleSelected(){
            return !!form.querySelector('input[name="role"]:checked');
        }

        function validateForm(){
            // Use HTML5 validity for native required checks
            var valid = form.checkValidity();
            // ensure honeypot empty
            var honeypot = form.querySelector('input[name="website"]');
            if (honeypot && honeypot.value.trim() !== '') valid = false;
            // require passwords match
            if (!passwordsMatch()) valid = false;
            // require role selected
            if (!isRoleSelected()) valid = false;
            // if department is Operations, require ops sub-role
            try {
                if (dept && dept.value === 'Operations') {
                    if (!isOpsRoleSelected()) valid = false;
                }
            } catch(e){}
            signupBtn.disabled = !valid;
        }

        // attach listeners
        form.addEventListener('input', validateForm);
        form.addEventListener('change', validateForm);
        // show/hide ops-role when department changes
        if (dept){
            dept.addEventListener('change', function(){
                if (dept.value === 'Operations'){
                    if (opsSection) opsSection.style.display = '';
                    // enable inputs
                    (document.querySelectorAll('input[name="ops_role"]')||[]).forEach(i => i.disabled = false);
                } else {
                    if (opsSection) opsSection.style.display = 'none';
                    (document.querySelectorAll('input[name="ops_role"]')||[]).forEach(i => { i.checked = false; i.disabled = true; });
                }
                validateForm();
            });
            // initialize disabled state for ops_role inputs when page loads
            (document.querySelectorAll('input[name="ops_role"]')||[]).forEach(i => {
                if (dept.value !== 'Operations') i.disabled = true;
            });
        }
        // initial run
        validateForm();

        // show modal toast if success
        var s = document.getElementById('success-msg');
        if (s) {
            showModalToast(s.textContent || s.innerText || 'Registration successful — an admin will review your request.');
        }

        function showModalToast(message){
            var modal = document.createElement('div');
            modal.setAttribute('role','dialog');
            modal.setAttribute('aria-modal','true');
            modal.style.position = 'fixed';
            modal.style.left = '0';
            modal.style.top = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.background = 'rgba(2,6,23,0.5)';
            modal.style.zIndex = 9999;

            var box = document.createElement('div');
            box.style.background = '#fff';
            box.style.padding = '20px 24px';
            box.style.borderRadius = '10px';
            box.style.maxWidth = '480px';
            box.style.boxShadow = '0 10px 40px rgba(2,6,23,0.4)';
            box.style.textAlign = 'center';

            var h = document.createElement('div');
            h.style.fontSize = '16px';
            h.style.color = '#06202b';
            h.style.marginBottom = '8px';
            h.textContent = message;

            var note = document.createElement('div');
            note.style.fontSize = '13px';
            note.style.color = '#475569';
            note.style.marginBottom = '12px';
            note.textContent = 'Wait for an email with the outcome.';

            var btn = document.createElement('button');
            btn.textContent = 'Close';
            btn.style.padding = '8px 14px';
            btn.style.border = 'none';
            btn.style.borderRadius = '8px';
            btn.style.background = '#0ea5a0';
            btn.style.color = '#fff';
            btn.style.cursor = 'pointer';

            btn.addEventListener('click', function(){
                document.body.removeChild(modal);
            });

            box.appendChild(h);
            box.appendChild(note);
            box.appendChild(btn);
            modal.appendChild(box);
            document.body.appendChild(modal);
        }
    })();
</script>
</html>
