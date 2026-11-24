<x-site-layout title="Register">
    <section class="auth-panel">
        <p class="eyebrow">Join the circle</p>
        <h1>Have a story to tell?</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <x-form-textinput name="name" label="Name" autocomplete="name" :required="true" autofocus />
            <x-form-textinput name="email" label="Email" type="email" autocomplete="username" :required="true" />
            <x-form-textinput name="password" label="Password" type="password" autocomplete="new-password" :required="true" minlength="8" />
            <x-form-textinput name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" :required="true" minlength="8" />
            <button type="submit">Create account</button>
        </form>
        <p>Already have an account? <a href="{{ route('login') }}">Log in</a>.</p>
    </section>
</x-site-layout>
