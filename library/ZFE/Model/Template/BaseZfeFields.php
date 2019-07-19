<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Стандартные служебные поля ZFE.
 * Шаблон для использования при YAML-основанном моделировании.
 */
class ZFE_Model_Template_BaseZfeFields extends Doctrine_Template
{
    protected $_options = [
        'version' => [
            'name' => 'version',
            'type' => 'integer',
            'length' => 4,
            'options' => [
                'unsigned' => true,
                'notnull' => true,
                'default' => '1',
                'comment' => 'Версия',
            ],
            'disabled' => false,
        ],
        'creator_id' => [
            'name' => 'creator_id',
            'type' => 'integer',
            'length' => 4,
            'options' => [
                'unsigned' => true,
                'comment' => 'Создал',
            ],
            'disabled' => false,
        ],
        'editor_id' => [
            'name' => 'editor_id',
            'type' => 'integer',
            'length' => 4,
            'options' => [
                'unsigned' => true,
                'comment' => 'Последним изменил',
            ],
            'disabled' => false,
        ],
        'datetime_created' => [
            'name' => 'datetime_created',
            'type' => 'timestamp',
            'length' => '25',
            'options' => [],
            'disabled' => false,
            'comment' => 'Дата и время создания',
        ],
        'datetime_edited' => [
            'name' => 'datetime_edited',
            'type' => 'timestamp',
            'length' => '25',
            'options' => [],
            'disabled' => false,
            'comment' => 'Дата и время последнего изменения',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'integer',
            'length' => 1,
            'options' => [
                'unsigned' => true,
                'notnull' => true,
                'default' => '0',
                'comment' => 'Удалено',
            ],
            'disabled' => false,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'integer',
            'length' => 1,
            'options' => [
                'unsigned' => true,
                'notnull' => true,
                'default' => '0',
                'comment' => 'Статус',
            ],
            'disabled' => false,
        ],
    ];

    public function setTableDefinition()
    {
        $columns = [
            'version',
            'creator_id',
            'editor_id',
            'datetime_created',
            'datetime_edited',
            'deleted',
            'status',
        ];
        foreach ($columns as $column) {
            $options = $this->_options[$column];
            if ($options && !$options['disabled']) {
                $name = $options['name'];
                if ($options['alias'] ?? false) {
                    $name .= ' as ' . $options['alias'];
                }
                $this->hasColumn($name, $options['type'], $options['length'], $options['options']);
            }
        }
    }

    public function setUp()
    {
        $config = Zend_Registry::get('config');

        if ($this->_options['creator_id'] && !$this->_options['creator_id']['disabled']) {
            $this->hasOne($config->userModel . ' as Creator', [
                'local' => $this->_options['creator_id']['name'],
                'foreign' => 'id',
                'onUpdate' => 'cascade',
                'onDelete' => 'set null',
            ]);
        }

        if ($this->_options['editor_id'] && !$this->_options['editor_id']['disabled']) {
            $this->hasOne($config->userModel . ' as Editor', [
                'local' => $this->_options['editor_id']['name'],
                'foreign' => 'id',
                'onUpdate' => 'cascade',
                'onDelete' => 'set null',
            ]);
        }
    }
}
