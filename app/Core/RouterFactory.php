<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        $admin = new RouteList('Admin');
        $admin->addRoute('admin/sign/<action>', 'Sign:in');
        $admin->addRoute('admin/<presenter>/<action>[/<id>]', 'Dashboard:default');
        $router[] = $admin;

        $router->withModule('Front')
            ->addRoute('novinky/<slug>', 'News:detail')
            ->addRoute('novinky', 'News:default')
            ->addRoute('clanky/<slug>', 'Articles:detail')
            ->addRoute('clanky', 'Articles:default')
            ->addRoute('produkty/<slug>', 'Products:detail')
            ->addRoute('produkty', 'Products:default')
            ->addRoute('rezervace/<token>', 'Reservation:detail')
            ->addRoute('rezervace', 'Reservation:default')
            ->addRoute('<slug>', 'Homepage:page')
            ->addRoute('', 'Homepage:default');

        return $router;
    }
}
