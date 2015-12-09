<?php

/**
 * Jesus Huerta Arrabal
 * Twitter: @DevStarlight
 */

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;

class AwsS3Handler
{
    protected $_s3;

    public function __construct($key, $secret)
    {
        $this->_s3 = new S3Client([
            'credentials' => [
                'key' => $key,
                'secret' => $secret
            ],
            'region'  => 'eu-west-1',
            'version' => 'latest'
        ]);
    }

    public function uploadToBucket($bucket, $keyPrefix, $directory)
    {
        try
        {
            $this->_s3->uploadDirectory($directory, $bucket, $keyPrefix, array(
                'params'      => array('ACL' => 'public-read'),
                'concurrency' => 20,
                'debug'       => true
            ));
        }
        catch (S3Exception $e)
        {
//            $this->logs(array($e->getMessage()));
        }
        catch (AwsException $e)
        {
//            $this->logs(array($e->getAwsRequestId(), $e->getAwsErrorType(), $e->getAwsErrorCode()));
        }
    }
}

?>
