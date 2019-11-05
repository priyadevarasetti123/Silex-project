<?php

namespace TwitterClone\Db;

use PDO;
use TwitterClone\Db as DbLog;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

# service provider
class DbServiceProvider implements ServiceProviderInterface
{
    
    public function boot(Application $application)
    {
    }
    
    public function register(Container $application)
    {
        $application['Db.factory'] = $application->protect(
            function (
                $dsn,
                $username = null,
                $password = null,
                array $options = array()
            ) use ($application) {
                if ($application['debug'] && isset($application['monolog'])) {
                    $Db = new DbLog($dsn, $username, $password, $options);
                    $Db->onLog(
                        function (array $entry) use ($application) {
                            $application['monolog']->addDebug(
                                sprintf(
                                    'Db query: %s, values :%s',
                                    $entry['query'],
                                    var_export($entry['values'], true)
                                )
                            );
                        }
                    );
                    return $Db;
                }
                $Db = new PDO($dsn, $username, $password, $options);
                $Db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                return $Db;
            }
        );
        $application['Db'] = function (Application $application) {
                foreach ($application['Db.defaults'] as $name => $value) {
                    if (!isset($application[$name])) {
                        $application[$name] = $value;
                    }
                }
                return $application['Db.factory'](
                    $application['Db.dsn'],
                    $application['Db.username'],
                    $application['Db.password'],
                    $application['Db.options']
                );
            };
        $application['Db.defaults'] = array(
            'Db.username' => null,
            'Db.password' => null,
            'Db.options' => array()
        );
    }
}