<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50 text-[16px]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0284c7">
    <title>{{ config('app.name', 'EyesComp') }} — Point de vente & Gestion de stock</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 antialiased font-sans text-base leading-normal selection:bg-sky-500 selection:text-white">
    <div id="app" class="h-full text-base"></div>
</body>
</html>
