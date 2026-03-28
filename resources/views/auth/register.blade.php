@extends('layouts.app', ['title' => 'Registration'])

@section('content')
    <div class="auth-wrap">
        <div class="card auth-card">
            <div class="auth-side">
                <span class="eyebrow">Create Account</span>
                <h1>Register a new user and jump straight into the dashboard.</h1>
                <p>This form includes jQuery validation on the frontend and Laravel validation on the backend, including unique email checks and password confirmation.</p>

                <div class="feature-list">
                    <div class="feature-item">First name, last name, email, password, and re-password fields.</div>
                    <div class="feature-item">Server-side validation messages shown directly on the page.</div>
                    <div class="feature-item">Successful signup saves the data and redirects to dashboard.</div>
                </div>
            </div>

            <div class="auth-form">
                <div class="title-block">
                    <h2>Registration</h2>
                    <p>Fill in the details below to create an account.</p>
                </div>

                @if ($errors->any())
                    <div class="error-box">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="registerForm" method="POST" action="{{ route('register.submit') }}" novalidate>
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="John">
                            @error('first_name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Doe">
                            @error('last_name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group full">
                            <label for="reg_email">Email Address</label>
                            <input type="email" id="reg_email" name="email" value="{{ old('email') }}" placeholder="john@example.com">
                            @error('email')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="At least 6 characters">
                            @error('password')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Re-Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password">
                        </div>
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Register Now</button>
                    </div>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="{{ route('login') }}">Back to login</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#registerForm').validate({
            rules: {
                first_name: { required: true },
                last_name: { required: true },
                email: { required: true, email: true },
                password: { required: true, minlength: 6 },
                password_confirmation: { required: true, equalTo: '#password' }
            },
            messages: {
                first_name: 'First name is required.',
                last_name: 'Last name is required.',
                email: {
                    required: 'Email is required.',
                    email: 'Enter a valid email address.'
                },
                password: {
                    required: 'Password is required.',
                    minlength: 'Password must be at least 6 characters.'
                },
                password_confirmation: {
                    required: 'Re-Password is required.',
                    equalTo: 'Password and Re-Password must match.'
                }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            }
        });
    </script>
@endpush
