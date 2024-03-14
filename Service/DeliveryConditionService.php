<?php

namespace DeliveryCondition\Service;

use CustomerFamily\Model\CustomerCustomerFamilyQuery;
use CustomerFamily\Model\CustomerFamilyQuery;
use DeliveryCondition\Model\DeliveryCustomerFamilyConditionQuery;
use DeliveryCondition\Model\Map\DeliveryCustomerFamilyConditionTableMap;
use DeliveryCondition\Model\Map\DeliveryWeightConditionTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Map\ModuleTableMap;
use Thelia\Model\ModuleQuery;

class DeliveryConditionService
{
    public function __construct(
        private RequestStack $requestStack,
        private EventDispatcherInterface $dispatcher
    )
    {
    }

    public function filterByAllConditions(ModuleQuery $query): void
    {
        $this->filterByCustomerFamilyCondition($query);
        $this->filterByWeightCondition($query);
    }

    public function filterByCustomerFamilyCondition(ModuleQuery $query): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()->getSession();

        $customer = $session->getCustomerUser();

        if (null === $customer) {
            return;
        }

        $deliveryCustomerFamilyCondition = DeliveryCustomerFamilyConditionQuery::create()
            ->findOne();

        // If no condition set, don't filter
        if (null === $deliveryCustomerFamilyCondition) {
            return;
        }

        $customerCustomerFamily = CustomerCustomerFamilyQuery::create()
            ->findOneByCustomerId($customer->getId());

        $customerFamily = $customerCustomerFamily?->getCustomerFamily() ??
            CustomerFamilyQuery::create()->filterByIsDefault(true)->findOne();


        // If no customer family set, disable all modules
        if (null === $customerFamily) {
            $query->filterById(-1);
            return;
        }

        $join = new Join();
        $join->addExplicitCondition(
            ModuleTableMap::TABLE_NAME,
            'ID',
            null,
            DeliveryCustomerFamilyConditionTableMap::TABLE_NAME,
            'DELIVERY_MODULE_ID',
            null
        );

        $join->setJoinType(Criteria::JOIN);
        $query->addJoinObject($join, 'delivery_customer_family_condition_join')
            ->addJoinCondition('delivery_customer_family_condition_join', DeliveryCustomerFamilyConditionTableMap::COL_CUSTOMER_FAMILY_ID.' = '.$customerFamily->getId())
            ->addJoinCondition('delivery_customer_family_condition_join', DeliveryCustomerFamilyConditionTableMap::COL_IS_VALID . ' = 1');
    }

    public function filterByWeightCondition(ModuleQuery $query): void
    {
        try {
            /** @var Session $session */
            $session = $this->requestStack->getSession();

            $cart = $session->getSessionCart($this->dispatcher);

            $cartWeight = $cart->getWeight();


            $join = new Join();
            $join->addExplicitCondition(
                ModuleTableMap::TABLE_NAME,
                'ID',
                null,
                DeliveryWeightConditionTableMap::TABLE_NAME,
                'DELIVERY_MODULE_ID',
                null
            );

            $join->setJoinType(Criteria::LEFT_JOIN);
            $query->addJoinObject($join, 'delivery_weight_condition_join');

            $query->condition('greater_min_weight', 'delivery_weight_condition_join.min_weight <= ? ', $cartWeight, \PDO::PARAM_STR);
            $query->condition('null_min_weight', 'delivery_weight_condition_join.min_weight IS NULL',);
            $query->combine(['greater_min_weight', 'null_min_weight'], Criteria::LOGICAL_OR, 'min_weight');

            $query->condition('less_max_weight', 'delivery_weight_condition_join.max_weight >= ? ', $cartWeight, \PDO::PARAM_STR);
            $query->condition('null_max_weight', 'delivery_weight_condition_join.max_weight IS NULL',);
            $query->combine(['less_max_weight', 'null_max_weight'], Criteria::LOGICAL_OR, 'max_weight');

            $query->where(['min_weight', 'max_weight'], Criteria::LOGICAL_AND);

        } catch (\Exception $e) {
            dd($e->getMessage());
            // If an exception is thrown, don't filter
        }
    }
}