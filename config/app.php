<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'IbansyahMarket'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'development'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://127.0.0.1:8001'),

    'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:3000'),

    'asset_url' => env('ASSET_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    // config other //
    'key_recaptcha' => '6Lfq-VMkAAAAAN8ROG2Sum0MrLzNUueoCvDX_95t',
    'key_secret_cp' => '6Lfq-VMkAAAAAPM1bpLouOPPkc0m9MW_VArocEYL',
    'year_company'  => '2022',
    'year_company2' => '2022 - ',
    'trademark'     => 'PT. Mitra Payment Indonesia',
    'brands'        => 'Iban Syahdien Akbar',
    'color'         => '#01016c',
    'rgba_color'    => 'rgb(1, 1, 108)',
    'keysite'       => 'google-site-verification=YHfVD2VeVfc280xjGzkY7QQn9UAXRoCPnX-sn3da1XA',
    'logo_putih'    => 'iban.png',
    'logo'          => 'iban.png',
    'alamat'        => '
        Jl. Kp. Tipar No. 54 RT. 02/08 Mekarsari, Cimanggis, Depok 16452
    ',
    'description'   => "
    Hi, Introduce me Iban Syahdan Akbar.

    I graduated from Gunadarma University, I majored in information systems, I learned about website-based programming and how to create structures that are used. Therefore, I am very interested in learning more about designing and building so that I can find out what is needed by a web-based application sequentially.

    To solve a problem on such a web-based application, I have experience in creating and designing through several projects, all done by defining various stakeholder requirements, conducting interviews with users so I can get their insights, learn from them, and turn them into ideas. which will provide a better experience for them.

    Apart from learning about Website-Based Programming, I don't limit myself to learning other things related to my major such as analysis and design of information systems and database systems.

    I can handle working with different types of people to create new experiences and learn new perspectives, I can work in a team or individually, I also want to learn more about the work I will be working on in the future.

    ",
    'telf'          => '6281293820960',
    'link_telf'     => 'tel:6281293820960',
    'wa'            => '+62 8129-3820-960',
    'link_wa'       => 'https://wa.me/6281293820960?text=Hallo%20Iban',
    'link_wa_mrc'   => 'https://wa.me/6281293820960?text=Hallo%Iban',
    'email_company' => 'ibansyahdienx7@gmail.com',
    'link_email'    => 'mailto:ibansyahdienx7@gmail.com',
    'key_cookie'    => time(),
    'brand_company' => 'Iban Syahdien Akbar',
    'url_company'   => 'https://hipesanin.com/',
    'email_supports' => 'ibansyahdienx7@gmail.com',
    'brand_name'    => 'Iban Syahdien Akbar',
    'slogan'        => '',

    'url_olshop'    => 'http://127.0.0.1:3000',
    'twitter'       => '',
    'facebook'      => '',
    'instagram'     => 'https://www.instagram.com/ibansyah_/',
    'google'        => '',
    'linkedin'      => 'https://www.linkedin.com/in/ibansyahdien/',
    'intervalReal'  => 6000,

    'app_store'     =>  '',
    'google_play'   =>  '',

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];
