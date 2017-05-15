<?php
/**
 * MIT License (MIT)
 *
 * Copyright (c) 2017 Zextras SRL
 */

namespace OCA\ZimbraDrive\AppInfo;

use OCA\ZimbraDrive\Service\LogService;
use OCP\AppFramework\App;
use OC;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;


class Application extends App {
    const APP_NAME = 'zimbradrive';

    public function __construct(array $urlParams=array()){
        parent::__construct(self::APP_NAME, $urlParams);

        $container = $this->getContainer();

        $container->registerService('IUserSession', function($c) {
            return $c->query('ServerContainer')->getUserSession();
        });

        $container->registerService('ILogger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });

        $container->registerService('LogService', function($c) {
            $logger = $c->query('ILogger');

            return new LogService($logger, self::APP_NAME);
        });

        $container->registerService('IServerContainer', function($c) {
            return $c->query('ServerContainer');
        });

        $container->registerService('IConfig', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });
    }
}

OC::$CLASSPATH['OC_User_Zimbra'] = 'zimbradrive/lib/auth/oc_user_zimbra.php';

$app = new Application();

if(!interface_exists(ISettings::class))  // ISettings not supported in OwnCloud 9.1.4
{
    \OCP\App::registerAdmin(Application::APP_NAME, 'admin');
}

$container = $app->getContainer();

$container->query('OCP\INavigationManager')->add(function () use ($container) {
    /** @var IURLGenerator $urlGenerator */
    $urlGenerator = $container->query('OCP\IURLGenerator');
    $l10n = $container->query('OCP\IL10N');
    return [
        // the string under which your app will be referenced in *Cloud
        'id' => Application::APP_NAME,

        // sorting weight for the navigation. The higher the number, the higher
        // will it be listed in the navigation
        'order' => 10,

        // the route that will be shown on startup
        'href' => $urlGenerator->linkToRoute('zimbradrive.page.index'),

        // the icon that will be shown in the navigation
        // this file needs to exist in img/
        'icon' => $urlGenerator->imagePath(Application::APP_NAME, 'app.svg'),

        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        'name' => $l10n->t('Zimbra'),
    ];
});
