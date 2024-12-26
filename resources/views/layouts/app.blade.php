<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Vedica ERP') }} - @yield('title')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://unpkg.com/@tailwindcss/forms@0.2.1/dist/forms.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased">
    <nav class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-2xl font-bold">Vedica ERP</a>
            <div class="space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:bg-blue-700 px-4 py-2 rounded">Login</a>
                @else
                    <span>Welcome, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:bg-blue-700 px-4 py-2 rounded">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    <main class="container mx-auto mt-8 px-4">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" 
                 class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" 
                 role="alert">
                {{ session('success') }}
                <button @click="show = false" class="ml-2 text-green-500">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" 
                 class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" 
                 role="alert">
                {{ session('error') }}
                <button @click="show = false" class="ml-2 text-red-500">✕</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-200 text-center py-4 mt-8">
        <p>&copy; {{ date('Y') }} Vedica ERP. All rights reserved.</p>
    </footer>

    @stack('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
