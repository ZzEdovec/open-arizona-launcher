<?php
namespace app\forms;

use std, gui, framework, app;


class reqLinux extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        app()->form('installRequirments')->linInstall(true);
        $this->button->free();
        $this->textArea->free();
        $this->free();
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {
        browse('https://vk.me/queinu');
    }

}
