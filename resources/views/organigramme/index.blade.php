<x-app-layout>
    <div class="p-6 bg-gray-50 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">
                Organigramme de la structure
            </h1>
            <p class="text-gray-500 text-sm">
                Gestion hiérarchique des postes
            </p>
        </div>

        {{-- CONTAINER SAAS --}}
        <div class="bg-white shadow rounded-xl p-6 overflow-auto">

            {{-- TREE WRAPPER --}}
            <div class="flex justify-center min-w-max">

                <ul class="flex flex-col items-center relative">

                    @foreach($structures as $structure)
                        @include('organigramme.partials.tree', [
                            'structure' => $structure
                        ])
                    @endforeach

                </ul>

            </div>

        </div>

    </div>
</x-app-layout>