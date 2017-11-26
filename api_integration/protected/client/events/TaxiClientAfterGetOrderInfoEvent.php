<?php




class TaxiClientAfterGetOrderInfoEvent extends TaxiClientAfterCommandEvent
{

   public function getOrderInfoResult()
    {
        if ($this->result) {
            return $this->result->result;
        } else {
            throw new TaxiException("Ошибка получения инф-ии по заказу");
        }
    }

}
