<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
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
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('layouts.guest', ['title' => $this->title]);
    }
}
