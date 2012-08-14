<?php
require CORE_DIR . "services/id_gen/interfaces/IIdGen.php";

class HiLoIdGen implements IIdGen
{
    public function __construct()
    {
    }
    
    public function gen()
    {
        return P('id')->gen();
    }
}