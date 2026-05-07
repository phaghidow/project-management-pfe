<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger inline-flex items-center justify-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest !text-white hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
