<?php

namespace DeliveryCondition\Service;

use CustomerFamily\Model\CustomerCustomerFamilyQuery;
use DeliveryCondition\Model\DeliveryCustomerFamilyConditionQuery;
use DeliveryCondition\Model\Map\DeliveryCustomerFamilyConditionTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Map\ModuleTableMap;
use Thelia\Model\ModuleQuery;

class DeliveryConditionService
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
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

        // If no customer family set, disable all modules
        if (null === $customerCustomerFamily) {
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
            ->addJoinCondition('delivery_customer_family_condition_join', DeliveryCustomerFamilyConditionTableMap::COL_CUSTOMER_FAMILY_ID.' = '.$customerCustomerFamily->getCustomerFamilyId())
            ->addJoinCondition('delivery_customer_family_condition_join', DeliveryCustomerFamilyConditionTableMap::COL_IS_VALID . ' = 1');
    }
}