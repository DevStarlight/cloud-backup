Add here your credential files

$object = [
    'connection' => [
        'name' => 'ixxxxxxxx',
        'host' => 'ec2-xx-xx-xxx-xxx.eu-west-2.compute.amazonaws.com', // IP
        'private_key' => 'yourkey.ppk',
        'user' => 'youruser'
    ],
    'directories' => [
        '/etc/apache2/your_directory'
    ],
    'files' => [
        'sites-available' => [
            '/etc/apache2/sites-available/your_file.conf',
        ]
    ],
    'ec2' => [
        'ec2_key' => 'XXXXXXXXXXXXXXXX',
        'ec2_secret' => 'XxXxxxXxxXXXxXxXXxxxxxxXxXXxXxx'
        
    ],
    's3' => [
        's3_key' => 'XXXXXXXXXXXXXXXX',
        's3_secret' => 'XxXxxxXxxXXXxXxXXxxxxxxXxXXxXxx'
    ]
];