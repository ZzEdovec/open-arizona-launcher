<?php
namespace app\forms;

use Throwable;
use std, gui, framework, app;


class installRequirments extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        if ($this->appModule()->detectSystem() == 'Windows')
            $this->winInstall();
        else 
            $this->linInstall();
    }
    
    function linInstall($con = false)
    {
        if ($con == false)
        {
            app()->showForm('reqLinux');
            return;
        }
        if (uiConfirm('Лаунчер использует Wine Prefix из переменной окружения WINEPREFIX. Если она не указана вручную, используется стандартный. Продолжить?') == false)
        {
            $this->kill();
            return;
        }
        
        new Thread(function ()
        {
            if (execute($this->detectTerminal().' -e winetricks dxvk vcrun2019',true)->getExitValue() != 0)
            {
                UXDialog::show('Установка завершилась неудачей','ERROR');
                $this->kill();
                return;
            }
             
            $this->kill();
        })->start();
    }
    
    function winInstall()
    {
        new Thread(function ()
        {
            $tmp = System::getProperty('java.io.tmpdir');
            $this->downloader->download('https://softslot.ru/d28bc0c/system/drivers/dxwebsetup.exe',$tmp.'dxwebsetup.exe');
            $this->downloader->download('https://aka.ms/vs/17/release/vc_redist.x86.exe',$tmp.'vc_redist.x86.exe');
            $this->downloader->download('https://aka.ms/vs/17/release/vc_redist.x64.exe',$tmp.'vc_redist.x64.exe');
            uiLater(function ()
            {
                $this->label->text = 'Установка';
            });
            
            try 
            {
                execute('cmd /c "'.$tmp.'dxwebsetup.exe /q"',true);
                execute('cmd /c "'.$tmp.'vc_redist.x86.exe /passive"',true);
                execute('cmd /c "'.$tmp.'vc_redist.x64.exe /passive"',true);
            }
            catch (Throwable $ex)
            {
                uiLater(function ()
                {
                    UXDialog::show('Ошибка запуска установщиков библиотек','ERROR');
                });
            }
            fs::delete($tmp.'dxwebsetup.exe');
            fs::delete($tmp.'vc_redist.x86.exe');
            fs::delete($tmp.'vc_redist.x64.exe');
            $this->kill();
        })->start();
    }
    
    function kill()
    {
        uiLater(function ()
        {
            $this->label->free();
            $this->panel->free();
            $this->free();
            app()->form('MainForm')->hidePreloader();
        });
    }
}
