<a {{ $attributes }} class="{{ $active 
    ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-[1.02]' 
    : 'text-gray-500 hover:bg-white hover:text-primary hover:shadow-sm' }} 
    flex items-center px-4 py-3.5 rounded-2xl font-black text-sm transition-all duration-300 group">
    <i data-lucide="{{ $icon }}" class="w-5 h-5 mr-3 {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-primary' }}"></i>
    {{ $slot }}
</a>