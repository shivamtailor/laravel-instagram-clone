@component('mail::message')
# Welcome to Instagram

This is a community of fellow developers and we glad that you joined us.

@component('mail::button', ['url' => ''])
All the best!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
