<?php

namespace controllers;

use program\core;
use models;

class Serials extends _Controller
{


    public static function run()
    {
        if (!empty(core\App::$URLParams['action'])) {
            switch (core\App::$URLParams['action']) {
                case 'del-serial':
                    models\Serials::delSerial(core\App::$URLParams['serial-id']);
                    header('Location: /edit-model/'.core\App::$URLParams['model-id'].'/');
                    break;
            }
            exit;
        }
    }
}
