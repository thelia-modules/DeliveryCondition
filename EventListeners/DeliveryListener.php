<?php

namespace DeliveryCondition\EventListeners;

use DeliveryCondition\Service\DeliveryConditionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\ModuleQuery;

class DeliveryListener implements EventSubscriberInterface
{
    public function __construct(private readonly DeliveryConditionService $deliveryConditionService)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::MODULE_DELIVERY_GET_POSTAGE => ['applyDeliveryCondition', 128],
        ];
    }

    public function applyDeliveryCondition(DeliveryPostageEvent $event)
    {
        $customFamilyModuleIsActive = ModuleQuery::create()
            ->filterByCode('CustomerFamily')
            ->filterByActivate(1)
            ->findOne();

        if (null === $customFamilyModuleIsActive) {
            return;
        }

        $moduleQuery = ModuleQuery::create()
            ->filterByCode($event->getModule()->getCode());

        $this->deliveryConditionService->filterByCustomerFamilyCondition($moduleQuery);

        $module = $moduleQuery->findOne();
        if (empty($module)) {
            $event->setValidModule(false);
        }
    }

}