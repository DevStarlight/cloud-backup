This program request some basic configurations in the server side:

    - OpenSSL.
    
    - ssh2:
    
        Installing on Ubuntu 14.0.4

        sudo pecl channel-update pecl.php.net
        sudo apt-get install libssh2-1-dev
        sudo pecl install -a ssh2-0.12
        echo 'extension=ssh2.so' | sudo tee /etc/php5/mods-available/ssh2.ini > /dev/null
        sudo php5enmod ssh2
        
        http://php.net/manual/es/ssh2.constants.php