@component('mail::message')
<div style="text-align: center;">
    <img src="https://bluemoji.io/cdn-proxy/646218c67da47160c64a84d5/64faef18be339014130cb880_94.png"
         alt="Logo"
         style="width: 100px; height: auto; margin-bottom: 20px; display: inline-block;">
</div>

Hi {{ $user->name }},

Thanks for registering. Please confirm your email address by clicking the button below.

@component('mail::button', ['url' => $url])
Verify Email Address
@endcomponent

If you did not create an account, you can safely ignore this email.

Rewards Team, {{ config('app.name') }}
@endcomponent
