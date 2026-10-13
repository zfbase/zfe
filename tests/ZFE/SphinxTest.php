<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

use Foolz\SphinxQL\Drivers\ConnectionInterface;
use PHPUnit\Framework\TestCase;

class ZFE_SphinxTest extends TestCase
{
    /** @var string */
    private $confPath;

    protected function setUp(): void
    {
        $this->confPath = tempnam(sys_get_temp_dir(), 'zfe-sphinx-');
        $this->writeConf(['rt_field = title', 'rt_attr_uint = attr_id', 'rt_attr_multi = tags', 'rt_attr_string = code']);

        Zend_Registry::set('config', new Zend_Config([
            'sphinx' => [
                'config' => $this->confPath,
                'conn' => ['host' => '127.0.0.1', 'port' => 9306],
                'index' => ['Common' => 'common_rt'],
                'tags' => ['Videos' => 'video'],
            ],
        ]));
        ZFE_Sphinx::clearRtIndexSchemaCache();
    }

    protected function tearDown(): void
    {
        ZFE_Sphinx::clearRtIndexSchemaCache();
        @unlink($this->confPath);
    }

    private function writeConf(array $options)
    {
        $body = "index videos_rt\n{\n    type = rt\n    path = /tmp/videos_rt\n    " . implode("\n    ", $options) . "\n}\n";
        file_put_contents($this->confPath, $body);
    }

    /**
     * Соединение, которое запоминает отправленные запросы.
     *
     * @param array $sent
     *
     * @return ConnectionInterface
     */
    private function recordingConnection(array &$sent)
    {
        $conn = $this->createStub(ConnectionInterface::class);
        $conn->method('quoteArr')->willReturnCallback(function (array $values) {
            return array_map(function ($v) {
                return is_int($v) ? (string) $v : "'{$v}'";
            }, $values);
        });
        $conn->method('query')->willReturnCallback(function ($sql) use (&$sent) {
            $sent[] = $sql;
            return null;
        });

        return $conn;
    }

    public function testGetRtIndexSchemaReadsConfig()
    {
        $this->assertEquals([
            'id' => 'rt_attr_uint',
            'title' => 'rt_field',
            'attr_id' => 'rt_attr_uint',
            'tags' => 'rt_attr_multi',
            'code' => 'rt_attr_string',
        ], ZFE_Sphinx::getRtIndexSchema('videos_rt'));
    }

    public function testGetRtIndexSchemaIsCachedUntilCleared()
    {
        $this->assertArrayHasKey('title', ZFE_Sphinx::getRtIndexSchema('videos_rt'));

        $this->writeConf(['rt_field = name']);
        $this->assertArrayHasKey('title', ZFE_Sphinx::getRtIndexSchema('videos_rt'), 'схема берется из кеша');

        ZFE_Sphinx::clearRtIndexSchemaCache();
        $schema = ZFE_Sphinx::getRtIndexSchema('videos_rt');
        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayNotHasKey('title', $schema);
    }

    public function testGetRtIndexSchemaOfUnknownIndex()
    {
        $this->expectException(ZFE_Exception::class);
        $this->expectExceptionMessage($this->confPath);

        ZFE_Sphinx::getRtIndexSchema('unknown_rt');
    }

    public function testFilterIndexData()
    {
        // Колонка вне схемы не должна давать предупреждение «Undefined array key».
        set_error_handler(function ($severity, $message) {
            if (!($severity & (E_WARNING | E_NOTICE))) {
                return false;
            }
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $filtered = ZFE_Sphinx::filterIndexData('videos_rt', [
                'id' => '5',
                'attr_id' => '7',
                'title' => null,
                'tags' => '1,2,3',
                'code' => null,
                'not_in_schema' => 'as is',
            ]);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([
            'id' => 5,
            'attr_id' => 7,
            'title' => '',
            'tags' => [1, 2, 3],
            'code' => '',
            'not_in_schema' => 'as is',
        ], $filtered);

        $filtered = ZFE_Sphinx::filterIndexData('videos_rt', ['tags' => '']);
        $this->assertSame(['tags' => []], $filtered);
    }

    public function testCommonIndexRow()
    {
        $row = ZFE_Sphinx::commonIndexRow('Videos', [
            'id' => 10,
            'attr_id' => 10,
            'attr_deleted' => 0,
            'title' => 'Заголовок',
            'code' => null,
        ]);

        $this->assertSame([
            'id' => 10,
            'attr_id' => 10,
            'attr_deleted' => 0,
            'model' => 'Videos',
            'tags' => 'video',
            'title' => 'Заголовок',
            'code' => '',
            'details' => '',
            'persons' => '',
            'id_number' => '',
        ], $row);
    }

    public function testReplaceIndexDataBatchSendsOneQueryForUniformRows()
    {
        $sent = [];
        $conn = $this->recordingConnection($sent);

        ZFE_Sphinx::replaceIndexDataBatch('videos_rt', [
            ['id' => 1, 'title' => 'a'],
            ['id' => 2, 'title' => 'b'],
        ], $conn);

        $this->assertSame(["REPLACE INTO videos_rt (id, title) VALUES (1, 'a'), (2, 'b')"], $sent);
    }

    public function testReplaceIndexDataBatchGroupsRowsByColumns()
    {
        $sent = [];
        $conn = $this->recordingConnection($sent);

        ZFE_Sphinx::replaceIndexDataBatch('videos_rt', [
            ['id' => 1, 'title' => 'a'],
            ['id' => 2],
            ['id' => 3, 'title' => 'c'],
        ], $conn);

        $this->assertSame([
            "REPLACE INTO videos_rt (id, title) VALUES (1, 'a'), (3, 'c')",
            'REPLACE INTO videos_rt (id) VALUES (2)',
        ], $sent);
    }

    public function testReplaceIndexDataBatchIgnoresEmptyRows()
    {
        $sent = [];
        $conn = $this->recordingConnection($sent);

        ZFE_Sphinx::replaceIndexDataBatch('videos_rt', [], $conn);

        $this->assertSame([], $sent);
    }

    public function testCommonIndexConnectionIsSeparateAndShared()
    {
        $common = ZFE_Sphinx::commonIndexConnection();

        $this->assertInstanceOf(ConnectionInterface::class, $common);
        $this->assertSame($common, ZFE_Sphinx::commonIndexConnection());
        $this->assertNotSame($common, ZFE_Sphinx::connection());
    }
}
