<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_AclTest extends PHPUnit_Framework_TestCase
{
    protected static $_config;

    protected function setUp()
    {
        if ( ! self::$_config) {
            self::$_config = new Zend_Config_Ini(RESOURCES_PATH . '/Acl/config.ini');
        }
    }

    public function testHasResource()
    {
        Zend_Registry::set('user', (object) [
            'role' => 'admin',
        ]);

        $acl = new ZFE_Acl(self::$_config);

        $this->assertTrue($acl->hasResource('error'));
        $this->assertTrue($acl->hasResource('auth'));
        $this->assertTrue($acl->hasResource('editors'));
        $this->assertFalse($acl->hasResource('undefined'));
    }

    /**
     * @dataProvider providerRoles
     *
     * @param string $role
     * @param bool   $test1
     * @param bool   $test2
     * @param bool   $test3
     * @param bool   $test4
     * @param bool   $test5
     */
    public function testIsAllowedMe($role, $test1, $test2, $test3, $test4, $test5)
    {
        Zend_Registry::set('user', (object) [
            'role' => $role,
        ]);

        $acl = new ZFE_Acl(self::$_config);

        $this->assertEquals($test1, $acl->isAllowedMe());
        $this->assertEquals($test2, $acl->isAllowedMe('error'));
        $this->assertEquals($test3, $acl->isAllowedMe('auth'));
        $this->assertEquals($test4, $acl->isAllowedMe('auth', 'logout'));
        $this->assertEquals($test5, $acl->isAllowedMe('editors'));
    }

    public function providerRoles()
    {
        return [
            ['guest',  false, true, false, false, false],
            ['editor', false, true, false, true,  false],
            ['admin',  true,  true, true,  true,  true],
        ];
    }
}
