<?php


class TaxiExceptionHandler
{
    
    public static function handle($exception)
    {
        $log = new TaxiLog(new TaxiExceptionHandler());
        $log->error(
                "Exception in {$exception->getFile()} on line {$exception->getLine()} \n"
                . get_class($exception) . ' with message: ' . $exception->getMessage() . ' code: ' . $exception->getCode()
                . "\n Stack Trace: \n {$exception->getTraceAsString()} "
        );
    }
    
    public static function handleError($exception = null)
    {
        $log = new TaxiLog(new TaxiExceptionHandler());
        $log->error(
                "Error or warning: " . CVarDumper::dumpAsString($exception)
        );
    }
}