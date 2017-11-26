<?php

/*
 * Валидатор адаптера
 */

/**
 * Валидатор адаптера
 */
class GootaxAdapterValidator extends TaxiValidator
{

    /**
     * Проверка форматы даты
     * @param string $priorTime
     */
    public function validate_createOrder_priorTime($priorTime)
    {
        /*if ($priorTime && !preg_match('/^\d{14}$/', $priorTime)) {
            $this->addError('priorTime', 'Время и дата поездки имеют неверный внутренний формат!');
        }*/
    }

}
