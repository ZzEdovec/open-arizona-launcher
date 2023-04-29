<?php
namespace app\modules;

use Throwable;
use std, gui, framework, app;


class AppModule extends AbstractModule
{

    /**
     * @event action 
     */
    function doAction(ScriptEvent $e = null)
    {
        $this->initConfig();
    }
    
    function detectSystem()
    {
        if (str::contains(System::getProperty('os.name'),'Windows'))
            return 'Windows';
        else 
            return 'Linux';
    }
    
    function getAvailableCdnDomain()
    {
        foreach (['arizona-recovery.react.domains','arizona-recovery.react.group'] as $domain)
        {
            try
            {
                $socket = new Socket;
                $socket->connect($domain,443);
                $socket->close();
                return 'https://'.$domain;
            }
            catch (Throwable $ex)
            {
                continue;
            }
        }
        
        $this->noInternet();
    }
    
    function initConfig()
    {
        if ($this->detectSystem() == 'Windows')
            $cfgpath = System::getEnv()['APPDATA'].'/queinu arizona launcher';
        else
            $cfgpath = System::getEnv()['HOME'].'/.config/queinu arizona launcher';
        
        if (fs::isDir($cfgpath) == false)
        {
            fs::makeDir($cfgpath);
        }
        
        $this->ini->path = $cfgpath.'/config.ini';
        app()->module('files')->gamepath = $this->ini->get('gamePath');
    }
    
    function noInternet()
    {
        UXDialog::showAndWait('Запуск лаунчера без Интернета невозможен или к серверам Arizona сейчас нету доступа','ERROR');
        System::halt(1);
    }
}
