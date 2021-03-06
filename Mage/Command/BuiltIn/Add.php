<?php
class Mage_Command_BuiltIn_Add
    extends Mage_Command_CommandAbstract
{
    public function run()
    {
        $subCommand = $this->getConfig()->getArgument(1);

        try {
            switch ($subCommand) {
                case 'environment':
                    $this->_environment();
                    break;
            }
        } catch (Exception $e) {
            Mage_Console::output('<red>' . $e->getMessage() . '</red>', 1, 2);
        }
    }

    private function _environment()
    {
        $withReleases = $this->getConfig()->getParameter('enableReleases', false);
        $environmentName = strtolower($this->getConfig()->getParameter('name'));

        if ($environmentName == '') {
            throw new Exception('You must specify a name for the environment.');
        }

        $environmentConfigFile = '.mage/config/environment/' . $environmentName . '.yml';

        if (file_exists($environmentConfigFile)) {
            throw new Exception('The environment already exists.');
        }

        Mage_Console::output('Adding new environment: <dark_gray>' . $environmentName . '</dark_gray>');

        $releasesConfig = 'releases:' . PHP_EOL
                        . '  enabled: true' . PHP_EOL
                        . '  max: 10' . PHP_EOL
                        . '  symlink: current' . PHP_EOL
                        . '  directory: releases' . PHP_EOL;

        $baseConfig = '#' . $environmentName . PHP_EOL
                    . 'deployment:' . PHP_EOL
                    . '  user: dummy' . PHP_EOL
                    . '  from: ./' . PHP_EOL
                    . '  to: /var/www/vhosts/example.com/www' . PHP_EOL
                    . '  excludes:' . PHP_EOL
                    . ($withReleases ? $releasesConfig : '')
                    . 'hosts:' . PHP_EOL
                    . 'tasks:' . PHP_EOL
                    . '  pre-deploy:' . PHP_EOL
                    . '  on-deploy:' . PHP_EOL
                    . '    - deployment/rsync' . PHP_EOL
                    . '  post-deploy:' . PHP_EOL;
        $result = file_put_contents($environmentConfigFile, $baseConfig);

        if ($result) {
            Mage_Console::output('<light_green>Success!!</light_green> Environment config file for <dark_gray>' . $environmentName . '</dark_gray> created successfully at <blue>' . $environmentConfigFile . '</blue>');
            Mage_Console::output('<dark_gray>So please! Review and adjust its configuration.</dark_gray>', 2, 2);
        } else {
            Mage_Console::output('<light_red>Error!!</light_red> Unable to create config file for environment called <dark_gray>' . $environmentName . '</dark_gray>', 1, 2);
        }
    }
}