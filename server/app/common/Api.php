<?php
abstract class Api extends Action
{
    /*
     * Override Action->run()
     */
    public function run()
    {
        P('debug')->off();
        $this->process();
    }

    public function response_error($error_code, $message)
    {
        echo json_encode(array(
           'status' => 'Failed',
           'error' => array(
               'code' => $error_code,
               'message' => $message
           )
        ));
    }

    public function response_success($data = "")
    {
        echo json_encode(array(
           'status' => 'OK',
           'data' => $data
        ));
    }
    
    /*
     * Method that will be overrided by children class
     */    
    abstract public function process();
}
