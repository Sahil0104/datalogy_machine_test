@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="auth-wrap">
        <div class="card auth-card">
            <div class="auth-side">
                <span class="eyebrow">Laravel Demo</span>
                <h1>Welcome back to your user management dashboard.</h1>
                <p>Login with your email and password to access the protected dashboard, user count, and AJAX-powered user list.</p>

                <div class="feature-list">
                    <div class="feature-item">Secure session-based login with database credential check.</div>
                    <div class="feature-item">Redirect protection for guest and authenticated users.</div>
                    <div class="feature-item">AJAX DataTable with add, edit, and delete support.</div>
                </div>
            </div>

            <div class="auth-form">
                <div class="title-block">
                    <h2>Login</h2>
                    <p>Enter your account details to continue.</p>
                </div>

                @if (session('success'))
                    <div class="flash success">{{ session('success') }}</div>
                @endif

                @if ($errors->has('login'))
                    <div class="flash error">{{ $errors->first('login') }}</div>
                @endif

                <form id="loginForm" method="POST" action="{{ route('login.submit') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com">
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password">
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                    </div>
                </form>

                <div class="auth-footer">
                    Need an account? <a href="{{ route('register') }}">Create one here</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#loginForm').validate({
            rules: {
                email: { required: true, email: true },
                password: { required: true }
            },
            messages: {
                email: {
                    required: 'Email is required.',
                    email: 'Enter a valid email address.'
                },
                password: { required: 'Password is required.' }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            }
        });
    </script>
@endpush
