<?php

/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */

class ZFE_Uploads
{
    protected static function find(array $params)
    {
        return Uploads::findBySeveralParams($params, null, true) ?: null;
    }

    public static function validateSatusParams($params)
    {
        $params = array_intersect_key($params, array_flip([
            'hash',
            'size',
        ]));
        if (empty($params['hash'])) {
            return 'querystring.hash should be string';
        }
        return null;
    }

    public static function status(Clients $client, array $params)
    {
        $params['hash'] = strtolower($params['hash']);
        $item = static::find(['hash' => $params['hash']]);
        if ( ! $item) {
            return [
                'exists' => false,
                'size' => null,
                'completed' => false,
            ];
        }
        $uploaded = $item->size === $item->uploaded_size;
        $actualHash = $item->getUploadedDataHash();
        $sameHash = $uploaded && ($actualHash === ($item->proxy_hash ?: $item->hash));
        $res = [
            'exists' => true,
            'size' => (int) $item->uploaded_size,
            'completed' => $uploaded && $sameHash,
            'badHash' => $uploaded && ! $sameHash,
            '_deprecated_actualHash' => $uploaded ? $actualHash : null,
        ];

        if ($item->size === $item->uploaded_size && ! $item->is_completed) {
            if ($sameHash) {
                $item->is_completed = 1;
                $item->save();
                $item->analyze();
            }
        }

        return $res;
    }

    public static function validateUploadParams($params)
    {
        $params = array_intersect_key($params, array_flip([
            'baseDir',
            'filePath',
            'fileModified',
            'hash',
            'isManual',
            'size',
            'transHash',
            'transQuality',
            'caseNumber',
            'caseDate',
            'recordDate',
            'fileNumber',
            'channelNumber',
        ]));
        if (empty($params['hash'])) {
            return 'querystring.hash should be a string';
        }
        if (empty($params['size']) || (string) ((int) ($params['size'])) !== $params['size'] || $params['size'] < 1) {
            return 'querystring.size should be a positive integer';
        }
        return null;
    }

    protected static function parseDate($value)
    {
        $t = strtotime($value);
        return $t ? date('Y-m-d H:i:s', $t) : null;
    }

    public static function upload(Clients $client, array $params, string $body)
    {
        $params['hash'] = strtolower($params['hash']);
        $size = (int) $params['size'];
        $item = static::find([
            'client_id' => $client->id,
            'hash' => $params['hash'],
        ]);
        if ( ! $item) {
            $item = new Uploads();
            $item->client_id = $client->id;
            $item->hash = $params['hash'];
            $item->size = $size;
            $item->datetime_created = date('Y-m-d H:i:s');
            $item->datetime_edited = date('Y-m-d H:i:s');
            $item->uploaded_size = 0;
            $item->datetime_modified = isset($params['fileModified']) ? static::parseDate($params['fileModified']) : null;
            $item->is_manual = isset($params['isManual']) && 'true' === $params['isManual'] ? 1 : 0;
            $item->file_path = $params['filePath'] ?? null;
            $item->base_dir = $params['baseDir'] ?? null;

            $item->case_num = $params['caseNumber'] ?? null;
            $item->case_date = isset($params['caseDate']) ? static::parseDate($params['caseDate']) : null;
            $item->date_rec = isset($params['recordDate']) ? static::parseDate($params['recordDate']) : null;
            $item->rec_order = $params['fileNumber'] ?? null;
            $item->rec_channel = $params['channelNumber'] ?? null;

            $item->save();
        }

        $offset = isset($params['offset']) ? $params['offset'] : null;
        $firstChunk = 1 === $offset && 0 === $item->is_completed;

        if ($firstChunk) {
            $item->quality = $params['transQuality'] ?? null;
            $item->proxy_hash = $params['transHash'] ?? null;
            $item->save();
        }

        if ($item->size > $item->uploaded_size || $firstChunk) {
            $item->append($body, $offset);
            $item->uploaded_size = $item->filesize();
            $item->datetime_edited = date('Y-m-d H:i:s');
            $item->save();
        }

        $res = [
            'size' => (int) $item->uploaded_size,
            'hash' => null,
            'completed' => (bool) $item->is_completed,
        ];

        if ($item->size === $item->uploaded_size && ! $item->is_completed) {
            $res['hash'] = $item->getUploadedDataHash();
            if ($res['hash'] === ($item->proxy_hash ?: $item->hash)) {
                $res['completed'] = true;
                $item->is_completed = 1;
                $item->save();
                $item->analyze();
            } else {
                $res['badHash'] = true;
            }
        }

        return $res;
    }

    public static function deleteAll()
    {
        $items = Doctrine_Query::create()
            ->from('Uploads')
            ->select('*')
            ->execute();
        foreach ($items as $item) {
            $filename = $item->getStorageFilename();
            @unlink($filename);
        }
        ZFE_Query::create()
            ->setHard(true)
            ->from('Uploads')
            ->delete()
            ->execute();
    }
}
