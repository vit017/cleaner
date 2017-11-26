<?php




class TaxiConsole extends TaxiObject
{
    
    protected $_currentColor;
    public $isPreStyle = true;

    

    

    

    private function createLine($text, $color)
    {
        $preStyle = $this->isPreStyle ? 'white-space:pre;' : '';
        return "<p style=\"color: {$color}; {$preStyle}  font-size: 12px; \">" . $text . "</p>";
    }

    private function filter($text)
    {
        return $text;
    }

    
    public function write($text, $color = null)
    {
        ob_start();
        if (!$color) {
            $color = $this->_currentColor;
        }
        $text = $this->filter($text);
        echo $this->createLine($text, $color);
        ob_flush();
    }

}
