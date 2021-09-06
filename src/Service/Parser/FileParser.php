<?php

namespace App\Service\Parser;

/**
 * ObjectSerializer
 * 
 * Informations :
 * Its unique method serializeEntity allow to serialize an entity (first parameter) 
 * taking care about the selected serialization group (second parameter)
 *
 * @author Sébastien : sebastien.maillot@coding-academy.fr
 */
class FileParser
{
    public function parse($fileName)
    {
        $content = file_get_contents($fileName);
        $content = iconv( 'US-ASCII', 'UTF-8//IGNORE//TRANSLIT', $content );
        dump($content);
        // $content = str_replace('\é', '', $content);
        // $fileName = mb_convert_encoding($fileName, "UTF-8", "auto");
        // $str = mb_convert_encoding($fileName,"UTF-8");
        // $content = file_get_contents($str);
        // dump($content);
        // $str = iconv("UTF-8",'ASCII','�');
        
        // dump(mb_detect_encoding($str));
        // dump($str);
        // $content = str_replace($str, '', $content);
        
        file_put_contents($fileName, $content);
    }
}