<?php

namespace Helium\Docs\Client\Builders;

use GuzzleHttp\Client;
use Helium\Docs\Client\Exceptions\ConfigurationException;

class HtmlToPdfBuilder
{
    //region Base
    protected $client;
    protected $apiKey;

    protected $files = [];
    protected $maxDelay = 10000;
    protected $s3Bucket;

    public function __construct(Client $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }
    //endregion

    //region Getters/Setters
    public function setMaxDelay(int $maxDelay)
    {
        $this->maxDelay = $maxDelay;
    }

    public function setS3Bucket(string $s3Bucket)
    {
        $this->s3Bucket = $s3Bucket;
    }

    protected function getS3Bucket()
    {
        if ($this->s3Bucket) {
            return $this->s3Bucket;
        } elseif (function_exists('config') && $bucket = config('filesystems.disks.s3.bucket')) {
            return $bucket;
        } else {
            throw new ConfigurationException('No S3 bucket is configured! Configure an S3 bucket with $builder->setS3Bucket(\'bucket\'), DocsClient::setDefaultS3Bucket(\'bucket\'), or using the Laravel filesystems configuration.');
        }
    }

    public function addFile(string $html)
    {
        $dom = new \DOMDocument();
        $dom->loadHtml($html);

        $head = $dom->getElementsByTagName('head');
        $style = $dom->createElement('style', "@page {margin: 15; size: auto; background-color: white;}");

        if (count($head) == 0) {
            $head = $dom->createElement('head');
            $dom->appendChild($head);
        } else {
            $head = $head[0];
        }

        $head->appendChild($style);

        $this->files[] = $dom->saveHTML();

        return $this;
    }
    //endregion

    //region Actions
    public function getContents(): string
    {
        $formParams = [
            'mode' => 'file',
            'delay' => $this->maxDelay
        ];

        foreach ($this->files as $i => $file) {
            $formParams["files[$i]"] = $file;
        }

        $response = $this->client
            ->post("api/batchConvert/$this->apiKey/html/pdf", [
                'form_params' => $formParams
            ]);

        return $response->getBody()->getContents();
    }

    public function saveToS3(string $path)
    {
        $formParams = [
            'mode' => 's3',
            'delay' => $this->maxDelay,
            'bucket_name' => $this->getS3Bucket(),
            'folder_name' => '',
            'output_destination' => $path
        ];

        foreach ($this->files as $i => $file) {
            $formParams["files[$i]"] = $file;
        }

        $response = $this->client
            ->post("api/batchConvert/$this->apiKey/html/pdf", [
                'form_params' => $formParams
            ]);

        return $response->getBody()->getContents();
    }
    //endregion
}