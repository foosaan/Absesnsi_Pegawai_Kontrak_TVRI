<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public string $title;

    /**
     * Create a new component instance.
     */
    public function __construct(string $title = 'TVRI Absensi')
    {
        $this->title = $title;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.tailwind', ['title' => $this->title]);
    }
}
