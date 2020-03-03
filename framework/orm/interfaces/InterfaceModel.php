<?php 

namespace Framework\Orm\Interfaces;

interface InterfaceModel {
    function save();
    static function find($id);
    static function all();
    function destroy();
}