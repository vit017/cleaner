<?php




class TaxiZip
{
    
    public $temporatySubDir = '__tmp_unzip';

    
    private $_zip;

    public function __construct()
    {
        @$this->_zip = new ZipArchive();
        if (!$this->_zip) {
            throw new TaxiException("Не удалось инициализовать архивы ZIP - не влключен модуль в PHP? ");
        }
    }

    
    public function createArchive($fromPath, $toArchivePath, $filter = null)
    {
        $zip = new ZipArchive();
        $files = new TaxiFilesHelper();
        $files->filter = $filter ? $filter : new TaxiFilesFilter();

        $outZipPath = dirname($toArchivePath);
                $zip->open($toArchivePath, ZipArchive::OVERWRITE);
        if ($zip) {
            $all = $files->findDirsAndFiles($fromPath);
            foreach ($all as $path) {
                $relative = $files->removeBaseDir($fromPath . '/', $path);
                if (is_dir($path)) {
                    $zip->addEmptyDir($relative);
                } else {
                    $zip->addFile($path, $relative);
                }
            }
            $zip->close();
            if (!is_file($toArchivePath)) {
                throw new TaxiException("Не удалось создать архив по пути: {$toArchivePath}");
            }
        } else {
            throw new TaxiException("Не удалось создать архив по пути: {$toArchivePath}");
        }
    }

    
    public function extractArchive($fromPath, $toPath, $filter = null)
    {
        $zip = new ZipArchive();
        $files = new TaxiFilesHelper();
                if ($filter) {
            $files->filter = $filter;
        }
        $files->makeDirectory($toPath);

        $oldFiles = $files->findFiles($toPath);
        if (count($oldFiles) > 0) {
            throw new TaxiException("Целевая директория распаковки архива уже содержит файлы {$toPath}");
        }

        $zip->open($fromPath);
        if ($zip) {
            $tempPath = $toPath . '/' . $this->temporatySubDir;
            $zip->extractTo($tempPath);
            $zip->close();

            foreach ($files->filter->exclude->beginPart as $key => $part) {
                $files->filter->exclude->beginPart[$key] = $tempPath . $part;
            }

            $files->filter->exclude->beginPart[] = $tempPath;

            $files->copyDirectory($tempPath, $toPath);

                        $files->removeDir($tempPath);

            $newFiles = $files->findFiles($toPath);
            if (count($newFiles) == 0) {
                throw new TaxiException("Целевая директория распаковки архива не содержит ни одного файла {$toPath}");
            }
            @rmdir($tempPath);
        } else {
            throw new TaxiException("Не удалось создать архив по пути: {$toArchivePath}");
        }
    }

}
