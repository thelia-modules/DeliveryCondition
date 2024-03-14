<?php

namespace DeliveryCondition\EventListeners;

use DeliveryCondition\Service\DeliveryConditionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\ModuleQuery;

class DeliveryLoopExtend implements EventSubscriberInterface
{
    public function __construct(
        private DeliveryConditionService $deliveryConditionService
    )
    {}

    public function deliveryCustomerFamilyCondition(LoopExtendsBuildModelCriteriaEvent $event)
    {
        /** @var ModuleQuery $moduleQuery */
        $moduleQuery = $event->getModelCriteria();
        $this->deliveryConditionService->filterByAllConditions($moduleQuery);
    }

    public static function getSubscribedEvents()
    {
        $events = [];

        $customFamilyModuleIsActive = ModuleQuery::create()
            ->filterByCode('CustomerFamily')
            ->filterByActivate(1)
            ->findOne();

        if (null !== $customFamilyModuleIsActive) {
            $events[TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'delivery')][] = ['deliveryCustomerFamilyCondition', '63'];
        }

        return $events;
    }
}
