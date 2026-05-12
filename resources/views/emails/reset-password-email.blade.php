@component('mail::message')
<div style="text-align: center;">
    <img src="https://bluemoji.io/cdn-proxy/646218c67da47160c64a84d5/64faef18be339014130cb880_94.png"
         alt="Logo"
         style="width: 100px; height: auto; margin-bottom: 20px; display: inline-block;">
</div>

Hi {{ $user->name }},

Click the button below to reset your password.

@component('mail::button', ['url' => url('/api/auth/reset-password?token=' . $token . '&email=' . $user->email)])
Reset Password
@endcomponent

If you didn't request this, you can safely ignore this email.

Rewards Team, {{ config('app.name') }}
@endcomponent