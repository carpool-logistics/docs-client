<?php

namespace Helium\Docs\Client;

use GuzzleHttp\Client;
use Helium\Docs\Client\Builders\HtmlToPdfBuilder;
use Helium\Docs\Client\Exceptions\ConfigurationException;

class DocsClient
{
    //region Base
    protected const BASE_URI = 'https://docs.heliumservices.com';

    protected static $apiKey;
    protected static $defaultS3Bucket;

    protected static $client;

    public static function setApiKey(string $apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function setDefaultS3Bucket(string $s3Bucket)
    {
        self::$defaultS3Bucket = $s3Bucket;
    }

    protected static function getApiKey()
    {
        if (self::$apiKey) {
            return self::$apiKey;
        } else {
            throw new ConfigurationException('No API Key is configured! Configure an API key with DocsClient::setApiKey(\'key\'), or set the DOCS_CLIENT_API_KEY env variable.');
        }
    }
    //endregion

    //region Helpers
    protected static function client(): Client
    {
        return is_null(self::$client) ? new Client([
            'base_uri' => self::BASE_URI
        ]) : self::$client;
    }
    //endregion

    //region Actions
    public static function htmlToPdf(string $html): HtmlToPdfBuilder
    {
        $builder = (new HtmlToPdfBuilder(self::client(), self::getApiKey()))
            ->addFile($html);

        if (self::$defaultS3Bucket) {
            $builder->setS3Bucket(self::$defaultS3Bucket);
        }

        return $builder;
    }
    //endregion
}