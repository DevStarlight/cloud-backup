<?php

/**
 * Jesus Huerta Arrabal
 * Twitter: @DevStarlight
 */

require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'amazon'.DIRECTORY_SEPARATOR.'autoload.php');
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'awsS3Handler.php');

class Restore
{
    protected $s3;

    public function __construct($options)
    {
        $this->s3 = new AwsS3Handler();

        if (count($options) === 1)
        {
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsites' . DIRECTORY_SEPARATOR . $options['microsite'] . '.php');
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['ipInstance'] . '.php');
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['idInstance'] . '.php');

            $this->restoreAll();
        }
        else
        {
            if (isset($options['microsite']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsites' . DIRECTORY_SEPARATOR . $options['microsite'] . '.php');
            }
            else if (isset($options['ipInstance']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['ipInstance'] . '.php');
            }
            else if (isset($options['idInstance']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['idInstance'] . '.php');
            }

            $this->restoreSelected($object);
        }
    }

    public function restoreAll(){}
    public function restoreSelected($object)
    {
        $this->s3->uploadDirectoryToBucket($object['bucket'], $object['keyPrefix'], $object['directories']);
        $this->s3->uploadFilesToBucket($object['bucket'], $object['keyPrefix'], $object['files']);
    }
}
