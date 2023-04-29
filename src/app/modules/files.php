<?php
namespace app\modules;

use Throwable;
use std, gui, framework, app;


class files extends AbstractModule
{

    $fileslist;
    $repo;
    $gamepath;
    $todownload;
    $downloaded;
    $checked;
    
    function initGame()
    {
        if (fs::isDir($this->gamepath) == false)
        {
            $this->selectGamePath();
            $newuser = true;
        }
        
        $this->repo = $this->appModule()->getAvailableCdnDomain();
        $game = $this->appModule()->httpClient->execute($this->repo.'/desktop/game/arizona/game.json');
        if ($game->isError())
            $this->appModule()->noInternet();
        $this->todownload = 0;
        $this->countFiles($game->body()['data']);
        $this->downloaded = 0;
        app()->form('MainForm')->progressBar->show();
        app()->form('MainForm')->label->show();
        app()->form('MainForm')->button->enabled = false;
        
        new Thread(function () use ($game,$newuser)
        {
            if ($this->checked != true)
                $this->processDir($game->body());
            $this->checked = true;
            
            if ($newuser)
                $this->showDownloadedNotify();
            uiLater(function ()
            {
            app()->form('MainForm')->progressBar->hide();
            app()->form('MainForm')->label->hide();
            
            $this->loadForm('gameStarting');});
        })->start();
    }
    
    function countFiles($game)
    {
        foreach ($game as $element)
        {
            if ($element['type'] == 'dir')
                $this->countFiles($element['data']);
                
            $this->todownload++;
        }
    }
    
    function processDir($dir,$workdir = '/')
    {
        foreach ($dir['data'] as $content)
        {
            uiLater(function () use ($content)
            {
                $this->downloaded++;
                app()->form('MainForm')->progressBar->progress = ($this->downloaded / $this->todownload) * 100;
                app()->form('MainForm')->label->text = 'Проверка '.$content['name'].' ('.$this->downloaded.'/'.$this->todownload.'), '.app()->form('MainForm')->progressBar->progress.'%';
            });
            
            if ($content['type'] == 'dir')
            {
                fs::makeDir($this->gamepath.$workdir.$content['name']);
                $this->processDir($content,$workdir.$content['name'].'/');
            }
            elseif (md5_file($this->gamepath.$workdir.$content['name']) != $content['hash'])
            {
                uiLater(function () use ($content)
                {
                    app()->form('MainForm')->label->text = 'Скачивание '.$content['name'].' ('.$this->downloaded.'/'.$this->todownload.'), '.app()->form('MainForm')->progressBar->progress.'%';
                });
                $this->downloader->download($this->repo.'/desktop/game/arizona/game'.$workdir.$content['name'],$this->gamepath.$workdir.$content['name'])->isError();
            }
        }
    }
    
    function selectGamePath()
    {
        $dirchooser = new UXDirectoryChooser;
        $dirchooser->title = 'Выберите папку, в которую будет установлена игра';
        $gamedir = $dirchooser->execute();
        if ($gamedir == null)
            return;
        try 
        {
            File::of($gamedir.'/test')->createNewFile();
        }
        catch (Throwable $ex)
        {
            if (uiConfirm('Нет прав для записи в эту папку. Открыть диалог выбора ещё раз?'))
                $this->downloadGame();
            return;
        }
        fs::delete($gamedir.'/test');
        $this->appModule()->ini->set('gamePath',$gamedir);
        $this->gamepath = $gamedir;
        app()->form('MainForm')->edit->text = $gamedir;
    }
    
    function showDownloadedNotify()
    {
        $notify = new UXTrayNotification;
        $notify->title = 'Arizona';
        $notify->message = 'Игра скачана, можно играть!';
        $notify->image = new UXImage('res://.data/img/111.png');
        $notify->notificationType = 'SUCCESS';
        $notify->show();
    }
    
    function detectTerminal()
    {
        if (fs::isFile('/usr/bin/kgx'))
            return 'kgx';
        elseif (fs::isFile('/usr/bin/gnome-terminal'))
            return 'gnome-terminal';
        elseif (fs::isFile('/usr/bin/konsole'))
            return 'konsole';
        elseif (fs::isFile('/usr/bin/lxterminal'))
            return 'lxterminal';
        elseif (fs::isFile('/usr/bin/xfce4-terminal'))
            return 'xfce4-terminal';
        elseif (fs::isFile('/usr/bin/xterm'))
            return 'xterm';
        else 
            return;
    }
}