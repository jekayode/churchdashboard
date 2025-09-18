<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>API Test Page</h1>
    <button onclick="testAPI()">Test Dashboard API</button>
    <div id="results"></div>

    <script>
        async function testAPI() {
            console.log('Testing API...');
            console.log('User:', @json(Auth::user()));
            console.log('Branch ID:', @json(Auth::user()->getActiveBranchId()));
            
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = 'Testing...';
            
            try {
                const response = await fetch('/api/reports/dashboard?period=month&branch_id=1', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const data = await response.json();
                console.log('Response data:', data);
                
                resultsDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                console.error('Error:', error);
                resultsDiv.innerHTML = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
