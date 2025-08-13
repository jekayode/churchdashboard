<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Church Dashboard') }} - Event Check-In</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Event Check-In</h1>
                        <p class="text-sm text-gray-600">Scan QR codes to check in attendees</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button id="toggleCamera" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            Start Scanner
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="px-4 py-6">
            <!-- Scanner Section -->
            <div class="bg-white rounded-lg shadow-sm border mb-6">
                <div class="p-4">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">QR Code Scanner</h2>
                    
                    <!-- Scanner Container -->
                    <div id="scanner-container" class="hidden">
                        <div id="qr-reader" class="w-full max-w-md mx-auto rounded-lg overflow-hidden"></div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">Point your camera at the QR code on the ticket</p>
                        </div>
                    </div>
                    
                    <!-- Manual Entry -->
                    <div id="manual-entry" class="mt-4">
                        <label for="registrationId" class="block text-sm font-medium text-gray-700 mb-2">
                            Or enter Registration ID manually:
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" 
                                   id="registrationId" 
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   placeholder="Enter registration ID">
                            <button onclick="checkInManually()" 
                                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition-colors">
                                Check In
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            <div id="status-messages" class="space-y-3">
                <!-- Success/Error messages will be inserted here -->
            </div>

            <!-- Recent Check-ins -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Check-ins</h3>
                    <div id="recent-checkins" class="space-y-3">
                        <p class="text-sm text-gray-500 text-center py-8">No check-ins yet</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let html5QrCode = null;
        let isScanning = false;

        // Initialize the app
        document.addEventListener('DOMContentLoaded', function() {
            // Check for camera permissions and availability
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                console.log('Camera API available');
            } else {
                showMessage('Camera not available on this device', 'error');
            }
        });

        // Toggle camera scanner
        document.getElementById('toggleCamera').addEventListener('click', function() {
            if (isScanning) {
                stopScanner();
            } else {
                startScanner();
            }
        });

        // Start QR code scanner
        function startScanner() {
            const scannerContainer = document.getElementById('scanner-container');
            const toggleBtn = document.getElementById('toggleCamera');
            
            scannerContainer.classList.remove('hidden');
            toggleBtn.textContent = 'Stop Scanner';
            toggleBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            toggleBtn.classList.add('bg-red-600', 'hover:bg-red-700');
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrCode.start(
                { facingMode: "environment" }, // Use back camera
                config,
                onScanSuccess,
                onScanError
            ).then(() => {
                isScanning = true;
                console.log('QR Code scanner started');
            }).catch(err => {
                console.error('Error starting scanner:', err);
                showMessage('Failed to start camera. Please check permissions.', 'error');
                stopScanner();
            });
        }

        // Stop QR code scanner
        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    isScanning = false;
                    
                    const scannerContainer = document.getElementById('scanner-container');
                    const toggleBtn = document.getElementById('toggleCamera');
                    
                    scannerContainer.classList.add('hidden');
                    toggleBtn.textContent = 'Start Scanner';
                    toggleBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                    toggleBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    
                    console.log('QR Code scanner stopped');
                }).catch(err => {
                    console.error('Error stopping scanner:', err);
                });
            }
        }

        // Handle successful QR code scan
        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code scanned:', decodedText);
            
            // Extract registration ID from URL
            const url = new URL(decodedText);
            const pathParts = url.pathname.split('/');
            const registrationId = pathParts[pathParts.length - 1];
            
            if (registrationId) {
                checkInAttendee(registrationId);
                // Stop scanner briefly to prevent multiple scans
                setTimeout(() => {
                    if (isScanning) {
                        // Scanner will continue after brief pause
                    }
                }, 2000);
            } else {
                showMessage('Invalid QR code format', 'error');
            }
        }

        // Handle scan errors (usually just no QR code in view)
        function onScanError(error) {
            // Don't log every scan error as it's normal when no QR code is visible
        }

        // Check in attendee by registration ID
        function checkInAttendee(registrationId) {
            showMessage('Processing check-in...', 'info');
            
            fetch(`/check-in/${registrationId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    addToRecentCheckins(data.data);
                } else {
                    showMessage(data.message || 'Check-in failed', 'error');
                }
            })
            .catch(error => {
                console.error('Check-in error:', error);
                showMessage('Network error. Please try again.', 'error');
            });
        }

        // Manual check-in
        function checkInManually() {
            const registrationId = document.getElementById('registrationId').value.trim();
            
            if (!registrationId) {
                showMessage('Please enter a registration ID', 'error');
                return;
            }
            
            checkInAttendee(registrationId);
            document.getElementById('registrationId').value = '';
        }

        // Add to recent check-ins list
        function addToRecentCheckins(data) {
            const container = document.getElementById('recent-checkins');
            
            // Remove "no check-ins" message
            if (container.querySelector('p')) {
                container.innerHTML = '';
            }
            
            const checkInElement = document.createElement('div');
            checkInElement.className = 'flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg';
            
            const now = new Date().toLocaleTimeString();
            const eventName = data.event?.name || 'Unknown Event';
            const attendeeName = data.registration?.name || 'Unknown Attendee';
            
            checkInElement.innerHTML = `
                <div>
                    <p class="font-medium text-green-900">${attendeeName}</p>
                    <p class="text-sm text-green-700">${eventName}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-green-900">${now}</p>
                    <p class="text-xs text-green-600">Checked In</p>
                </div>
            `;
            
            container.insertBefore(checkInElement, container.firstChild);
            
            // Keep only the last 10 check-ins
            while (container.children.length > 10) {
                container.removeChild(container.lastChild);
            }
        }

        // Show status messages
        function showMessage(message, type) {
            const container = document.getElementById('status-messages');
            
            const messageElement = document.createElement('div');
            messageElement.className = `p-4 rounded-lg border ${getMessageClasses(type)}`;
            messageElement.textContent = message;
            
            container.insertBefore(messageElement, container.firstChild);
            
            // Remove message after 5 seconds
            setTimeout(() => {
                if (messageElement.parentNode) {
                    messageElement.parentNode.removeChild(messageElement);
                }
            }, 5000);
            
            // Keep only the last 3 messages
            while (container.children.length > 3) {
                container.removeChild(container.lastChild);
            }
        }

        // Get CSS classes for message types
        function getMessageClasses(type) {
            switch (type) {
                case 'success':
                    return 'bg-green-50 border-green-200 text-green-800';
                case 'error':
                    return 'bg-red-50 border-red-200 text-red-800';
                case 'info':
                    return 'bg-blue-50 border-blue-200 text-blue-800';
                default:
                    return 'bg-gray-50 border-gray-200 text-gray-800';
            }
        }

        // Allow Enter key for manual entry
        document.getElementById('registrationId').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkInManually();
            }
        });
    </script>
</body>
</html> 