<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - {{ auth()->user()->name }}</title>
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@12.0.0/build/esm/styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            background: #f6f6f7;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #202223;
        }
        .dashboard-header p {
            margin: 0.5rem 0 0 0;
            font-size: 14px;
            color: #6d7175;
        }
        .btn-logout {
            padding: 0.75rem 1.5rem;
            background: white;
            color: #202223;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-logout:hover {
            background: #f6f6f7;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .card h2 {
            margin: 0 0 1rem 0;
            font-size: 18px;
            font-weight: 600;
            color: #202223;
        }
        .credential-item {
            padding: 1rem;
            background: #f6f6f7;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credential-info {
            flex: 1;
        }
        .credential-name {
            font-size: 14px;
            font-weight: 600;
            color: #202223;
            margin-bottom: 0.25rem;
        }
        .credential-date {
            font-size: 12px;
            color: #6d7175;
        }
        .btn-remove {
            padding: 0.5rem 1rem;
            background: #fbeae5;
            color: #d72c0d;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-remove:hover {
            background: #f1c8ba;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6d7175;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h1>Welcome, {{ auth()->user()->name }}!</h1>
                <p>{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>

        <div class="card">
            <h2>Biometric Credentials</h2>
            
            @php
                $credentials = auth()->user()->biometricCredentials;
            @endphp

            @if($credentials->count() > 0)
                @foreach($credentials as $credential)
                    <div class="credential-item">
                        <div class="credential-info">
                            <div class="credential-name">
                                {{ $credential->device_name ?? 'Biometric Device' }}
                            </div>
                            <div class="credential-date">
                                Registered on {{ $credential->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <form method="POST" action="/api/biometric/credentials/{{ $credential->id }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-remove" onclick="return confirm('Are you sure you want to remove this biometric credential?')">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <p>No biometric credentials registered yet.</p>
                    <p style="font-size: 13px; margin-top: 0.5rem;">Log out and log back in to set up biometric authentication.</p>
                </div>
            @endif
        </div>

        <div class="card">
            <h2>Account Information</h2>
            <div style="font-size: 14px; color: #6d7175; line-height: 1.8;">
                <p><strong style="color: #202223;">Name:</strong> {{ auth()->user()->name }}</p>
                <p><strong style="color: #202223;">Email:</strong> {{ auth()->user()->email }}</p>
                <p><strong style="color: #202223;">Member since:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Load biometric login script -->
    <script src="{{ asset('js/biometric-login.js') }}"></script>
    
    @if(session('biometric_enrollment_pending'))
        <script>
            console.log('[Biometric] Enrollment pending flag detected!');
            // Set flag for biometric enrollment
            window.biometricEnrollmentPending = true;
            
            // Wait for script to load and trigger enrollment
            setTimeout(function() {
                console.log('[Biometric] Triggering enrollment check...');
                if (typeof window.initBiometricLogin === 'function') {
                    window.initBiometricLogin();
                } else {
                    console.error('[Biometric] initBiometricLogin function not found!');
                }
            }, 1000);
        </script>
        @php
            // Clear the flag after displaying
            session()->forget('biometric_enrollment_pending');
        @endphp
    @endif
</body>
</html>
