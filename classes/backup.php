<?php
/**
 * Jesus Huerta Arrabal
 * Twitter: @DevStarlight
 *
 * http://phpseclib.sourceforge.net/
 * http://stackoverflow.com/questions/22502016/phpseclib-stfp-check-directory-exists
 */

require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'amazon'.DIRECTORY_SEPARATOR.'autoload.php');
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'awsS3Handler.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'phpseclib'.DIRECTORY_SEPARATOR.'Crypt/Base.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'phpseclib'.DIRECTORY_SEPARATOR.'Math/BigInteger.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'phpseclib'.DIRECTORY_SEPARATOR.'Net/SFTP.php');
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'phpseclib'.DIRECTORY_SEPARATOR.'Crypt/RSA.php');

class Backup
{
    protected $_s3;

    public function __construct($options)
    {
        if (count($options) === 1)
        {
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsites' . DIRECTORY_SEPARATOR . $options['microsite'] . '.php');
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['ipInstance'] . '.php');
            require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsite' . DIRECTORY_SEPARATOR . $options['idInstance'] . '.php');

            $this->backupAll();
        }
        else
        {
            if (isset($options['microsite']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'microsites' . DIRECTORY_SEPARATOR . $options['microsite'] . '.php');
            }
            else if (isset($options['ipInstance']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'ipInstances' . DIRECTORY_SEPARATOR . $options['ipInstance'] . '.php');
            }
            else if (isset($options['idInstance']))
            {
                require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR . 'idInstances' . DIRECTORY_SEPARATOR . $options['idInstance'] . '.php');
            }

            $this->backupSelected($object);
        }
    }

    public function backupAll(){}

    public function getFiles($object, $save_folder, &$sftp)
    {
        foreach ($object as $key => $value)
        {
            if (is_array($object[$key]))
            {
                if (!file_exists($save_folder . '/' . $key))
                {
                    mkdir($save_folder . '/' . $key, 0777);
                }

                $this->getFiles($object[$key], $save_folder . '/' . $key, $sftp);
            }
            else
            {
                $sftp->get($value, $save_folder . '/' . end(explode('/',$value)));
            }
        }
    }

    public function getDirectories($object, $save_folder, &$sftp)
    {
        foreach ($object as $key => $value)
        {
            if (!file_exists($save_folder . '/' . end(explode('/',$value))))
            {
                mkdir($save_folder . '/' . end(explode('/',$value)), 0777);
            }

            $list = $sftp->nlist($value);

            foreach ($list as $key2 => $value2)
            {
//                if ($sftp->chdir($value))
//                {
//                    $this->getDirectories($object[$key2], $save_folder . '/' . end(explode('/',$value)) . '/' . $value2, $sftp);
//                }
//                else
//                {
                    if ($value2 !== '.' && $value2 !== '..')
                    {
                        $sftp->get($value . '/' . $value2, $save_folder . '/' . end(explode('/',$value)) . '/' . $value2);
                    }
//                }
            }

        }
    }

    public function rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (filetype($dir."/".$object) == "dir")
                    {
                        $this->rrmdir($dir."/".$object);
                    }
                    else
                    {
                        unlink($dir."/".$object);
                    }
                }
            }

            reset($objects);

            rmdir($dir);
        }
    }

    public function backupSelected($object)
    {
        $sftp = new Net_SFTP($object['connection']['host']);

        $key = new Crypt_RSA();

        $key->loadKey(file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'credentials' . DIRECTORY_SEPARATOR . $object['connection']['private_key']));

        if (!$sftp->login($object['connection']['user'], $key))
        {
            exit('Login Failed');
        }

        $save_path = SAVE_PATH . date("m_d_y") . '/' . $object['connection']['name'];

        if (file_exists($save_path))
        {
            exit('There is already a folder with that name');
        }
//
        mkdir ($save_path, 0777, true);

        $arr_exclude = ['connection', 'ec2', 's3'];

        foreach ($object as $key => $value)
        {
            if (!in_array($key, $arr_exclude))
            {
                mkdir($save_path . '/' . $key, 0777);

                if ('directories' === $key)
                {
                    $this->getDirectories($object[$key], $save_path . '/' . $key, $sftp);
                }
                else
                {
                    $this->getFiles($object[$key], $save_path . '/' . $key, $sftp);
                }
            }

        }

        $this->_s3 = new AwsS3Handler($object['s3']['s3_key'], $object['s3']['s3_secret']);

        $this->_s3->uploadToBucket(S3_BUCKET, S3_PATH . '/' . date("m_d_y"), SAVE_PATH . date("m_d_y"));

        $this->rrmdir(SAVE_PATH . date("m_d_y"));
    }
}
