<?php

namespace DeliveryCondition\Controller;

use DeliveryCondition\Model\DeliveryWeightConditionQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

#[Route("/admin/module/DeliveryCondition", name: "delivery_condition_admin_config_")]
class AdminController extends BaseAdminController
{
    #[Route("", name: "view", methods: ["GET"])]
    public function viewAction()
    {
        $moduleCodes = [];
        $moduleWeightConditions = [];

        $deliveryModules = ModuleQuery::create()
            ->findByCategory('delivery');


        /** @var Module $deliveryModule */
        foreach ($deliveryModules as $deliveryModule) {
            $moduleCodes[$deliveryModule->getId()] = $deliveryModule->getCode();
            $conditions = DeliveryWeightConditionQuery::create()
                ->filterByDeliveryModuleId($deliveryModule->getId())
                ->findOne();

            $moduleWeightConditions[$deliveryModule->getId()] = $conditions ? [
                "min" => $conditions->getMinWeight(),
                "max" => $conditions->getMaxWeight(),
            ] : [
                "min" => null,
                "max" => null,
            ];
        }

        return $this->render(
            'delivery-condition/configuration',
            [
                "moduleCodes" => $moduleCodes,
                "moduleWeightConditions" => $moduleWeightConditions,
            ]
        );
    }
}
