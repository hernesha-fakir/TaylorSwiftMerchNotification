{{-- Custom TypeScript Notification Dropdown for Filament --}}
<div id="notification-dropdown" class="flex items-center" x-data="{ loaded: false }" x-init="$nextTick(() => { loaded = true; console.log('Notification dropdown container loaded'); })">
    {{-- Container will be populated by TypeScript --}}
</div>

@once
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $jsFile = $manifest['resources/js/app.ts']['file'] ?? null;
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    @endphp

    @if($cssFile)
        <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
    @endif

    @if($jsFile)
        <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
    @endif
@endonce
