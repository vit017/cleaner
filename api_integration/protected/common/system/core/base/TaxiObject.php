<?php


class TaxiObject extends stdClass
{
    
    private function toUpper($name)
    {
        if (preg_match('/^([A-Za-z])(\S+)/', $name, $m)) {
            return strtoupper($m[1]) . $m[2];
        }
    }
    
    public function __get($name)
    {
        $getter = 'get' . $this->toUpper($name);
        if (method_exists($this, $getter))
            return $this->{$getter}();
        else {
            $intenalName = '_' . $name;
            if (property_exists($this, $intenalName)) {
                return $this->{$intenalName};
            }
        }
        $class = get_class($this);
        $property = $name;
        throw new TaxiException("Свойство \" {$class}.{$property} \" неопределено или доступно только для записи.");
    }
    
    public function __set($name, $value)
    {
        $setter = 'set' . $this->toUpper($name);
        if (method_exists($this, $setter)) {
            return $this->{$setter}($value);
        } else {
            $intenalName = '_' . $name;
            if (property_exists($this, $intenalName)) {
                return $this->{$intenalName} = $value;
            }
        }
        $class = get_class($this);
        $property = $name;
        throw new TaxiException("Свойство \" {$class}.{$property} \" не определено или доступно только для чтения.");
    }
    
    public function __isset($name)
    {
        $getter = 'get' . $this->toUpper($name);
        if (method_exists($this, $getter))
            return $this->{$getter}() !== null;
        else {
            $intenalName = '_' . $name;
            if (property_exists($this, $intenalName)) {
                return isset($this->{$intenalName});
            }
        }
    }
    
    
    public function hasProperty($name)
    {
        $setter = 'set' . $this->toUpper($name);
        $getter = 'get' . $this->toUpper($name);
        return property_exists($this, '_' . $name) || method_exists($this, $setter) || method_exists($this, $getter);
    }
    
    public function getProperties()
    {
        $vars = get_object_vars($this);
        
        $exludeMethods = array();
        if ($parentClass = get_parent_class(get_class($this))) {
            $exludeMethods = get_class_methods($parentClass);
        }
        $methods = array_diff(get_class_methods(get_class($this)), $exludeMethods);
        $properties = array();
        foreach ($methods as $methodName) {
            if (preg_match('/^get([A-Z]{1})(\S*)/', $methodName, $m)) {
                $property = strtolower($m[1]) . $m[2];
                $properties[$property] = $this->{$methodName}();
            }
        }
        foreach ($vars as $name => $value) {
            if (preg_match('/^_(\S+)/', $name, $m)) {
                $property = $m[1];
                $properties[$property] = $value;
            }
        }
        return $properties;
    }
}
