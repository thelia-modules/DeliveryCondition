<?php

namespace DeliveryCondition\Controller;

use DeliveryCondition\Model\DeliveryWeightConditionQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Tools\URL;

#[Route("/admin/module/DeliveryCondition/weight", name: "delivery_condition_weight_condition_")]
class WeightConditionController extends BaseAdminController
{
    #[Route("", name: "save", methods: ["POST"])]
    public function saveAction(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();

        $moduleId = $request->request->get("module_id");
        $minWeight = $request->request->get("min_weight");
        if ($minWeight === "") {
            $minWeight = null;
        }

        $maxWeight = $request->request->get("max_weight");
        if ($maxWeight === "") {
            $maxWeight = null;
        }

        $deliveryCustomerFamily = DeliveryWeightConditionQuery::create()
            ->filterByDeliveryModuleId($moduleId)
            ->findOneOrCreate();

        $deliveryCustomerFamily
            ->setMinWeight($minWeight)
            ->setMaxWeight($maxWeight)
            ->save();

        return new RedirectResponse(URL::getInstance()->absoluteUrl('/admin/module/DeliveryCondition'));
    }
}
