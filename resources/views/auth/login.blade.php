<!DOCTYPE html>
<html lang="en" class="dark" x-data="{ 
    darkMode: localStorage.getItem('theme') !== 'light',
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartoh Technologies - ISP Billing &amp; Network Management Suite</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased selection:bg-blue-600 selection:text-white relative overflow-hidden transition-colors">

    <!-- Ambient Glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Theme Switcher -->
    <div class="absolute top-4 right-4">
        <button @click="toggleTheme()" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 shadow-sm transition">
            <template x-if="darkMode">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </template>
            <template x-if="!darkMode">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            </template>
        </button>
    </div>

    <div class="max-w-md w-full relative z-10">

        <!-- Header & Logo -->
        <div class="text-center mb-6">
            <a href="{{ url('/') }}" class="inline-block hover:scale-105 transition-transform mb-3">
                <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Technologies Logo" class="w-16 h-16 rounded-2xl object-contain bg-white dark:bg-slate-900 shadow-xl shadow-blue-500/10 border border-slate-200 dark:border-slate-800 p-1 mx-auto">
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Smartoh Technologies</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Welcome back — sign in to your ISP console</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">

            @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-xl text-xs text-rose-600 dark:text-rose-400">
                {{ $errors->first() }}
            </div>
            @endif

            @if (session('success'))
            <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl text-xs text-emerald-600 dark:text-emerald-400">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        placeholder="admin@yourisp.com"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        placeholder="••••••••••"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500/20">
                        <span>Remember session</span>
                    </label>
                    <span class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Forgot password?</span>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-600/20 transition transform active:scale-[0.98]">
                    Sign In to Console
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    New ISP? 
                    <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Register your company &rarr;</a>
                </p>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center mt-5 text-xs text-slate-400 font-mono">
            Smartoh Technologies &bull; Intelligent ISP Billing Platform v2.0
        </div>
    </div>

</body>
</html>
