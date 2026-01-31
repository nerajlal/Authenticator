<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account - Biometric Authentication</title>
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@12.0.0/build/esm/styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            background: #f6f6f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-container {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 24px;
            font-weight: 600;
            color: #202223;
        }
        .register-header p {
            margin: 0;
            font-size: 14px;
            color: #6d7175;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 13px;
            font-weight: 500;
            color: #202223;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #5c6ac4;
            box-shadow: 0 0 0 3px rgba(92, 106, 196, 0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #5c6ac4 0%, #4959bd 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(92, 106, 196, 0.3);
        }
        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 13px;
            color: #6d7175;
        }
        .form-footer a {
            color: #5c6ac4;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 13px;
        }
        .alert-error {
            background: #fbeae5;
            color: #d72c0d;
            border: 1px solid #f1c8ba;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join us today</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.register.submit') }}">
            @csrf
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    autofocus
                    value="{{ old('name') }}"
                    placeholder="John Doe"
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="At least 8 characters"
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    placeholder="Re-enter your password"
                >
            </div>

            <button type="submit" class="btn-primary">
                Create Account
            </button>
        </form>

        <div class="form-footer">
            <p>Already have an account? <a href="{{ route('customer.login') }}">Sign in</a></p>
        </div>
    </div>

    <!-- Load biometric login script -->
    <script src="{{ asset('js/biometric-login.js') }}"></script>
</body>
</html>
