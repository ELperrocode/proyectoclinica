<link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

<!-- Styles -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans">
    <div class="bg-gray-50 text-black/50 dark:bg-gray-800 dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <!-- Hero -->
                <div class="relative overflow-hidden before:absolute before:top-0 before:start-1/2 before:bg-[url('https://preline.co/assets/svg/examples/polygon-bg-element.svg')] dark:before:bg-[url('https://preline.co/assets/svg/examples-dark/polygon-bg-element.svg')] before:bg-no-repeat before:bg-top before:bg-cover before:size-full before:-z-[1] before:transform before:-translate-x-1/2">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-10">
                        <!-- Title -->
                        <div class="mt-5 max-w-2xl text-center mx-auto">
                            <h1 class="block font-bold text-gray-800 text-4xl md:text-5xl lg:text-6xl dark:text-neutral-200">
                                Bienvenidos a la Clínica
                                <span class="bg-clip-text bg-gradient-to-tl from-blue-600 to-violet-600 text-transparent animate-pulse">MedCare</span>
                            </h1>
                        </div>
                        <!-- End Title -->

                        <!-- Buttons -->
                        <div class="mt-8 gap-3 flex justify-center">
                            <a type="button" href="/login" class="cursor-pointer py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                                Iniciar Sesión
                                <x-tabler-login />
                            </a>
                            <a type="button" href="/register" class="cursor-pointer py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
                                Registrarse
                                <x-tabler-user-plus />
                            </a>
                        </div>
                        <div class="flex justify-center mt-4">
                            <x-dark-toggle-button />
                        </div>
                        <!-- End Buttons -->

                        <!-- Additional Information -->
                        <div class="mt-12 text-center">
                            <p class="text-lg text-gray-600 dark:text-gray-400">
                                Nuestra clínica está dedicada a proporcionar la mejor atención médica. Regístrese o inicie sesión para acceder a su historial médico, programar citas y más.
                            </p>
                        </div>
                        <!-- End Additional Information -->
                    </div>
                </div>
                <!-- End Hero -->
            </div>
        </div>
    </div>
</body>