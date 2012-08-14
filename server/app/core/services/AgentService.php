<?php
require CORE_DIR . "services/BaseService.php";

class AgentService extends HasRepoService
{
    public function can_delete($id)
    {
        return '';
    }
}