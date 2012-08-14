<?php
require APP_DIR . 'common/ProtectedPage.php';

class Create extends ProtectedPage
{
    public function render_main()
    {
        $form = new PiForm($name);
        
        $form->add('name', PiForm::$SINGLELINE, array(
            'display_name' => 'Name',
            'validation' => 'required'
        ));
        
        $form->add('stream', PiForm::$SINGLECHOICE, array(
            'display_name' => 'Stream',
            'editable' => 'false',
            'validation' => 'required'
        ));
        
        if ($_POST) 
        {
            $form->update($_POST);
            if ($form->ok())
            {
                $agent = Agent::parse($form->values());
                $res = DataFactory::create_agent($agent);
                if ($res->ok()) 
                {
                    $this->info("New agent was created successfully."); 
                    $this->redirect("/agent/edit?id=" . $res->data->id);
                } else 
                {
                    $this->error($res->message);    
                }
            }
            else 
            {
                foreach ($form->get_errors() as $error) {
                    $this->error($error);
                }
            }
        }
        
        return $form->render();
    }

    public function render_sidebar()
    {
        $res = DataFactory::find_agent();
        
        if ($res->ok()) 
        {
            return P('tpl')->html('common/sidebar.tpl.php', array(
                'agents' => $res->data
            ));
        } 
        else 
        {
            $this->error($res->message);    
        }
    }
    
    public function render()
    {
        echo P('tpl')->html('two_column.layout.tpl.php', array(
            'side' => $this->render_sidebar(),
            'main' => $this->render_main()
        ));
    }
}