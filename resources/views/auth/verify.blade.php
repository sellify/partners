@extends('vendor.nova.auth.layout')

@section('content')

    @include('vendor.nova.auth.partials.header')
<div class="bg-white shadow rounded-lg p-8 max-w-login mx-auto">

    @component('vendor.nova.auth.partials.heading')
        {{ __('Verify Your Email Address') }}
    @endcomponent

        @if (session('resent'))
        <p class="text-center font-semibold text-success my-3">
            {{ __('A fresh verification link has been sent to your email address.') }}
        </p>
        @endif

        <p class="my-6 font-semibold text-center leading-normal">
            {{ __('Before proceeding, please check your email for a verification link.') }}
            {{ __('If you did not receive the email') }},

            <a class="text-primary dim font-bold no-underline" href="{{ route('verification.resend') }}">{{ __('click here to request another') }}</a>


        </p>
</div>
@endsection
