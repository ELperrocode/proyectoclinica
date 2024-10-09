<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard del Paciente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- Bienvenida -->
                    <span class="font-black text-2xl text-gray-500 font-mono mb-4 dark:text-neutral-400">Bienvenido, [Nombre del Paciente]</span>
                    <p class="text-gray-800 dark:text-white mt-4">
                        Aquí puedes gestionar tus citas, acceder a tus historiales médicos, ver tus recetas y diagnósticos, y más.
                    </p>

                    <!-- Enlaces a Funcionalidades -->
                    <ul class="list-disc list-inside text-gray-800 dark:text-white mt-4">
                        <li>
                            <a href="#" class="flex items-center space-x-2">
                                <i class="tabler-calendar text-xl"></i>
                                <span>Mis Citas</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center space-x-2">
                                <i class="tabler-file-medical text-xl"></i>
                                <span>Historiales Médicos</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center space-x-2">
                                <i class="tabler-prescription text-xl"></i>
                                <span>Recetas y Diagnósticos</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center space-x-2">
                                <i class="tabler-phone text-xl"></i>
                                <span>Información de Contacto</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>