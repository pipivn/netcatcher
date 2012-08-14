<?php
require CORE_DIR . "services/id_gen/interfaces/IIdGen.php";

class SimpleIdGen implements IIdGen
{
    public function __construct()
    {
    }
    
    public function gen()
    {
        return mt_rand();
    }
}