<div class="flex flex-col items-center justify-center">
	{{
        \Filament\Support\generate_icon_html($icon, attributes: new \Illuminate\View\ComponentAttributeBag([
            'pointer-events' => 'none',
        ]))
    }}
</div>