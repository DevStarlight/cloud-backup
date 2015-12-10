Add here your credential files. p.e

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
    ]
];