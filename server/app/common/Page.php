<?php
abstract class Page extends Action
{
    /*
     * Override Action->run()
     */
    public function run()
    {
        $this->render();
    }

    /*
     * @params string $message
     */
    public function warning($message)
    {
        P('message')->push($message, 'w');
    }

    /*
    * @params string $message
    */
    public function info($message)
    {
        P('message')->push($message, 'i');
    }

    /*
    * @params string $message
    */
    public function error($message)
    {
        P('message')->push($message, 'e');
    }

    /*
     * Method that will be overrided by children class
     */
    abstract public function render();
}
