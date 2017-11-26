<?php




class TaxiLogConsole extends TaxiConsole implements ITaxiLogEventHandler
{
    

    

    
    protected function createColorizedLine($info)
    {
        $line = $info->createLine();
        $line = preg_replace('/http\S+/', '<a href="$0">$0</a>', $line);
        return $line;
    }

    
    public function onLogWrite($info)
    {
        $line = $this->createColorizedLine($info);
        $color = '#000';
        switch ($info->level) {
            case TaxiLog::LEVEL_INFO:
                $color = '#005500';
                break;
            case TaxiLog::LEVEL_ERROR:
                $color = '#FF0000';
                break;
            case TaxiLog::LEVEL_WARNING:
                $color = '#BB0088';
                break;
            default:
                break;
        }
        $this->write($line, $color);
    }

}
