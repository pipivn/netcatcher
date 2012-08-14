<div id="header">
    <div class="inner">
    <?php
        $usercard = P('guard')->get_current_usercard();
        if (!empty($usercard)) {
    ?>
        <a class="header_logo" href="<?php echo home_url()?>"> <?php echo APP_NAME ?></a>
        <div class="right">
            Welcome, <b><?php echo $usercard->display_name; ?></b> &nbsp;&nbsp;
            <a href="<?php echo url('auth/logout')?>">Logout</a>
        </div>

    <?php } else {?>

        <a class="header_logo" href="<?php echo home_url()?>"> <?php echo APP_NAME ?></a>
        <div class="right">
            <a href="<?php echo url('auth/login')?>">Login</a>
        </div>

    <?php } ?>
    </div>
</div>