<x-app-layout>
  <div class="page-mobile">
    <div id="my-tasks-app" data-api-url="{{ route('api.my-tasks') }}"></div>
    
@vite(['resources/js/app.js', 'resources/js/my-tasks-mount.js'])
  </div>
  
  <meta name="csrf-token" content="{{ csrf_token() }}">
</x-app-layout>
