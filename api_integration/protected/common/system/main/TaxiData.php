<?php




class TaxiData extends TaxiInfo
{
    

    
    public function __construct($assocArray = null)
    {
        if ($assocArray) {
            $this->fillFromArray($assocArray);
        }
    }

    

    
    public function fillFromArray($assocArray)
    {
        foreach ($this->getProperties() as $propertyName => $value) {
            if (key_exists($propertyName, $assocArray)) {
                $this->{$propertyName} = $assocArray[$propertyName];
            }
        }
    }
    
    
    public function asArray()
    {
        return $this->getProperties();
    }

}
