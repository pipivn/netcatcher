<?php
abstract class JSONP extends Action
{
    /*
     * Override Action->run()
     */
    public function run()
    {
        P('debug')->off();
        $this->process();
    }

    public function response($data = "")
    {
        $callback = P('param')->read('callback', '');
        if (!empty($callback)) {
            echo $callback . "(" . $this->wrap('OK', $data) . ")";
        } else {
            return $this->wrap('OK', $data);
        }
    }
    
    public function error($data = "")
    {
        $callback = P('param')->read('callback', '');
        if (!empty($callback)) {
            echo $callback . "(" . $this->wrap('Fail', $data) . ")";
        } else {
            return $this->wrap('Fail', $data);
        }
    }
    
    private function wrap($status, $data)
    {
        return json_encode(array(
            'status' => $status,
            'data' => $data
        ));
    }
    /*
     * Method that will be overrided by children class
     */    
    abstract public function process();
}
