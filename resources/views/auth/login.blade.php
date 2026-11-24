<x-site-layout title="Log in">
    <section class="auth-panel">
        <p class="eyebrow">Welcome back</p>
        <h1>Return to the stories.</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <x-form-textinput name="email" label="Email" type="email" autocomplete="username" :required="true" autofocus />
            <x-form-textinput name="password" label="Password" type="password" autocomplete="current-password" :required="true" />
            <label class="checkbox"><input type="checkbox" name="remember"> Remember me</label>
            <button type="submit">Log in</button>
        </form>
        <p>New here? <a href="{{ route('register') }}">Create an account</a>.</p>
    </section>
</x-site-layout>
