<?php

namespace App\Controller;

use App\Repository\ActionPay;
use App\Repository\AgeRepo;
use App\Repository\CountriesRepository;
use App\Repository\Departments;
use App\Repository\Orders;
use App\Repository\Sites;
use App\Repository\Statuses;
use App\Repository\UserGroup;

class OrdersController extends AbstractController
{
    public function indexAction()
    {
        exit;
    }

    public function exportOrdersPage()
    {
        $dateStart = $this->request->getQueryKey('date_start', date("Y-m-d"));
        $dateEnd = $this->request->getQueryKey('date_end', date("Y-m-d"));
        $actionPay = $this->request->getQueryKey('action_pay');
        $userGroup = $this->request->getQueryKey('user_group');
        $departments = $this->request->getQueryKey('departments');

        $userGroupRepo = new UserGroup();
        $userGroupOptions = $userGroupRepo->all();
        
        $departmentsRepo = new Departments();
        $departmentsOptions = $departmentsRepo->all();
        
        $actionPayRepo = new ActionPay();
        $actionPayOptions = $actionPayRepo->getByIds(array(3,4));

        $ordersRepo = new Orders();
        $orders = $ordersRepo->exportPage($dateStart, $dateEnd, $actionPay, $userGroup, $departments);
        $stats = $ordersRepo->exportPageStats($dateStart, $dateEnd, $actionPay, $userGroup, $departments);
        
        echo $this->render->render('export-orders-page/index.html.twig', array(
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'action_pay_options' => $actionPayOptions,
            'userGroupOptions' => $userGroupOptions,
            'departmentsOptions' => $departmentsOptions,

            'actionPay' => $actionPay,
            'userGroup' => $userGroup,
            'departments' => $departments,

            'orders' => $orders,
            'stats' => $stats,
        ));
    }

    public function getCDCountStat()
    {
        $ordersRepo = new Orders();
        $statusRepo = new Statuses();

        $date = $this->request->getQueryKey('date', date("Y-m-d"));
        $statusOper = $this->request->getQueryKey('status-oper');
        $statusLogist = $this->request->getQueryKey('status-logist');
        $ppUserIds = $this->request->getQueryKey('pp-user-ids');

        $stats = $ordersRepo->CDCountStat($date, $statusOper, $statusLogist, $ppUserIds);

        echo $this->render->render('cd-count-stat/index.html.twig', array(
            'date' => $date,
            'operStatuses' => $statusRepo->getOperStatuses(),
            'logistStatuses' => $statusRepo->getLogistStatuses(),
            'statusOperSelected' => $statusOper,
            'statusLogistSelected' => $statusLogist,
            'ppUserIds' => $ppUserIds,
            'stats' => $stats,
        ));
    }

    public function exchangeStatuses()
    {
        $maxPostDate = $this->request->getQueryKey('max-post-date');
        $statusOper = $this->request->getQueryKey('status-oper');
        $statusLogist = $this->request->getQueryKey('status-logist');

        /** @var Orders $ordersRepo */
        $ordersRepo = new Orders();
        /** @var Statuses $statusRepo */
        $statusRepo = new Statuses();

        $totalUpdated = 0;
        if (!empty($maxPostDate)
            && !empty($statusOper)
            && !empty($statusLogist)
        ) {

            ini_set('max_execution_time', '0'); //300 seconds = 5 minutes
            set_time_limit(0);
            $datesByStatus = array_unique($ordersRepo->getDatesByStatusAndPostDate($maxPostDate));
            
            foreach ($datesByStatus as $day) {

                $statusChange = 0;
                $ppUserIDDates = $ordersRepo->getPPUserIDDatesByStatusAndPostDate($maxPostDate);
                foreach ($ppUserIDDates as $ppUserId) {

                    $orders = $ordersRepo->getOrdersForStatusExchange($day, $ppUserId);

                    $cntOrders = count($orders);
                    if (empty($cntOrders)) continue;

                    $chunkSize = round(count($orders) / 2);

                    $statuses = array(
                        3, //отменен(z)
                        12, //Некорректный(z)
                    );

                    batch(function($batch) use ($ordersRepo, &$statusChange, $statuses, &$totalUpdated) {
                        $whereOrders = "(" . implode(',', $batch) . ")";
                        $status = $statuses[$statusChange % 2];

                        if ($status == 3) {
                            $statusLs = 21;
                        } else {
                            $statusLs = 37;
                        }

                        $ordersRepo->updateStatusExchange($status, $statusLs, $whereOrders);

                        $statusChange++;
                        $totalUpdated++;
                    }, $orders, $chunkSize);
                }
            }
        }

        echo $this->render->render('update-status/index.html.twig', array(
            'max_post_date' => $maxPostDate,
            'totalUpdated' => $totalUpdated,
            'operStatuses' => $statusRepo->getOperStatuses(),
            'logistStatuses' => $statusRepo->getLogistStatuses(),
        ));
    }

    public function ageStat()
    {
        $orderRepo = new Orders();
        $countriesRepo = new CountriesRepository();
        $sitesRepo = new Sites();
        $ageRepo = new AgeRepo();

        $sites = $this->request->getQueryKey('sites');
        $date = $this->request->getQueryKey('date', date("Y-m-d"));
        $ppUserIds = $this->request->getQueryKey('pp-user-ids');
        $countries = $this->request->getQueryKey('countries');
        $ages = $this->request->getQueryKey('ages');
        $sex = $this->request->getQueryKey('sex');

        $stats = $orderRepo->offerByAge($sites, $date, $ppUserIds, $countries, $ages, $sex);
        
        echo $this->render->render('age-stats/index.html.twig', array(
            'date' => $date,
            'countries' => $countriesRepo->getAll(),
            'countriesSelected' => $countries,

            'sites' => $sitesRepo->getAll(),
            'sitesSelected' => $sites,

            'ages' => $ageRepo->getAll(),
            'agesSelected' => $ages,

            'sex' => array('m' => 'М', 'w' => 'Ж'),
            'sexSelected' => $sex,

            'ppUserIds' => $ppUserIds,
            'stats' => $stats,
        ));
    }
}
