@if($image_url)
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg h-full">
        <img
            src="{{ $image_url }}"
            alt="{{ $product_name }}"
            style="max-height: 500px;"
            class="w-auto object-cover rounded-lg shadow-sm"
        />
    </div>
@else
    <div class="flex items-center justify-center p-8 bg-gray-50 dark:bg-gray-800 rounded-lg h-full">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h4 class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                Select a product
            </h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Product image will appear here
            </p>
        </div>
    </div>
@endif
