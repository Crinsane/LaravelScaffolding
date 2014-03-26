<?php namespace Gloudemans\Scaffolding\Facades;

use Illuminate\Support\Facades\Facade;

class Scaffold extends Facade {

    protected static function getFacadeAccessor() { return 'scaffolding'; }

}