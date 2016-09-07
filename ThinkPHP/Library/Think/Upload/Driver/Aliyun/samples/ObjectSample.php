<?php
require_once dirname(__DIR__).'/aliyun.php';

use Aliyun\OSS\OSSClient;

// Sample of create client
function createClient($accessKeyId, $accessKeySecret) {
    return OSSClient::factory(array(
        'AccessKeyId' => $accessKeyId,
        'AccessKeySecret' => $accessKeySecret,
    ));
}

function listObjects(OSSClient $client, $bucket) {
    $result = $client->listObjects(array(
        'Bucket' => $bucket,
    ));
    foreach ($result->getObjectSummarys() as $summary) {
        echo 'Object key: ' . $summary->getKey() . "\n";
    }
}

// Sample of put object from string
function putStringObject(OSSClient $client, $bucket, $key, $content) {
    $result = $client->putObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
        'Content' => $content,
    ));
    echo 'Put object etag: ' . $result->getETag();
}

// Sample of put object from resource
function putResourceObject(OSSClient $client, $bucket, $key, $content, $size) {
    $result = $client->putObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
        'Content' => $content,
        'ContentLength' => $size,
    ));
    var_dump($result);
    echo 'Put object etag: ' . $result->getETag();
    return $result->getETag();
}

// Sample of get object
function getObject(OSSClient $client, $bucket, $key) {
    $object = $client->getObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
    ));

    echo "Object: " . $object->getKey() . "\n";
    echo (string) $object;
}

// Sample of delete object
function deleteObject(OSSClient $client, $bucket, $key) {
    $client->deleteObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
    ));
}

$keyId = 'LrrnWwMV4W5c8vn1';

$keySecret = 'lr84aa5YK5fKGuSaYPPwC0162X3i6c';

$client = createClient($keyId, $keySecret);

$bucket = 'wanka-file';

$path = '';
$fileName = 'HjWanKa_1.6.0.1_90001a.APK';

//putStringObject($client, $bucket, $key, '123');
var_dump(filesize('HjWanKa_1.6.0.1_90001a.APK'));
$ret = putResourceObject($client, $bucket, $fileName, fopen('HjWanKa_1.6.0.1_90001a.APK', 'r'), filesize('HjWanKa_1.6.0.1_90001a.APK'));

//getObject($client, $bucket, $fileName);

//deleteObject($client, $bucket, $key);
