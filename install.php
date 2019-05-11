#!/usr/bin/php
<?php

$s = new Setup();

class Setup {

    private $os = PHP_OS;

    private $slug = '';
    private $download_path = __DIR__ . '/downloads';
    private $db_user = '';
    private $db_pass = '';
    private $db_name = '';
    private $db_host = '';

    function __construct() {

        if (empty($_SERVER['argv'][1])) {

            echo "Please enter the project's slug ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            $this->slug = trim($line);

        } else {

            $this->slug = $_SERVER['argv'][1];
        }

        $this->local_config = parse_ini_file(__DIR__ . "/config/config.local.ini", true);
        $this->config = parse_ini_file(__DIR__ . "/config/config.ini", true);
        $this->createVHost();
        $this->clearDownloadFolder();
        $this->downloadWP();
        $this->unzipWP();
        $this->restartServer();
    }

    private function unzipWP() {

        echo "Unzipping Worpress\n";
        echo shell_exec("unzip -q $this->download_path/latest-de_DE.zip -d $this->download_path/ 2>&1;");
    }

    private function clearDownloadFolder() {

        echo "Deleting Downloading Folder\n";
        echo shell_exec( "rm -rf $this->download_path/*");
    }

    private function downloadWP() {

        echo "Downloading Wordpress\n";
        echo shell_exec("wget -q -P $this->download_path http://de.wordpress.org/latest-de_DE.zip 2>&1;");
    }

    private function createVHost() {

        echo "Creating Virtual Host File\n";

        $available_path = $this->local_config[$this->os]['apache_config_path'] . "/sites-available/";
        $enabled_path = $this->local_config[$this->os]['apache_config_path'] . "/sites-enabled/";
        $vhost_config_filename = $this->config['virtual_host']['domain_name'] . ".conf";
        $vhost_template = file_get_contents(__DIR__  . "/templates/vhost.template.conf");
        $vhost_template = str_replace("%APACHE_DOC_ROOT%", $this->local_config[$this->os]['apache_doc_root'] , $vhost_template);
        $vhost_template = str_replace("%DOMAIN_NAME%", $this->config['virtual_host']['domain_name'], $vhost_template);
        $vhost_template = str_replace("%WEBMASTER_EMAIL%", $this->config['virtual_host']['webmaster_email'], $vhost_template);

        file_put_contents($available_path . $vhost_config_filename, $vhost_template );

        echo "Creating Symbolic Link\n";

        echo shell_exec("ln -s $available_path$vhost_config_filename $enabled_path$vhost_config_filename 2>&1;");
    }

    private function restartServer() {
        echo "Restarting Apache\n";
        exec("/etc/init.d/apache2 restart",$error,$out);
        if ($error)
            echo $out;
    }
}