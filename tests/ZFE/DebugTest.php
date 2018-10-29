<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_DebugTest extends PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        Zend_Debug::setSapi('apache');

        $result = ZFE_Debug::dump('string', null, false);
        $expected = "<pre class=\"zfe-dump\"><code>string(6) &quot;string&quot;\n</code></pre>";
        $this->assertEquals($expected, $result);
    }

    public function testSql()
    {
        // Проверка связности
        $result = ZFE_Debug::sql('SELECT * FROM Editors', false);
        $this->assertNotNull($result);
    }
}
