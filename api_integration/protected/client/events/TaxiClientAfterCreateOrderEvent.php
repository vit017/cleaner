<?php




class TaxiClientAfterCreateOrderEvent extends TaxiClientAfterCommandEvent
{

    
    public function getOrderId()
    {
        if ($this->result) {
            return $this->result->result;
        } else {
            throw new TaxiException("Неверный номер заказа в обработке события после создания заказа");
        }
    }

}
