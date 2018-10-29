<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

/**
 * Помощник преобразования POST- данные в ЧПУ.
 *
 * @category  ZFE
 */
class ZFE_Controller_Action_Helper_PostToGet extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Преобразовать POST- данные в ЧПУ.
     *
     * @param array $ignore
     */
    public function direct(array $ignore = ['submit'])
    {
        $this->_ignore = $ignore;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = [];
            $get = [];

            foreach ($request->getQuery() as $var => $val) {
                $get[] = $var . '=' . urlencode($val);
            }

            $this->_recursiveParser($request->getPost(), $ret, $get);

            $module = $request->getModuleName();
            if ($module) {
                $parts[] = $module;
            }
            $parts[] = $request->getControllerName();
            $parts[] = $request->getActionName();

            $redirectTo = '/' . implode('/', $parts) . '/' . implode('/', $ret);
            if ( ! empty($get)) {
                $redirectTo .= '?' . implode('&', $get);
            }

            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl($redirectTo);
        }
    }

    /**
     * Игнорируемые параметры.
     *
     * @var array
     */
    protected $_ignore;

    /**
     * Рекурсивно перебрать параметры и составить массивы компонентов GET-запроса.
     *
     * @param array  $data
     * @param array  $ret
     * @param array  $get
     * @param string $prefix
     */
    protected function _recursiveParser(array $data, array &$ret, array &$get, $prefix = '')
    {
        if ( ! empty($prefix)) {
            $prefix .= '_';
        }

        foreach ($data as $key => $value) {
            $key = $prefix . $key;

            if (in_array($key, $this->_ignore, true)) {
                continue;
            }

            if (is_array($value)) {
                $this->_recursiveParser($value, $ret, $get, $key);
            } elseif (is_string($value) && (false !== strpos($value, '/') || false !== strpos($value, '\\') || false !== strpos($value, '.'))) {
                // Apache, в целях безопасности, встречая в адресе (до "?") символ %2F (/) или %2С (\) возвращет 404 ошибку
                $get[] = $key . '=' . urlencode($value);
            } elseif ( ! empty($value) || '0' === $value) {
                $ret[] = urlencode($key);
                $ret[] = urlencode($value);
            }
        }
    }
}
