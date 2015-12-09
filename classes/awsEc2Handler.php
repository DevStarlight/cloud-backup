<?php

/**
 * Jesus Huerta Arrabal
 * Twitter: @DevStarlight
 */

use Aws\Ec2\Ec2Client;
use Aws\Ec2\Exception\Ec2Exception;
use Aws\Exception\AwsException;

//http://stackoverflow.com/questions/25404250/transferring-files-between-two-ec2-instances-in-the-same-region
//http://php.net/manual/es/ref.ssh2.php


class AwsEc2Handler
{
    protected $ec2;

    public function __construct()
    {
        $this->ec2 = new Ec2Client([
            'version' => 'latest',
            'region'  => 'us-west-2a',
            'credentials' => [
                'key' => EC2_KEY,
                'secret' => EC2_SECRET
            ]
        ]);
    }

    public function handler($sentence = '')
    {
       try
        {
            $result = $this->ec2->DescribeInstances($sentence);
            $this->printResult($result);
        }
        catch (Ec2Exception $e)
        {
            $this->logs(array($e->getMessage()));
        }
        catch (AwsException $e)
        {
            $this->logs(array($e->getAwsRequestId(), $e->getAwsErrorType(), $e->getAwsErrorCode()));
        }
    }

    public function listInstance($function, $params)
    {
        switch ($function)
        {
            // List all instances without any restriction
            case 'listAllInstances':
                $this->handler();
            break;

            // List all instances under a specific id of instance
            case 'listInstanceById':
                $this->handler(array('InstanceIds' => $params));
            break;

            // List a single instance filtered by an specific name
            case 'listInstanceByName':
                $this->handler(array('Filters' => ['availability-zone' => $params]));
            break;

            // List all instances grouped by a specific security group name
            case 'listInstanceBySecurityGroupName':
                $this->handler(array('Filters' => ['instance.group-name' => $params]));
            break;

            // List a single instance filtered by an specific instance type
            case 'listInstancesByType':
                $this->handler(array('Filters' => ['instance-type' => $params]));
            break;

            // List all instances under a specific region
            case 'listInstancesByRegion':
                $this->handler(array('Filters' => ['availability-zone' => $params]));
            break;
        }
    }

    // Copy an AMI between different regions

    public function copyAMItoRegion($region)
    {
       $result = $client->copyImage([
            'ClientToken' => '<string>',
            'Description' => '<string>',
            'DryRun' => true || false,
            'Name' => '<string>', // REQUIRED
            'SourceImageId' => '<string>', // REQUIRED
            'SourceRegion' => '<string>', // REQUIRED
        ]);
    }
//
//    public function exportInstanceToS3()
//    {
//        $result = $client->createInstanceExportTask([
//            'Description' => '<string>',
//            'ExportToS3Task' => [
//                'ContainerFormat' => 'ova',
//                'DiskImageFormat' => 'VMDK|RAW|VHD',
//                'S3Bucket' => '<string>',
//                'S3Prefix' => '<string>',
//            ],
//            'InstanceId' => '<string>', // REQUIRED
//            'TargetEnvironment' => 'citrix|vmware|microsoft',
//        ]);
//    }

    // Prints all the data for a request

    public function printResult($result)
    {
        $reservations = $result['Reservations'];

        foreach ($reservations as $reservation)
        {
            $instances = $reservation['Instances'];

            foreach ($instances as $instance)
            {
                $instanceName = '';

                foreach ($instance['Tags'] as $tag)
                {
                    if ($tag['Key'] == 'Name')
                    {
                        $instanceName = $tag['Value'];
                    }
                }
                echo 'Instance Name: ' . $instanceName . PHP_EOL;
                echo '<br>';
                echo '---> State: ' . $instance['State']['Name'] . PHP_EOL;
                echo '<br>';
                echo '---> Instance ID: ' . $instance['InstanceId'] . PHP_EOL;
                echo '<br>';
                echo '---> Image ID: ' . $instance['ImageId'] . PHP_EOL;
                echo '<br>';
                echo '---> Private Dns Name: ' . $instance['PrivateDnsName'] . PHP_EOL;
                echo '<br>';
                echo '---> Instance Type: ' . $instance['InstanceType'] . PHP_EOL;
                echo '<br>';
                echo '---> Security Group: ' . $instance['SecurityGroups'][0]['GroupName'] . PHP_EOL;
                echo '<br>';
                echo '-----------------------------------------------------------------------------------------------------';
                echo '<br>';
                echo '<br>';
            }
        }
    }

    public function logs($err)
    {
        $log = array(
            date("Y-m-d H:i:s"),
            json_encode($err),
            PHP_EOL, PHP_EOL
        );

        file_put_contents(LOG_PATH, $log, FILE_APPEND);
    }
}

?>
