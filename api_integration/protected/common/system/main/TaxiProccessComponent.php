<?php


class TaxiProccessComponent extends TaxiComponent
{
    
    protected $_errors = array();
    
    protected $_reports = array();
    
    public $reportsLineEnd = '';
    
    public $errorsLineEnd = '';
    
    
    public function wasErrors()
    {
        return !empty($this->_errors);
    }
    
    
    public function getErrorsText()
    {
        return implode(', ', $this->_errors);
    }
    
    public function getReportsText()
    {
        $res = '';
        $res = $this->getErrorsText();
        $res .= implode(' ', $this->_reports);
        return $res;
    }
    
    public function getErrors()
    {
        return $this->_errors;
    }
    
    public function getReports()
    {
        return $this->_reports;
    }
    
    
    public function addError($message)
    {
        $this->_errors[] = $message . $this->errorsLineEnd;
    }
    
    public function addReport($message)
    {
        $this->_reports[] = $message . $this->reportsLineEnd;
    }
    
    public function clearErrors()
    {
        $this->_errors = array();
    }
    
    public function clearReports()
    {
        $this->_reports = array();
    }
}