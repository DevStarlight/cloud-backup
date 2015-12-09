<?php
/**
 * Jesus Huerta Arrabal
 * Twitter: @DevStarlight
 */

class Panel
{
    public function __construct()
    {
        $this->loadConfig();

        $options = $this->getOptionRequested();

        if ($options !== false && (isset($options['backup']) || isset($options['restore']) || isset($options['help']) || isset($options['b']) || isset($options['r']) || isset($options['h'])))
        {
            $this->dispatch($options);
        }
        else
        {
            echo "\r\n use the argument \"--help\" for more support \r\n\r\n\r\n";
        }
    }

    public function loadConfig()
    {
        require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'config.php');
    }

    protected function getOptionRequested()
    {
        $argOptions = "brhi:m:p:k:";
        $argLongOptions = ['backup', 'restore', 'help', 'idInstance:', 'ipInstance:', 'microsite:', 'bucket:'];

        if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) // command line
        {
            return getopt($argOptions, $argLongOptions);
        }
        else if (isset($_REQUEST))  // web script
        {
            $found = array();

            $shortopts = preg_split('@([a-z0-9][:]{0,2})@i', $argOptions, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $opts = array_merge($shortopts, $argLongOptions);

            foreach ($opts as $opt)
            {
                if (substr($opt, -2) === '::')  // optional
                {
                    $key = substr($opt, 0, -2);

                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                    {
                        $found[$key] = $_REQUEST[$key];
                    }
                    else if (isset($_REQUEST[$key]))
                    {
                        $found[$key] = false;
                    }
                }
                else if (substr($opt, -1) === ':')  // required value
                {
                    $key = substr($opt, 0, -1);

                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                    {
                        $found[$key] = $_REQUEST[$key];
                    }
                }
                else if (ctype_alnum($opt))  // no value
                {
                    if (isset($_REQUEST[$opt]))
                    {
                        $found[$opt] = false;
                    }
                }
            }

            return $found;
        }

        return false;
    }

    private function dispatch($options)
    {
        if (isset($options['backup']) || isset($options['b'])) // Backup data
        {
            require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'backup.php');

            new Backup($options);
        }
        else if (isset($options['restore']) || isset($options['r'])) // Restore data
        {
            require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'restore.php');

            new Restore($options);
        }
        else if (isset($options['help']) || isset($options['h'])) // Helping options
        {
            echo file_get_contents(dirname(__DIR__).DIRECTORY_SEPARATOR.'helper.txt');
        }
    }
}

?>
