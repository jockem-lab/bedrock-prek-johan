<?php

namespace App\View\Components;

use Roots\Acorn\View\Component;

class Form extends Component
{
    public $slug = '';

    public function __construct($slug)
    {
        $forms = \App\View\Composers\Form::getFormBySlug($slug);
        if (is_object($forms)) {
            $this->slug = $slug;
        }
    }

    public function render()
    {
        return $this->view('components.form');
    }
}