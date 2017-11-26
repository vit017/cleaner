<?php




class PackageJsFilter
{

    
    public function createTrashCode()
    {
        $length = rand(0, 50);
        $trashVars = 'taxiMap map yandexMaps maps taxis taxiMapper clients servers serverJs order createOrder';
        $trashVars = explode(' ', $trashVars);
        $tokens = 'var @ = #;|function @(){ if (#) {return #}};|@;|@.@ = #;| @.@.@(){ return #;}';
        $tokens = explode('|', $tokens);
        $res = '';

        return $res;
    }

    
    public function filter_replaceConsoleLogToTrash($jsCode)
    {
        $limit = 1;
        $count = 1;
        while ($count > 0) {
            $trash = $this->createTrashCode();
            $trash = ' ' . $trash . ' ';
            $jsCode = preg_replace('/(;|\{|\s+)console\.log\(.*(?!=\);)\);([a-zA-Z]|\}|\s+)/Usm', "$1$trash$2", $jsCode, $limit, $count);
        }
        return $jsCode;
        
        
    }

    
    public function filter_removeStrongComments($jsCode)
    {
        return $jsCode;
        $jsCode = preg_replace('/\/\*\!/', '/*', $subject);
    }

    
    public function applyFilters($filePath, $sourceCode)
    {
        
        $sourceCode = $this->filter_replaceConsoleLogToTrash($sourceCode);
        $sourceCode = $this->filter_removeStrongComments($sourceCode);
        return $sourceCode;
    }

}
