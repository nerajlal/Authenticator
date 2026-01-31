<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biometric Diagnostic</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #00ff00;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #00ff00;
            border-radius: 5px;
        }
        .pass { color: #00ff00; }
        .fail { color: #ff0000; }
        .info { color: #ffff00; }
        button {
            background: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🔍 Biometric Authentication Diagnostic</h1>

    <div class="section">
        <h2>1. Session Status</h2>
        <p>User ID: <span class="info">{{ auth()->id() }}</span></p>
        <p>User Email: <span class="info">{{ auth()->user()->email }}</span></p>
        <p>Enrollment Flag: <span class="{{ session('biometric_enrollment_pending') ? 'pass' : 'fail' }}">
            {{ session('biometric_enrollment_pending') ? 'TRUE ✓' : 'FALSE ✗' }}
        </span></p>
        <p>Session ID: <span class="info">{{ session()->getId() }}</span></p>
    </div>

    <div class="section">
        <h2>2. Biometric Credentials</h2>
        @php
            $credentials = auth()->user()->biometricCredentials;
        @endphp
        <p>Credentials Count: <span class="info">{{ $credentials->count() }}</span></p>
        @if($credentials->count() > 0)
            @foreach($credentials as $cred)
                <p>- {{ $cred->device_name ?? 'Unknown' }} ({{ $cred->created_at->diffForHumans() }})</p>
            @endforeach
        @endif
    </div>

    <div class="section">
        <h2>3. JavaScript Tests</h2>
        <p>WebAuthn Support: <span id="webauthn-support" class="info">Checking...</span></p>
        <p>Biometric Script Loaded: <span id="script-loaded" class="info">Checking...</span></p>
        <p>Window Flag: <span id="window-flag" class="info">Checking...</span></p>
    </div>

    <div class="section">
        <h2>4. Manual Tests</h2>
        <button onclick="testEnrollment()">Test Enrollment Popup</button>
        <button onclick="testLogin()">Test Biometric Login</button>
        <button onclick="setFlag()">Set Enrollment Flag</button>
        <div id="test-output" style="margin-top: 10px;"></div>
    </div>

    <div class="section">
        <h2>5. Console Logs</h2>
        <div id="console-output" style="max-height: 300px; overflow-y: auto;"></div>
    </div>

    <script src="{{ asset('js/biometric-login.js') }}"></script>
    
    <script>
        // Capture console logs
        const consoleOutput = document.getElementById('console-output');
        const originalLog = console.log;
        const originalError = console.error;
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            consoleOutput.innerHTML += `<p class="pass">[LOG] ${args.join(' ')}</p>`;
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            consoleOutput.innerHTML += `<p class="fail">[ERROR] ${args.join(' ')}</p>`;
        };

        // Check WebAuthn support
        document.getElementById('webauthn-support').textContent = 
            (window.PublicKeyCredential !== undefined) ? 'YES ✓' : 'NO ✗';
        document.getElementById('webauthn-support').className = 
            (window.PublicKeyCredential !== undefined) ? 'pass' : 'fail';

        // Check if biometric script loaded
        setTimeout(() => {
            const loaded = typeof window.initBiometricLogin === 'function';
            document.getElementById('script-loaded').textContent = loaded ? 'YES ✓' : 'NO ✗';
            document.getElementById('script-loaded').className = loaded ? 'pass' : 'fail';
            
            // Check window flag
            const flagSet = window.biometricEnrollmentPending === true;
            document.getElementById('window-flag').textContent = flagSet ? 'TRUE ✓' : 'FALSE ✗';
            document.getElementById('window-flag').className = flagSet ? 'pass' : 'fail';
        }, 500);

        function testEnrollment() {
            const output = document.getElementById('test-output');
            output.innerHTML = '<p class="info">Testing enrollment popup...</p>';
            
            if (typeof window.initBiometricLogin === 'function') {
                window.biometricEnrollmentPending = true;
                window.initBiometricLogin();
                output.innerHTML += '<p class="pass">Enrollment function called ✓</p>';
            } else {
                output.innerHTML += '<p class="fail">Enrollment function not found ✗</p>';
            }
        }

        function testLogin() {
            const output = document.getElementById('test-output');
            output.innerHTML = '<p class="info">Testing biometric login...</p>';
            
            fetch('/api/biometric/login-options', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                output.innerHTML += '<p class="pass">API Response: ' + JSON.stringify(data).substring(0, 100) + '...</p>';
            })
            .catch(err => {
                output.innerHTML += '<p class="fail">API Error: ' + err.message + '</p>';
            });
        }

        function setFlag() {
            window.biometricEnrollmentPending = true;
            document.getElementById('window-flag').textContent = 'TRUE ✓';
            document.getElementById('window-flag').className = 'pass';
            alert('Flag set! Now click "Test Enrollment Popup"');
        }

        // Auto-trigger if flag is set
        @if(session('biometric_enrollment_pending'))
            console.log('[DIAGNOSTIC] Session flag detected, triggering enrollment...');
            window.biometricEnrollmentPending = true;
            setTimeout(() => {
                if (typeof window.initBiometricLogin === 'function') {
                    console.log('[DIAGNOSTIC] Calling initBiometricLogin...');
                    window.initBiometricLogin();
                } else {
                    console.error('[DIAGNOSTIC] initBiometricLogin not found!');
                }
            }, 1000);
        @endif
    </script>
</body>
</html>
