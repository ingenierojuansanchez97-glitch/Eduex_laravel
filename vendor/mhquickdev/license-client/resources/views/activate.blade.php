<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate License - Product Registration</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at 10% 20%, rgb(15, 23, 42) 0%, rgb(9, 11, 23) 90%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased selection:bg-indigo-500 selection:text-white">
    <!-- Glow Background Effects -->
    <div class="fixed top-0 left-1/4 w-[400px] h-[400px] bg-emerald-500/10 rounded-full blur-[100px] -z-10"></div>
    <div class="fixed bottom-0 right-1/4 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] -z-10"></div>

    <div class="w-full max-w-lg">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-emerald-500 to-indigo-500 rounded-2xl shadow-xl shadow-indigo-500/10 mb-4 ring-1 ring-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Product License Verification</h1>
            <p class="text-gray-400 mt-2 text-sm">Please verify your purchase code to activate your product installation</p>
        </div>

        <!-- Main Form Card (Glassmorphism) -->
        <div class="backdrop-blur-xl bg-slate-900/60 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-white/25 to-transparent"></div>

            @if ($is_activated)
                <!-- Success Welcome Message -->
                <div class="text-center py-6 space-y-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-500/10 rounded-full border border-emerald-500/20 mb-2 relative">
                        <div class="absolute inset-0 bg-emerald-500/5 rounded-full animate-ping"></div>
                        <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold text-white tracking-tight">License Activated</h2>
                        <p class="text-gray-400 text-sm max-w-sm mx-auto">
                            Your product installation is active and verified. Thank you for choosing our product!
                        </p>
                    </div>

                    @if(session('success_message'))
                        <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-xs text-emerald-400 max-w-md mx-auto">
                            {{ session('success_message') }}
                        </div>
                    @endif

                    <!-- Active License Details -->
                    <div class="bg-white/5 border border-white/5 rounded-2xl p-4 text-xs text-left space-y-3 max-w-md mx-auto font-sans">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-medium">Activation Domain</span>
                            <span class="font-mono text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20 font-bold">
                                {{ request()->getHost() }}
                            </span>
                        </div>
                        @if ($purchase_code)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-medium">Purchase Code</span>
                            <span class="font-mono text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20 font-bold">
                                {{ substr($purchase_code, 0, 8) . '-xxxx-xxxx-xxxx-' . substr($purchase_code, -12) }}
                            </span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-medium">License Status</span>
                            <span class="text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20 font-bold uppercase tracking-wider text-[10px]">
                                Active
                            </span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-3.5 w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-sm rounded-2xl shadow-lg shadow-emerald-500/25 transition-all hover:scale-[1.01] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                            Go to Dashboard
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/25 rounded-2xl flex gap-3 items-start">
                        <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-rose-300">Activation Failed</h4>
                            <p class="text-xs text-rose-400/95 mt-1">{{ $errors->first('purchase_code') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('license.activate') }}" class="space-y-6">
                    @csrf

                    <!-- Host info display -->
                    <div class="bg-white/5 border border-white/5 rounded-2xl p-4 flex justify-between items-center text-xs">
                        <span class="text-gray-400 font-medium">Activation Domain</span>
                        <span class="font-mono text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20 font-bold">
                            {{ request()->getHost() }}
                        </span>
                    </div>

                    <!-- Purchase Code input -->
                    <div>
                        <label for="purchase_code" class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Purchase Code</label>
                        <input 
                            type="text" 
                            id="purchase_code" 
                            name="purchase_code" 
                            value="{{ old('purchase_code') }}" 
                            placeholder="e.g. 1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d"
                            required
                            autocomplete="off"
                            class="w-full px-4 py-3 bg-slate-950/50 border border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm text-white font-mono placeholder-gray-600 transition-all"
                        >
                        <p class="text-[11px] text-gray-500 mt-2">
                            Enter your official CodeCanyon purchase code. You can find this inside your Envato Downloads tab.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-sm rounded-2xl shadow-lg shadow-emerald-500/25 transition-all active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-900"
                    >
                        Verify & Activate Product
                    </button>
                </form>
            @endif
        </div>

        <!-- Help Info Footer -->
        <div class="text-center mt-6 text-xs text-gray-500 space-y-1">
            <p>Need assistance with your license? <a href="https://mhquickdev.com/support" target="_blank" class="text-indigo-400 hover:underline">Contact MHQuickDEV Support</a></p>
            <p>&copy; {{ date('Y') }} MHQuickDEV. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
