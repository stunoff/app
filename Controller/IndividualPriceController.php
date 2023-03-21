<?php

namespace App\Controller;

use App\Repository\IndividualPrices;

class IndividualPriceController extends AbstractController
{
    public function indexAction()
    {
        $iPrices = new IndividualPrices();
        $current_price_id = $this->request->getQueryKey('current-price');

        $current_price_data = array();
        if (!empty($current_price_id)) {
            $current_price = $iPrices->one($current_price_id);
            $current_price_data = json_decode($current_price['data'], true);
            unset($current_price_data['hadStartPrice']);
        }
        
        echo $this->render->render('individual-price/index.html.twig', array(
            'individual_prices' => $iPrices->all(),
            'current_price_id' => $current_price_id,
            'current_price' => $current_price,
            'current_price_data' => $current_price_data,
        ));
    }
}
