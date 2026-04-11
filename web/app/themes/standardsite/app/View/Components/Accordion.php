<?php

namespace App\View\Components;

use Roots\Acorn\View\Component;

class Accordion extends Component
{

    public $title;
    public $target;
    public $group;
    public $content;
    public $expanded;
    public static $groups = [];

    public function __construct($title = '', $target = null, $group = '', $content = null)
    {
        $this->title   = $title;
        $this->target  = $target ?? uniqid();
        $this->group   = $group;
        $this->content = $content;
        $this->addGroup();
        $this->expanded = $this->isExpanded();
    }

    private function addGroup()
    {
        if (!empty($this->group)) {
            if (!isset(self::$groups[$this->group])) {
                self::$groups[$this->group] = [
                    'count' => 0,
                ];
            }
            self::$groups[$this->group]['count']++;
        }
    }

    private function isExpanded(): bool
    {
        return isset(self::$groups[$this->group]) && self::$groups[$this->group]['count'] === 1;
    }

    public function render()
    {
        return $this->view('components.accordion');
    }
}