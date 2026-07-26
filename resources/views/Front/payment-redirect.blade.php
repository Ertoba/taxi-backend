<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keepz</title>
    <style>
        body {
            align-items: center;
            background: #f8faf5;
            display: flex;
            font-family: sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
        }

        .loader {
            animation: spin 1s linear infinite;
            border: 7px solid #e5e7eb;
            border-radius: 50%;
            border-top-color: #f2bd0b;
            height: 48px;
            width: 48px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <form action="{{ route('payment', ['booking' => $bookingId, 'method' => $method]) }}"
          method="post"
          id="keepz-payment-form">
        @csrf
        <noscript>
            <button type="submit">Keepz-ით გადახდა</button>
        </noscript>
    </form>
    <div class="loader" aria-label="Keepz-ის გადახდის გვერდი იტვირთება"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('keepz-payment-form').submit();
        }, { once: true });
    </script>
</body>
</html>
