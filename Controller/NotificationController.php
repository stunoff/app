<?php

namespace App\Controller;

use App\Repository\AsteriskNotice;

class NotificationController extends AbstractController
{
    public function indexAction()
    {
        exit('Hello World');
    }
    
    public function asteriskNotificationsAction()
    {
        $dateStart = $this->request->getPostKey('date_start', date("Y-m-d"));
        $dateEnd = $this->request->getPostKey('date_end', date("Y-m-d"));
        $asteriskNotice = new AsteriskNotice();
        $noticeCount = $asteriskNotice->getNoticeCount($dateStart, $dateEnd, $_SESSION['user']);
        $noticeData = $asteriskNotice->getNotice($dateStart, $dateEnd, $_SESSION['user']);
        
        echo $this->render->render('asterisk-notifications/index.html.twig', array(
            'notice_count' => $noticeCount,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'notice_data' => $noticeData,
        ));
    }
}
