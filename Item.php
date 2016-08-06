<?php

namespace dmleach\dynamophp;

class Item
{
    private $db = null;

    /**
     * @param SimpleAmazonSTS $service
     */
    public static function createUsingTokenService($service)
    {
        $item = new Item();

        $token = $service->call('GetSessionToken', [
            'DurationSeconds' => $service->tokenDuration
        ]);

        $item->db = new \dmleach\dynamophp\SimpleAmazonDynamoDB(
            $token['GetSessionTokenResult']['Credentials']['AccessKeyId'],
            $token['GetSessionTokenResult']['Credentials']['SecretAccessKey'],
            $token['GetSessionTokenResult']['Credentials']['SessionToken']
        );

        return $item;
    }

    public function fetch($table, $keys)
    {
        $formattedKeys = [];

        foreach ($keys as $key => $value) {
            $code = $this->getValueTypeCode($value);
            $formattedKeys[$key] = [$code => $value];
        }

        $rowFetch = $this->db->call(
            'GetItem',
            [
                'TableName' => $table,
                'Key' => $formattedKeys,
            ]
        );

        return $rowFetch;
    }

    public function getValueTypeCode($value)
    {
        // Since anything can be a string, use that as the default
        $code = 'S';

        return $code;
    }
}
