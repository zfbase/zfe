<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Валидатор цвета.
 *
 * Поддерживаются названия цветов и HEX.
 * HEX должен начинаться с символа # и содержать 3 или 6 символов.
 */
class ZFE_Validate_Color extends Zend_Validate_Abstract
{
    const INVALID = 'colorInvalid';
    const NOT_COLOR = 'notColor';

    /**
     * Сообщения об ошибках.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID => 'Некорректный тип. Должна быть строка',
        self::NOT_COLOR => "'%value%' has not only hexadecimal digit characters",
    ];

    /**
     * Именованные цвета.
     *
     * @var array
     */
    protected $_colors = [
        'aliceblue',       'antiquewhite',          'aqua',               'aquamarine',       'azure',
        'beige',           'bisque',                'black',              'blanchedalmond',   'blue',
        'blueviolet',      'brown',                 'burlywood',          'cadetblue',        'chartreuse',
        'chocolate',       'coral',                 'cornflowerblue',     'cornsilk',         'crimson',
        'cyan',            'darkblue',              'darkcyan',           'darkgoldenrod',    'darkgray',
        'darkgreen',       'darkkhaki',             'darkmagenta',        'darkolivegreen',   'darkorange',
        'darkorchid',      'darkred',               'darksalmon',         'darkseagreen',     'darkslateblue',
        'darkslategray',   'darkturquoise',         'darkviolet',         'deeppink',         'deepskyblue',
        'dimgray',         'dodgerblue',            'firebrick',          'floralwhite',      'forestgreen',
        'fuchsia',         'gainsboro',             'ghostwhite',         'gold',             'goldenrod',
        'gray',            'green',                 'greenyellow',        'honeydew',         'hotpink',
        'indianred',       'indigo',                'ivory',              'khaki',            'lavender',
        'lavenderblush',   'lawngreen',             'lemonchiffon',       'lightblue',        'lightcoral',
        'lightcyan',       'lightgoldenrodyellow',  'lightgray',          'lightgreen',       'lightpink',
        'lightsalmon',     'lightseagreen',         'lightskyblue',       'lightslategray',   'lightsteelblue',
        'lightyellow',     'lime',                  'limegreen',          'linen',            'magenta',
        'maroon',          'mediumaquamarine',      'mediumblue',         'mediumorchid',     'mediumpurple',
        'mediumseagreen',  'mediumslateblue',       'mediumspringgreen',  'mediumturquoise',  'mediumvioletred',
        'midnightblue',    'mintcream',             'mistyrose',          'moccasin',         'navajowhite',
        'navy',            'oldlace',               'olive',              'olivedrab',        'orange',
        'orangered',       'orchid',                'palegoldenrod',      'palegreen',        'paleturquoise',
        'palevioletred',   'papayawhip',            'peachpuff',          'peru',             'pink',
        'plum',            'powderblue',            'purple',             'rebeccapurple',    'red',
        'rosybrown',       'royalblue',             'saddlebrown',        'salmon',           'sandybrown',
        'seagreen',        'seashell',              'sienna',             'silver',           'skyblue',
        'slateblue',       'slategray',             'snow',               'springgreen',      'steelblue',
        'tan',             'teal',                  'thistle',            'tomato',           'turquoise',
        'violet',          'wheat',                 'white',              'whitesmoke',       'yellow',
        'yellowgreen',
    ];

    /**
     * Отвечает на вопрос: переданное значение является допустимым цветом?
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        if (!in_array(mb_strtolower($value), $this->_colors)) {
            $len = mb_strlen($value);
            if ('#' !== $value[0] || !(4 == $len || 7 == $len) || !ctype_xdigit(mb_substr($value, 1))) {
                $this->_error(self::NOT_COLOR);
                return false;
            }
        }

        return true;
    }
}
