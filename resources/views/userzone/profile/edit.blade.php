<x-site-layout title="Account settings">
    <section class="form-page">
        <a class="back-link" href="{{ route('dashboard') }}">← My campfire</a><h1>Account settings</h1>
        <h2>Your details</h2>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <x-form-textinput name="name" label="Name" :value="$user->name" :required="true" maxlength="255" autocomplete="name" />
            <x-form-textinput name="email" label="Email" type="email" :value="$user->email" :required="true" maxlength="255" autocomplete="email" />
            <button>Save details</button>
        </form>
        <section class="account-section"><h2>Change password</h2>
            @foreach($errors->updatePassword->all() as $error)<p class="error">{{ $error }}</p>@endforeach
            <form method="POST" action="{{ route('password.update') }}">
                @csrf @method('PUT')
                <x-form-textinput name="current_password" label="Current password" type="password" :required="true" autocomplete="current-password" />
                <x-form-textinput name="password" label="New password" type="password" :required="true" autocomplete="new-password" />
                <x-form-textinput name="password_confirmation" label="Confirm new password" type="password" :required="true" autocomplete="new-password" />
                <button>Update password</button>
            </form>
        </section>
        <section class="account-section"><h2>Delete account</h2>
            <p>Deleting your account permanently removes your stories, including the trash, and your comments and votes. This cannot be undone.</p>
            <details @if($errors->userDeletion->any()) open @endif><summary>Delete my account</summary>
                @foreach($errors->userDeletion->all() as $error)<p class="error">{{ $error }}</p>@endforeach
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Permanently delete your account and its stories, comments and votes?');">
                    @csrf @method('DELETE')
                    <div class="field"><label for="delete-password">Confirm your password</label><input id="delete-password" type="password" name="password" autocomplete="current-password" required></div>
                    <button class="delete-button">Permanently delete account</button>
                </form>
            </details>
        </section>
    </section>
</x-site-layout>
