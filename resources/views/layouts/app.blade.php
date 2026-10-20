<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TaskFlow') · TaskFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DM Sans', 'sans-serif'], display: ['Manrope', 'sans-serif'] },
                    colors: { forest: '#315b45', leaf: '#5c8763', coral: '#d47a61', canvas: '#f5f6f0' }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        summary::-webkit-details-marker { display: none; }
        @media (prefers-reduced-motion: no-preference) {
            .rise-in { animation: rise-in .35s ease-out both; }
            @keyframes rise-in { from { opacity: 0; transform: translateY(7px); } to { opacity: 1; transform: translateY(0); } }
        }
    </style>
</head>
<body class="min-h-screen bg-canvas text-stone-800 antialiased">
    @yield('content')
</body>
</html>