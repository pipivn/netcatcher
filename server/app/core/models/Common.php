<?php

class BoundaryResponse 
{
    public $status;
    public $message;
    public $data;

    public function ok()
    {
        return $this->status == "success";
    }
	
    public static function fail($message)
    {
        $res = new BoundaryResponse();
        $res->message =$message;
        $res->status = "fail";
        $res->data = null;
        return $res;
    }
    
    public static function success($data = null)
    {
        $res = new BoundaryResponse();
        $res->message = '';
        $res->status = "success";
        $res->data = $data;
        return $res;
    }
}
