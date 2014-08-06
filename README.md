# GearmanSchedulerBundle

This bundle brings scheduling to your [Gearman](http://bernardphp.com/) powered [Symfony2](http://symfony.com/) app.


## Requirements

To use this bundle, you need to have:

1. a running [Gearman](http://gearman.org/) server
2. php Gearman [extension](http://pecl.php.net/package/gearman) installed
3. and the [GearmanBundle](https://github.com/mmoreram/GearmanBundle/) setup


## Install

1. Add GearmanSchedulerBundle to your dependencies:

        // composer.json
        {
            // ...
            "require": {
                // ...
                "pompdelux/gearman-scheduler-bundle": "1.*"
            }
        }
2. Use Composer to download and install the bundle:

        $ php composer.phar update pompdelux/gearman-scheduler-bundle

3. Register the bundle in your application:

        // app/AppKernel.php
        class AppKernel extends Kernel
        {
            // ...
            public function registerBundles()
            {
                $bundles = array(
                    // ...
                    new Pompdelux\GearmanSchedulerBundle\GearmanSchedulerBundle(),
                );
            }
        }

4. Add `php_redis` section to `config.yml`

        // app/config.yml
        php_resque:
            class:
                bernard:
                    host:     %redis_host%
                    port:     %redis_port%
                    prefix:   %redis_prefix%
                    skip_env: %redis_skip_env%
                    database: %redis_database%
                    auth:     %redis_password%


## Usage

```php
use Pompdelux\GearmanSchedulerBundle\Job;

$job = new Job('YourGearmanBundleWorker~doStuff', [
    'any' => 'job data',
]);

// enqueue in 30 seconds
$container->get('pdl.gearman_scheduler.scheduler')->enqueueIn(30, $job);

// enqueue at 2pm
$container->get('pdl.gearman_scheduler.scheduler')->enqueueAt(new \DateTime('2 pm'), $job);

```
