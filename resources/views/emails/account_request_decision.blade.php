<div style="font-family:Inter,system-ui,Arial,Helvetica;color:#0f172a">
    <h2>Account request {{ ucfirst($decision) }}</h2>
    <p>Hi {{ $request->name }},</p>
    @if($decision === 'approved')
        <p>Your account request has been approved. You can now sign in using your email.</p>
        @if($generated_password)
            <p><strong>Temporary password:</strong> {{ $generated_password }}</p>
        @endif
    @else
        <p>We're sorry — your account request has been denied.</p>
    @endif

    @if($admin_note)
        <h4>Admin note</h4>
        <p style="white-space:pre-wrap">{{ $admin_note }}</p>
    @endif

    <p>If you have questions, please contact the administrator.</p>
    <p>Regards,<br>San Juan CDRMMD</p>
</div>
