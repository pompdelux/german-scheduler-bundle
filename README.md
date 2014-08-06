# GearmanSchedulerBundle

This bundle brings scheduling to your [Gearman](http://bernardphp.com/) powered [Symfony2](http://symfony.com/) app.


## Requirements

To use this bundle, you need to have:

1. a running [Gearman](http://gearman.org/) server
2. php Gearman [extension](http://pecl.php.net/package/gearman) installed
3. the [GearmanBundle](https://github.com/mmoreram/GearmanBundle/)
4. you also need to configure a redis backend for storing schedule data, see step 4 in the install guide.


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
                gearman_schedule:
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


## Running the consumer

To consume scheduled jobs, you need to run a consumer.

Here you have two options:

1. Add a cronjob to run with the desired interval

		// run consumer once pr minute
		* * * * * /usr/bin/php /path/to/console --env=prod gearman:scheduler:consume
2. Use [supervisord](http://supervisord.org/) to run the consumer

		// starting via supervisord, here set to run with a 30 second interval
		[program:gearman_scheduler]
		command = /usr/bin/php /path/to/console --env=prod gearman:scheduler:consume --interval=30
		stopsignal=QUIT


## supervisord

Here is an example of running the scheduler and gearman workers together via supervisord.

```ini
[program:myapp_gearman_scheduler]
command = /usr/bin/php /path/to/console --env=prod gearman:scheduler:consume --interval=30
stopsignal=QUIT

[program:myapp_gearman_worker]
command = /usr/bin/php /path/to/console --env=prod gearman:worker:execute YourGearmanBundleWorker --no-interaction
stopsignal=QUIT

[group:myapp]
programs=myapp_gearman_scheduler,myapp_gearman_worker
```