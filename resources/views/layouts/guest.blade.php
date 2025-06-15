<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Sistema de Gestión Escolar</title>
    <script src="https://kit.fontawesome.com/eb36e646d1.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/uploadForm.js'])
</head>

<body class="flex flex-col min-h-screen w-full font-poppins drawer">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

    <div class="drawer-content flex flex-col">
        <header id="main-header" class="w-full px-5 z-50 sticky top-0 transition-all duration-300 border-b border-primary/0">
            <div class="w-full max-w-[1200px] mx-auto py-5 flex items-center justify-between">
                <div class="flex-1 text-2xl font-oswald">
                    <a href="/" class="hover:text-primary duration-300 cursor-pointer">MATRYERSE</a>
                </div>
                <nav>
                    <div class="lg:hidden">
                        <label for="my-drawer-2" class="btn btn-primary drawer-button py-1.5 px-2 lg:hidden">
                            <i class="fa-solid fa-bars"></i>
                        </label>
                    </div>
                    <ul class="gap-5 hidden lg:flex">
                        <li class="text-lg hover:scale-[1.02] hover:text-primary transition-all duration-300"><a href="/">Inicio</a></li>
                        <li class="text-lg hover:scale-[1.02] hover:text-primary transition-all duration-300"><a href="/funcionalidades">Funcionalidades</a></li>
                        <li class="text-lg hover:scale-[1.02] hover:text-primary transition-all duration-300"><a href="/matricular">Matricular</a></li>
                    </ul>
                </nav>
                <div class="flex-1 flex justify-end">
                    @if (Auth::check())
                    <a href="/dashboard" class="btn btn-primary btn-outline py-1">
                        <i class="fa-solid fa-user text-sm"></i>
                        <span class="ml-1">Panel</span>
                    </a>
                    @else
                    <a href="/login" class="btn btn-primary btn-outline py-1">
                        <i class="fa-solid fa-right-to-bracket text-sm"></i>
                        <span class="ml-1">Autenticate</span>
                    </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="w-full flex-1 space-y-20 mb-15">
            @yield('content')
        </main>

        <footer class="w-full py-4 bg-base-200 text-center text-sm text-base-content">
            &copy; {{ date('Y') }} MATRYERSE. All rights reserved.
        </footer>
    </div>

    <!-- Sidebar -->
    <div class="drawer-side z-50">
        <label for="my-drawer-2" class="drawer-overlay"></label>
        <aside class="bg-base-200 w-80 h-screen">
            <div class="p-4 h-full flex flex-col">
                <div class="text-2xl font-oswald mb-8">
                    <a href="/" class="hover:text-primary duration-300 cursor-pointer">MATRYERSE</a>
                </div>
                <ul class="menu menu-lg gap-2 flex-1">
                    <div class="flex-1">
                        <li>
                            <a href="/" class="hover:bg-primary/10 hover:text-primary transition-all duration-300">
                                <i class="fa-solid fa-house"></i>
                                Inicio
                            </a>
                        </li>
                        <li>
                            <a href="/funcionalidades" class="hover:bg-primary/10 hover:text-primary transition-all duration-300">
                                <i class="fa-solid fa-list-check"></i>
                                Funcionalidades
                            </a>
                        </li>
                        <li>
                            <a href="/matricular" class="hover:bg-primary/10 hover:text-primary transition-all duration-300">
                                <i class="fa-solid fa-graduation-cap"></i>
                                Matricular
                            </a>
                        </li>
                    </div>
                    @if (Auth::check())
                    <li class="mt-4">
                        <a href="/dashboard" class="hover:bg-primary/10 hover:text-primary transition-all duration-300">
                            <i class="fa-solid fa-user"></i>
                            Panel
                        </a>
                    </li>
                    @else
                    <li class="mt-4">
                        <a href="/login" class="hover:bg-primary/10 hover:text-primary transition-all duration-300">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Autenticate
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </aside>
    </div>

    <div class="fixed top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 h-30 w-300 blur-[500px] rounded-[50%] bg-primary/50"></div>
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 h-30 w-500 blur-[500px] rounded-[50%] bg-white/10"></div>

    <style>
        .glass-effect {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    <script>
        const $header = document.getElementById('main-header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                $header.classList.add('glass-effect', 'border-primary/20', 'bg-base-200/50');
            } else {
                $header.classList.remove('glass-effect', 'border-primary/20', 'bg-base-200/50');
            }
        });
    </script>
    @yield('scripts')
</body>

</html>