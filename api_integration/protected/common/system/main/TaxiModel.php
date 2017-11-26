<?php


class TaxiModel extends TaxiObject
{
    const CONFIG_OPTIONS_KEY = 'options';
    const CONFIG_ENABLE_MODEL_INIT_KEY = 'enableModelInit';
    
    public function tryModelInit($fromPath)
    {
        $rootOptions = self::loadRootConfig($this, $fromPath);
        if ($rootOptions && isset($rootOptions[self::CONFIG_ENABLE_MODEL_INIT_KEY]) &&
                $rootOptions[self::CONFIG_ENABLE_MODEL_INIT_KEY]) {
            self::applyOptionsTo($rootOptions[self::CONFIG_OPTIONS_KEY], $this);
            return true;
        } else {
            return false;
        }
    }
    
    public function applyOptions($options)
    {
        return self::applyOptionsTo($options, $this);
    }
    private static function loadRootConfig($object, $fromPath)
    {
        $rootOptions = false;
        $class = get_class($object);
        if (file_exists($fromPath)) {
            $config = require $fromPath;
            if (!key_exists($class, $config)) {
                throw new TaxiException("Не удалось загрузить конфигурацию объекта класса {$class} из файла {$fromPath}");
            }
            $rootOptions = $config[$class];
        }
        return $rootOptions;
    }
    
    public function loadClassConfigTo($object, $fromPath)
    {
        $rootOptions = self::loadRootConfig($object, $fromPath);
        if ($rootOptions) {
            self::applyOptionsTo($rootOptions[self::CONFIG_OPTIONS_KEY], $object);
        }
        return true;
    }
    
    public static function applyOptionsTo($options, $object)
    {
        foreach ($options as $propertyName => $value) {
            $object->{$propertyName} = $value;
        }
    }
}


