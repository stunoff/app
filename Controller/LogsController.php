<?php

namespace App\Controller;

use App\Filters\OrderFilter;
use App\Repository\LogsRepository;

class LogsController extends AbstractController
{
    public function indexAction()
    {
        $logsRepository = new LogsRepository();
        $orderId        = $this->request->getQueryKey('order-id');
        $phone          = $this->request->getQueryKey('phone');
        $customerName   = $this->request->getQueryKey('name');


        $logs = null;
        if (!empty($orderId) || !empty($phone) || !empty($customerName)) {
            $logs = $logsRepository->getLogs($orderId, $phone, $customerName);
        }

        echo $this->render->render('logs/logs.html.twig', array(
            'logs' => !empty($logs) ? OrderFilter::hidePhonesByUserTip($logs, $this->user) : $logs,
            'order_id'      => $orderId,
            'phone'         => $phone,
            'customer_name' => $customerName,
        ));
    }
}
