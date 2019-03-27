<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Управление консольными скриптами.
 */
class ZFE_Controller_Default_ConsoleManager extends Controller_Abstract
{
    public function indexAction()
    {
        $this->view->commands = ZFE_Console_CommandBroker::getInstance()->getCommands();
    }

    public function executeAction()
    {
        $broker = ZFE_Console_CommandBroker::getInstance();
        $this->view->command = $broker->getCommand($this->getParam('command'));
    }

    public function consoleAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $command = $this->getParam('command');
        $params = array_diff(explode(' ', $this->getParam('params')), ['', null]);

        ob_implicit_flush();
        echo '<pre>';
        $tools = new ZFE_Console_Tools;
        $tools->run($command, $params);
        echo '</pre>';
    }
}