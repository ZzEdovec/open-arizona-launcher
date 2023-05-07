<?php
namespace app\forms;

use facade\Json;
use httpclient;
use std, gui, framework, app;


class MainForm extends AbstractForm
{

    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {    
        $this->appModule()->detectSystem() == 'Windows' ? browse('https://arizona-rp.com/shop') : execute('xdg-open https://arizona-rp.com/shop');
    }

    /**
     * @event linkAlt.action 
     */
    function doLinkAltAction(UXEvent $e = null)
    {    
        $this->toast('скоро станет доступно :)');
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $servers = $this->appModule()->httpClient->execute('https://api-samp.arizona-five.com/launcher/servers-data');
        if ($servers->isFail())
            $this->appModule()->noInternet();
        
        $servers = $servers->body()['arizona'];
        foreach ($servers as $server)
        {
            $icon = new UXImageView(UXImage::ofUrl($server['icon']));
            $icon->size = [14,14];
            $name = new UXLabel($server['name'].' ('.$server['online'].'/'.$server['maxplayers'].')');
            $hbox = new UXHBox([$icon,$name]);
            $hbox->spacing = 5;
            $hbox->padding = 2;
            $hbox->data('ip',$server['ip']);
            $hbox->data('port',$server['port']);
            $this->listView->items->add($hbox);
        }
        
        $this->listView->selectedIndex = $this->appModule()->ini->get('selectedServerIndex');
    }

    /**
     * @event listView.action 
     */
    function doListViewAction(UXEvent $e = null)
    {    
        $this->appModule()->ini->set('selectedServerIndex',$e->sender->selectedIndex);
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        if ($this->editAlt->text == null)
        {
            $this->toast('Не указан никнейм');
            return;
        }
        
        $this->initGame();
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {
        fs::clean($this->appModule()->ini->get('gamePath'));
        app()->module('files')->checked = false;
        $this->initGame();
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {
        app()->showForm('installRequirments');
        $this->showPreloader('Ожидание установки');
    }

    /**
     * @event checkboxAlt.click 
     */
    function doCheckboxAltClick(UXMouseEvent $e = null)
    {    
        $this->appModule()->ini->set('widescreen',$e->sender->selected);
    }

    /**
     * @event checkbox3.click 
     */
    function doCheckbox3Click(UXMouseEvent $e = null)
    {    
        $this->appModule()->ini->set('lowgraphics',$e->sender->selected);
    }


    /**
     * @event checkbox4.click 
     */
    function doCheckbox4Click(UXMouseEvent $e = null)
    {    
        $this->appModule()->ini->set('windowed',$e->sender->selected);
    }

    /**
     * @event checkbox5.click 
     */
    function doCheckbox5Click(UXMouseEvent $e = null)
    {    
        $this->appModule()->ini->set('graphics+',$e->sender->selected);
    }

    /**
     * @event checkbox6.click 
     */
    function doCheckbox6Click(UXMouseEvent $e = null)
    {    
        $this->appModule()->ini->set('seasons',$e->sender->selected);
    }

    /**
     * @event image4.click 
     */
    function doImage4Click(UXMouseEvent $e = null)
    {    
        $this->panel->anchorFlags = [null];
        Animation::moveTo($this->panel,400,688,0,function ()
        {
            $this->panel->anchorFlags = ['right'=>1,'top'=>1,'bottom'=>1];
        });
        
    }

    /**
     * @event image3.click 
     */
    function doImage3Click(UXMouseEvent $e = null)
    {    
        $this->panel->anchorFlags = [null];
        Animation::moveTo($this->panel,400,928,0,function ()
        {
            $this->panel->anchorFlags = ['right'=>1,'top'=>1,'bottom'=>1];
        });
    }

    /**
     * @event edit.click 
     */
    function doEditClick(UXMouseEvent $e = null)
    {    
        $this->selectGamePath();
    }

    /**
     * @event panel.construct 
     */
    function doPanelConstruct(UXEvent $e = null)
    {    
        $this->label3->text = System::getProperty('os.name').' '.System::getProperty('os.version');
        $this->edit->text = $this->appModule()->ini->get('gamePath');
        $this->checkboxAlt->selected = $this->appModule()->ini->get('widescreen');
        $this->checkbox3->selected = $this->appModule()->ini->get('lowgraphics');
        $this->checkbox4->selected = $this->appModule()->ini->get('windowed');
        $this->checkbox5->selected = $this->appModule()->ini->get('graphics+');
        $this->checkbox6->selected = $this->appModule()->ini->get('seasons');
        $this->checkbox->selected = $this->appModule()->ini->get('autologin');
        $this->checkbox7->selected = $this->appModule()->ini->get('preload');
        #$this->checkbox8->selected = $this->appModule()->ini->get('checkMD5');
        $this->editAlt->text = $this->appModule()->ini->get('nickname');
    }

    /**
     * @event image.click 
     */
    function doImageClick(UXMouseEvent $e = null)
    {    
        if ($this->panel->x != 928)
            $this->doImage3Click();
    }

    /**
     * @event imageAlt.click 
     */
    function doImageAltClick(UXMouseEvent $e = null)
    {    
        $this->doImageClick();
    }

    /**
     * @event editAlt.keyUp 
     */
    function doEditAltKeyUp(UXKeyEvent $e = null)
    {    
        $this->appModule()->ini->set('nickname',$e->sender->text);
    }

    /**
     * @event checkbox.click 
     */
    function doCheckboxClick(UXMouseEvent $e = null)
    {
        $this->appModule()->ini->set('autologin',$e->sender->selected);
    }

    /**
     * @event checkbox7.click 
     */
    function doCheckbox7Click(UXMouseEvent $e = null)
    {
        $this->appModule()->ini->set('preload',$e->sender->selected);
    }


}
