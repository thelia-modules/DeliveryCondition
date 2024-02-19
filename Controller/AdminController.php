<?php

namespace DeliveryCondition\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/module/DeliveryCondition", name: "delivery_condition_admin_config_")]
class AdminController extends BaseAdminController
{
    #[Route("", name: "view", methods: ["GET"])]
    public function viewAction()
    {
        return $this->render('delivery-condition/configuration');
    }
}
