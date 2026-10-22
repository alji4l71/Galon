<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin - {{ \App\Models\Setting::get('business_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 text-slate-800 antialiased">
    <form method="POST" action="{{ url('/admin/login') }}" class="w-full max-w-sm space-y-4 rounded-2xl bg-white p-6 shadow">
        @csrf

        <div class="text-center">
            <p class="text-3xl">💧</p>
            <h1 class="mt-1 text-lg font-bold">{{ \App\Models\Setting::get('business_name') }}</h1>
            <p class="text-sm text-slate-500">Masuk ke panel admin</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div>
            <label for="email" class="mb-1 block text-sm text-slate-600">Email</label>
            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm text-slate-600">Password</label>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Ingat saya
        </label>

        <button class="w-full rounded-lg bg-sky-600 py-2.5 font-semibold text-white hover:bg-sky-700">Masuk</button>
    </form>
</body>
</html>
