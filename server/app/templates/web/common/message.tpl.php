<?php if (count(P('message')->stack())>0): ?>

<div class="message_container">
    <?php foreach (P('message')->stack() as $msg) { ?>
        <div class="message <?php echo_if_ne($msg['type']);?>">
            <?php echo $msg['content']; ?>
        </div>
    <?php } ?>
</div>

<?php endif; ?>