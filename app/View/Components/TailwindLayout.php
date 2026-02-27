<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class TailwindLayout extends Component
{
    /**
     * Page title
     */
    public string $title;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title = '')
    {
        $this->title = $title ?: config('app.name', 'Absensi TVRI');
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.tailwind');
    }
}
