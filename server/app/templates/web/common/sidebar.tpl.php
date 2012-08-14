<div>
    <span>Agents:</span>
    <ul>
        <?php foreach($agents as $agent) {
        echo "<li><a href='" . url("agent/" . $agent->id) . "'>" . $agent->name . "</a></li>";
    } ?>
    </ul>
    <a href="<?php echo url('agent/create')?>">create agent</a>
</div>