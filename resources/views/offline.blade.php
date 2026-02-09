<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sense connexió - Menut</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #fdfcfa;
            color: #574139;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            opacity: 0.6;
        }
        h1 {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 1.75rem;
            color: #2d3a24;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 1rem;
            color: #7e5d4e;
            max-width: 300px;
            line-height: 1.6;
        }
        .retry-btn {
            margin-top: 2rem;
            padding: 0.75rem 1.5rem;
            background-color: #536b3c;
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .retry-btn:hover {
            background-color: #425432;
        }
    </style>
</head>
<body>
    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
    </svg>
    <h1>Sense connexió</h1>
    <p>No s'ha pogut connectar. Comprova la teva connexió a internet i torna-ho a provar.</p>
    <button class="retry-btn" id="retry-btn">Tornar a provar</button>
    <script>
        // Manual reload
        document.getElementById('retry-btn').addEventListener('click', () => {
            window.location.reload();
        });
        // Auto-reload when connectivity returns (fixes iOS PWA stuck on offline page)
        window.addEventListener('online', () => {
            window.location.reload();
        });
        // Poll server when device reports online but fetch may still fail
        async function checkNetworkAndReload() {
            try {
                const response = await fetch(window.location.href, {
                    method: 'HEAD',
                    cache: 'no-store'
                });
                if (response.status >= 200 && response.status < 500) {
                    window.location.reload();
                    return;
                }
            } catch (_) {
                // Still offline, keep polling
            }
            setTimeout(checkNetworkAndReload, 2500);
        }
        checkNetworkAndReload();
    </script>
</body>
</html>
