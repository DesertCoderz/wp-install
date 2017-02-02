#!/usr/bin/php
<?php

$s = new Setup();

class Setup {

    function __CONSTRUCT() {
        if (empty($argv[1]))
            $argv[1] = "setup.csv";

        $this->local_config = $this->getCSV('localconfig.csv');
        $this->config = $this->getCSV($argv[1]);
        $this->createVHost();
        $this->restartServer();

    }

    private function createVHost() {
        echo "Creating Virtual Host File\n";

        $available_path = $this->local_config['apache_config_path'] . "sites-available/";
        $enabled_path = $this->local_config['apache_config_path'] . "sites-enabled/";
        $vhost_config_filename =$this->config['subdomain_name'] . "." . $this->config['domain_name'] . ".conf";
        $vhost_template = file_get_contents("vhost.template.conf");
        $vhost_template = str_replace("%SUBDOMAIN_NAME%",$this->config['subdomain_name'],$vhost_template );
        $vhost_template = str_replace("%DOMAIN_NAME%",$this->config['domain_name'],$vhost_template );
        $vhost_template = str_replace("%WEBMASTER_EMAIL%",$this->config['webmaster_email'],$vhost_template );
        file_put_contents($available_path . $vhost_config_filename ,$vhost_template );

        echo "Creating Symbolic Link\n";

        exec("ln -s $available_path$vhost_config_filename $enabled_path$vhost_config_filename",$error,$out);
        print_r($out);
    }

    private function getCSV($filename) {

        $data = array();

        if (($handle = fopen($filename, "r")) !== FALSE) {

            fgetcsv($handle, 1000, ",");

            while (($row_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $data[$row_data[1]] = $row_data[2];
            }

            fclose($handle);
        }
        return $data;
    }


}