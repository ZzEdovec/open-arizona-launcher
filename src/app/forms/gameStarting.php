<?php
namespace app\forms;

use Throwable;
use std, gui, framework, app;


class gameStarting extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $ini = $this->appModule()->ini;
        $server = app()->form('MainForm')->listView->selectedItem;
        $gamepath = app()->module('files')->gamepath;
        
        if ($ini->get('widescreen'))
            $exec .= ' -widescreen';
        if ($ini->get('windowed'))
            $exec .= ' -window';
        if ($ini->get('lowgraphics'))
            $exec .= ' -t';
        if ($ini->get('seasons'))
            $exec .= ' -seasons';
        if ($ini->get('graphics+'))
            $exec .= ' -graphics';
        if ($ini->get('preload'))
            $exec .= ' -ldo';
        if ($ini->get('autologin'))
            $exec .= ' -x';
        if ($this->appModule()->detectSystem() == 'Windows')
            $proc = new Process([$gamepath.'\gta_sa.exe','-c -h '.$server->data('ip').' -p '.$server->data('port').' -n '.app()->form('MainForm')->editAlt->text.' -mem 2048'.$exec.' -arizona'],$gamepath);
        else 
            $proc = new Process(['wine',$gamepath.'/gta_sa.exe','-c -h '.$server->data('ip').' -p '.$server->data('port').' -n '.app()->form('MainForm')->editAlt->text.' -mem 2048'.$exec.' -arizona'],$gamepath);
        
        $this->label->blinkAnim->disable();
        $this->label->blinkAnim->free();
        new Thread(function () use ($proc)
        {
            try 
            {
                $proc->startAndWait();
            }
            catch (Throwable $ex)
            {
                uiLater(function () use ($ex)
                {
                    UXDialog::show('Произошла ошибка - '.$ex->getMessage(),'ERROR');
                });
            }

            uiLater(function ()
            {
            $this->kill();
            });

        })->start();
        
    }
    
    function kill()
        app()->form('MainForm')->button->enabled = true;
        $this->label->free();
        $this->panel->free();
        $this->free();
    }

}
