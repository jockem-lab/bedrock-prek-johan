<?php

namespace App\View\Components;

use Roots\Acorn\View\Component;

use function App\getAttribute;

class Coworker extends Component
{

    public $type;
    public $imageSrc;
    public $name;
    public $title;
    public $titleExtra;
    public $email;
    public $cellphone;
    public $cellphoneString;
    public $phone;
    public $phoneString;
    public $permalink;
    public function __construct($coworker = null, $hide = [])
    {
        $hidden = is_array($hide) ? $hide : explode(',', $hide);
        $hidden = array_map(function($item){ return trim($item); }, $hidden);
        if(!empty($coworker->type) && !in_array('type', $hidden)){
            $this->type = $coworker->type;
        }
        if(!empty($coworker->image->src)  && !in_array('image', $hidden)){
            $this->imageSrc = $coworker->image->src;
        }
        if(!empty($coworker->name)  && !in_array('name', $hidden)){
            $this->name = $coworker->name;
        }
        if(!empty($coworker->title)  && !in_array('title', $hidden)){
            $this->title = $coworker->title;
        }
        if(!empty($coworker->titleExtra)  && !in_array('titleExtra', $hidden)){
            $this->titleExtra = $coworker->titleExtra;
        }
        if(!empty($coworker->email)  && !in_array('email', $hidden)){
            $this->email = $coworker->email;
        }

        if(!empty($coworker->cellphone)  && !in_array('cellphone', $hidden)){
            $this->cellphone = $coworker->cellphone;
            $this->cellphoneString = $coworker->cellphoneString;
        }

        if(!empty($coworker->phone)  && !in_array('phone', $hidden)){
            $this->phone = $coworker->phone;
            $this->phoneString = $coworker->phoneString;
        }

        $this->permalink = getAttribute('permalink', $coworker);
    }

    public function render()
    {
        return $this->view('components.coworker');
    }
}
