<?php

/**
 * N11 Marketplace Adapter (Skeleton)
 */
class N11Adapter extends Marketplace
{
    protected $marketplaceName = 'n11';

    public function pushProduct($productId)
    {
        // N11 SOAP API Implementation will be here
        $this->log('pushProduct', 'Mock Request', 'Mock Response');
        return true;
    }

    public function fetchOrders()
    {
        return [];
    }

    public function syncInventory($productId)
    {
        return true;
    }
}
