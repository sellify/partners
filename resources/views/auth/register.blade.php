@extends('vendor.nova.auth.layout')

@section('content')

    @include('vendor.nova.auth.partials.header')

    <form
            class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
            method="POST"
            action="{{ route('register') }}"
    >
        {{ csrf_field() }}

        @component('vendor.nova.auth.partials.heading')
            {{ __('Create an account') }}
        @endcomponent

        @if ($errors->any())
            @foreach ($errors->all() as $error)
            <p class="text-center font-semibold text-danger my-3">
                {{ $error }}
            </p>
            @endforeach
        @endif

        <div class="mb-6 {{ $errors->has('name') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="name">{{ __('Your Name') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="name" type="text" name="name"
                   value="{{ old('name') }}" required>
        </div>

        <div class="mb-6 {{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="email">{{ __('Email Address') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-6 {{ $errors->has('username') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="username">{{ __('Username') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="username" type="text" name="username"
                   value="{{ old('username') }}" required>
        </div>

        <div class="mb-6 {{ $errors->has('paypal_email') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="paypal_email">{{ __('PayPal Email') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="paypal_email" type="email" name="paypal_email"
                   value="{{ old('paypal_email') }}" required>
        </div>

        <div class="mb-6 {{ $errors->has('password') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="password">{{ __('Password') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="password" type="password" name="password" required>
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="password">{{ __('Confirm Password') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="password" type="password" name="password_confirmation" required>
        </div>

        <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
            {{ __('Register') }}
        </button>

        <div class="flex mt-4">
            <div class="ml-auto">
                <a class="text-primary dim font-bold no-underline" href="{{ route('nova.login') }}">
                    {{ __('Already have an account?') }}
                </a>
            </div>
        </div>
    </form>
@endsection
