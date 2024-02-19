<?php

namespace DeliveryCondition\Hook;

use HookAdminHome\Hook\AdminHook;
use Thelia\Core\Event\Hook\HookRenderEvent;

class ConfigurationHook extends AdminHook
{
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $event->add($this->render("module_configuration.html", []));
    }
}