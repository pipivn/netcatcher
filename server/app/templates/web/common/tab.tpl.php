<ul class='tab_header'>
    <?php foreach($tabs as $key => $tab) {
        echo "<li " . ($key == $selected ? "class='selected'" : "") . "'>";
        echo "<a class='_ajax tab_item'" . " href='" . $tab['link'] . "'>" .  $tab['display_name'] . "</a>";
        echo "</li>";
    } ?>
</ul>
<div class="tab_body _ajax_target">
    <?php echo $body ?>
</div>