<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LoginAlert extends Component
{
    /**
     * Create a new component instance.
     */
    public $type;
    public $message;
    public function __construct($type = 'success', $message = 'connect with ashish to make component work')
    {
        $this->type = $type;
        $this->message = $message;
    }
   

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.login-alert');
    }
}
