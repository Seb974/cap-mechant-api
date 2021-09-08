<?php

namespace App\Service\Parser;

/**
 * FileParser
 * 
 * Informations :
 * Its unique method parse convert encoded file from ASCII to UTF-8
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class FileParser
{
    public function parse($fileName)
    {
        $content = file_get_contents($fileName);
        $content = iconv('US-ASCII', 'UTF-8//IGNORE//TRANSLIT', $content);
        file_put_contents($fileName, $content);
    }
}