<?php

namespace DeliveryCondition\Hook;

use DeliveryCondition\Model\DeliveryWeightConditionQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

class ConfigurationHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $moduleCodes = [];
        $moduleWeightConditions = [];

        $deliveryModules = ModuleQuery::create()
            ->findByCategory('delivery');

        /** @var Module $deliveryModule */
        foreach ($deliveryModules as $deliveryModule) {
            $moduleCodes[$deliveryModule->getId()] = $deliveryModule->getCode();

            $condition = DeliveryWeightConditionQuery::create()
                ->filterByDeliveryModuleId($deliveryModule->getId())
                ->findOne();

            $moduleWeightConditions[$deliveryModule->getId()] = [
                'min' => $condition ? $condition->getMinWeight() : null,
                'max' => $condition ? $condition->getMaxWeight() : null,
            ];
        }

        $customerFamilyEnabled = ModuleQuery::create()
            ->filterByCode('CustomerFamily')
            ->filterByActivate(1)
            ->count() > 0;

        $event->add($this->render('DeliveryCondition/configuration.html.twig', [
            'moduleCodes' => $moduleCodes,
            'moduleWeightConditions' => $moduleWeightConditions,
            'customerFamilyEnabled' => $customerFamilyEnabled,
        ]));
    }
}
