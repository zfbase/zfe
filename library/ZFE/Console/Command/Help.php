<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Команда вывода информации по доступным командам.
 */
class ZFE_Console_Command_Help extends ZFE_Console_Command_Abstract
{
    protected static $_name = 'help';
    protected static $_description = 'Справка по доступным командам';
    protected static $_help =
        "При вызове без аргументов выведет список зарегистрированных команд с коротким описанием.\n" .
        "Для получения подробной информации о команде укажите её аргументом. Укажите несколько команд через пробел и получите справку сразу по всем ним.";

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [])
    {
        if ($params) {
            foreach ($params as $command) {
                $description = $command::getDescription();
                if ($description) {
                    echo $description . "\n";
                }

                echo ($command::getHelp() ?? 'Подробная информация о команде не доступна.') . "\n";
            }
        } else {
            $table = $this->getHelperBroker()->get('Table');
            $table->setHeaders(['Команда', 'Описание']);
            $commands = ZFE_Console_CommandBroker::getInstance()->getCommands();
            foreach ($commands as $command) {
                $table->addRow([
                    $command::getName(),
                    $command::getDescription(),
                ]);
            }
            $table->render();
        }
    }
}
