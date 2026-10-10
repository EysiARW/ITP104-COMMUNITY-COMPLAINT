<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300&family=Roboto+Condensed:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
<script>
    try {
        const theme = localStorage.getItem('banlic_theme');
        if (theme === 'dark' || (!theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        if (localStorage.getItem('banlic_sidebar_folded') === '1' && window.innerWidth > 900) {
            document.documentElement.classList.add('sidebar-folded');
        }
    } catch (e) {}
</script>
