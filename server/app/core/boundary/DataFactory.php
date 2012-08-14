<?php

class DataFactory
{
    /**
     * @return AgentService
     */
    protected static function get_agent_service()
    {
        return P('ioc')->load('@AgentService');
    }
    
    ////////////
    
    public static function create_stream($name, $code_name = null)
    {
    }
    
    public static function update_stream($id, $tags)
    {
        
    }
    
    public static function delete_stream($id)
    {        
    }
    
    ////////////
    
    public static function find_agent($take = null, $skip = null, $order = null)
    {
        return BoundaryResponse::success (
            self::get_agent_service()->find(null, $take, $skip, $order) 
        );
    }
    
    public static function create_agent($stream_id, $name, $content, $options = array())
    {
        //do validate
        if (empty($name))
            return BoundaryResponse::fail("Agent's name can not be null");
        
        if (!is_array($options))
            return BoundaryResponse::fail("Parameter 'options' must be an array");
        
        //do create
        $agent = new Agent();

        $agent->name = $name;
        $agent->content = $content;
        $agent->options = $options;

        $saved_agent = self::get_agent_service()->save_agent($agent);
        return BoundaryResponse::success($saved_agent);
    }
    
    public static function load_agent($id)
    {
        $agent = self::get_agent_service()->load_agent($id);
        if (empty($agent)) return BoundaryResponse::fail("Agent not found");
        return BoundaryResponse::success($agent);
    }

    public static function update_agent($id, $name = null, $content = null, $options = null)
    {
        //do validate
        if (!is_null($name) && empty($name))
            return BoundaryResponse::fail("Agent's name can not be null");
        
        if (!is_null($options) && !is_array($options))
            return BoundaryResponse::fail("Parameter 'options' must be an array");
        
        $agent = self::get_agent_service()->load_agent($id);
        if (empty($agent))
            return BoundaryResponse::fail("Agent not found");
      
        //do update
        if (!is_null($name)) $agent->name = $name;
        if (!is_null($content)) $agent->content = $content;
        if (!is_null($options)) $agent->options = $options;

        $saved_agent = self::get_agent_service()->save_agent($agent);
        return BoundaryResponse::success($saved_agent);
    }

    public static function delete_agent($id)
    {
        $message = self::get_agent_service()->can_delete($id);

        if (!empty($message)) {
            return BoundaryResponse::fail("Can not delete this journal. " + $message);
        } else {
            self::get_agent_service()->delete_agent($id);
            return BoundaryResponse::success();
        }
    }
}