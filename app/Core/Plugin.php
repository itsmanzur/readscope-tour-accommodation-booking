<?php

declare(strict_types=1);

namespace Readscope\Core;

use Readscope\Common\Abstracts\ServiceProvider;
use Readscope\Common\Container;
use Readscope\Common\Traits\Hookable;
use Readscope\Common\Traits\Singleton;
use Readscope\Providers\AdminServiceProvider;
use Readscope\Providers\AppServiceProvider;
use Readscope\Providers\BlockServiceProvider;
use Readscope\Providers\DatabaseServiceProvider;
use Readscope\Providers\ElementorServiceProvider;
use Readscope\Providers\MetaBoxServiceProvider;
use Readscope\Providers\PostTypeServiceProvider;
use Readscope\Providers\RouteServiceProvider;
use Readscope\Providers\TemplateServiceProvider;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Plugin
 *
 * Central orchestrator and bootstrap class for Readscope Booking.
 *
 * @package Readscope\Core
 */
final class Plugin
{
    use Singleton;
    use Hookable;

    /**
     * Dependency injection container.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Registered service providers.
     *
     * @var array<class-string<ServiceProvider>, ServiceProvider>
     */
    protected array $providers = [];

    /**
     * Core service provider classes.
     *
     * @var array<class-string<ServiceProvider>>
     */
    protected array $defaultProviders = [
        DatabaseServiceProvider::class,
        PostTypeServiceProvider::class,
        MetaBoxServiceProvider::class,
        RouteServiceProvider::class,
        TemplateServiceProvider::class,
        BlockServiceProvider::class,
        ElementorServiceProvider::class,
        AppServiceProvider::class,
        AdminServiceProvider::class,
    ];

    /**
     * Initialize container.
     */
    protected function __construct()
    {
        $this->container = new Container();
        Container::setInstance($this->container);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(self::class, $this);
    }

    /**
     * Get the DI container.
     *
     * @return Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Bootstrap the plugin.
     *
     * @return void
     */
    public function boot(): void
    {
        // 1. Fire early hook
        do_action('readscope/before_boot', $this);

        // Existing installs pick up capabilities added after the plugin was activated.
        Installer::addCapabilities();

        // 2. Allow third-parties / Pro add-on to register additional providers
        $providers = apply_filters('readscope/service_providers', $this->defaultProviders);

        // 3. Register all providers
        foreach ($providers as $providerClass) {
            $this->registerProvider($providerClass);
        }

        // 4. Boot all providers
        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        // 5. Fire main ready hook
        do_action('readscope/loaded', $this);
    }

    /**
     * Register a service provider.
     *
     * @param class-string<ServiceProvider> $providerClass
     * @return ServiceProvider
     */
    public function registerProvider(string $providerClass): ServiceProvider
    {
        if (isset($this->providers[$providerClass])) {
            return $this->providers[$providerClass];
        }

        /** @var ServiceProvider $provider */
        $provider = new $providerClass($this->container);
        $provider->register();

        $this->providers[$providerClass] = $provider;

        return $provider;
    }

    /**
     * Retrieve a loaded service provider by class.
     *
     * @param class-string<ServiceProvider> $providerClass
     * @return ServiceProvider|null
     */
    public function getProvider(string $providerClass): ?ServiceProvider
    {
        return $this->providers[$providerClass] ?? null;
    }
}
