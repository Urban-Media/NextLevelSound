<?php

function out_of_the_box_autoload($className)
{
    $classPath = explode('\\', $className);
    if ($classPath[0] != 'TheLion') {
        return;
    }
    if ($classPath[1] != 'OutoftheBox') {
        return;
    }
    $classPath = array_slice($classPath, 2, 3);

    $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
    if (file_exists($filePath)) {
        require_once($filePath);
    }
}
spl_autoload_register('out_of_the_box_autoload');
