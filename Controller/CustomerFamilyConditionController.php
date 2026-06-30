<?php

namespace DeliveryCondition\Controller;

use DeliveryCondition\Model\DeliveryCustomerFamilyCondition;
use DeliveryCondition\Model\DeliveryCustomerFamilyConditionQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Tools\TokenProvider;

#[Route("/admin/module/DeliveryCondition/customerfamily", name: "delivery_condition_customer_family_condition_")]
class CustomerFamilyConditionController extends BaseAdminController
{
    #[Route("", name: "view", methods: ["GET"])]
    public function viewAction()
    {
        $customerFamilyDeliverysModules = [];
        $moduleCodes = [];
        $familyCodes = [];

        $deliveryModules = ModuleQuery::create()
            ->findByCategory('delivery');

        if (class_exists(\CustomerFamily\Model\CustomerFamilyQuery::class)) {
            $customerFamilies = \CustomerFamily\Model\CustomerFamilyQuery::create()->find();

            /** @var Module $deliveryModule */
            foreach ($deliveryModules as $deliveryModule) {
                $moduleCodes[$deliveryModule->getId()] = $deliveryModule->getCode();

                foreach ($customerFamilies as $customerFamily) {
                    $customerFamilyDeliverysModules[$customerFamily->getId()][$deliveryModule->getId()] = 0;
                    $familyCodes[$customerFamily->getId()] = $customerFamily->getCode();
                }
            }

            $customerFamilyDeliverys = DeliveryCustomerFamilyConditionQuery::create()->find();

            /** @var DeliveryCustomerFamilyCondition $customerFamilyDelivery */
            foreach ($customerFamilyDeliverys as $customerFamilyDelivery) {
                $customerFamilyDeliverysModules[$customerFamilyDelivery->getCustomerFamilyId()][$customerFamilyDelivery->getDeliveryModuleId()] = $customerFamilyDelivery->getIsValid();
            }
        } else {
            /** @var Module $deliveryModule */
            foreach ($deliveryModules as $deliveryModule) {
                $moduleCodes[$deliveryModule->getId()] = $deliveryModule->getCode();
            }
        }

        return $this->render('delivery-condition/customer_family', [
            "module_codes" => $moduleCodes,
            "family_codes" => $familyCodes,
            "deliveryFamilyCondition" =>$customerFamilyDeliverysModules
        ]);
    }

    #[Route("", name: "save", methods: ["POST"])]
    public function saveAction(RequestStack $requestStack, TokenProvider $tokenProvider)
    {
        $request = $requestStack->getCurrentRequest();

        $tokenProvider->checkToken((string) $request->query->get('_token'));

        try {
            $moduleId = $request->request->get("moduleId");
            $customerFamilyId = $request->request->get("customerFamilyId");
            $isValid = $request->request->get("isValid") == "true" ? 1 : 0;

            $deliveryCustomerFamily = DeliveryCustomerFamilyConditionQuery::create()
                ->filterByDeliveryModuleId($moduleId)
                ->filterByCustomerFamilyId($customerFamilyId)
                ->findOneOrCreate();

            $deliveryCustomerFamily->setIsValid($isValid)
                ->save();

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
        return new JsonResponse("Success");
    }
}
