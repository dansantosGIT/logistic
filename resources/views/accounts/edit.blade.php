@extends('layouts.app')

@section('content')
<div style="max-width:820px;margin:48px auto;padding:18px;background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(2,6,23,0.06)">
    <h2 style="margin:0 0 12px;font-weight:800">Edit Account</h2>
    <form method="POST" action="/accounts/{{ $user->id }}" enctype="application/x-www-form-urlencoded">
        @csrf
        @method('PATCH')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <label class="block">Name</label>
                <input name="name" value="{{ old('name', $user->name) }}" required style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px">
            </div>
            <div>
                <label class="block">Email</label>
                <input name="email" value="{{ $user->email }}" disabled style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px;background:#f8fafc">
            </div>
            <div>
                <label class="block">Department</label>
                <select id="department" name="department" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                    <option value="">Select department</option>
                    <option value="CEDOC" {{ old('department', $user->department) == 'CEDOC' ? 'selected' : '' }}>CEDOC</option>
                    <option value="Admin & Training" {{ old('department', $user->department) == 'Admin & Training' ? 'selected' : '' }}>Admin & Training</option>
                    <option value="Research and Planning" {{ old('department', $user->department) == 'Research and Planning' ? 'selected' : '' }}>Research and Planning</option>
                    <option value="Operations" {{ old('department', $user->department) == 'Operations' ? 'selected' : '' }}>Operations</option>
                </select>
            </div>
            <div>
                <label class="block">Role</label>
                <select id="role" name="role" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                    <option value="requestor" {{ old('role', $user->role) == 'requestor' ? 'selected' : '' }}>Requestor</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>

        <div id="ops-role-wrapper" style="margin-top:12px; display: none;">
            <label class="block" for="ops_role" style="font-weight:700;margin:0">Operations sub-role</label>
            <select id="ops_role" name="ops_role" style="padding:8px;border:1px solid #e6e9ef;border-radius:8px;margin-top:8px">
                <option value="Alpha" {{ old('ops_role', $user->ops_role) === 'Alpha' ? 'selected' : '' }}>Alpha</option>
                <option value="Bravo" {{ old('ops_role', $user->ops_role) === 'Bravo' ? 'selected' : '' }}>Bravo</option>
                <option value="Charlie" {{ old('ops_role', $user->ops_role) === 'Charlie' ? 'selected' : '' }}>Charlie</option>
            </select>
        </div>

<script>
    (function(){
        var dept = document.getElementById('department');
        var ops = document.getElementById('ops_role');
        var opsWrapper = document.getElementById('ops-role-wrapper');
        function updateOpsVisibility(){
            if(!dept || !ops || !opsWrapper) return;
            if(dept.value === 'Operations'){
                ops.disabled = false;
                opsWrapper.style.display = '';
            } else {
                ops.disabled = true;
                ops.value = '';
                opsWrapper.style.display = 'none';
            }
        }
        if(dept){ dept.addEventListener('change', updateOpsVisibility); }
        // initialize
        updateOpsVisibility();
    })();
</script>

        <div style="margin-top:12px;display:flex;align-items:center;gap:12px">
            <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="active" value="1" {{ $user->active ? 'checked' : '' }}> Active</label>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:18px;gap:8px">
            <a href="/accounts?tab=accounts" class="btn" style="background:linear-gradient(90deg,#f8fafc,#eef2ff);color:#0f172a;padding:8px 12px;border-radius:8px;border:1px solid rgba(15,23,42,0.06);box-shadow:0 6px 18px rgba(2,6,23,0.04)">Cancel</a>
            <button class="btn primary" style="padding:8px 14px;border-radius:8px;background:linear-gradient(90deg,#7c3aed,#a78bfa);color:#fff;border:none;box-shadow:0 10px 30px rgba(124,58,237,0.12);cursor:pointer">Save</button>
        </div>
    </form>
</div>
@endsection
