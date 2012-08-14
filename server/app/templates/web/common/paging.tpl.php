<?php
    $total_page = ceil($total / $pagesize);
    
    
    if (!function_exists('getPageURL'))
    {
        function getPageURL($page) {
            $uri = $_SERVER['REQUEST_URI'];
            $arr = explode('?', $uri);
            $query = isset($arr[1]) ? $arr[1] : '';
            $query = preg_replace('/[\\?&]?page=[0-9]*/', '', $query);

            if (empty($query)) $query = 'page=' . $page;
            else {
                if ($query[0] == '&') $query = substr($query, 1);
                $query .= '&page=' . $page;
            }

            return '?' . $query;
        }
    }
    if (empty($total_page)) $total_page = 0;

    if (empty($page)) $page = 1;

    $from_page = $page - 2;
    $from_page = ($from_page < 1) ? 1 : $from_page;
    $to_page = $page + 2;
    $to_page = ($to_page > $total_page) ? $total_page : $to_page;

    $next_page = $page + 1;
    $previous_page = $page - 1;
?>

<div class="paging-bar">
    <?php if ($total_page>1) : ?>
    <ul>
        <?php
            if ($page > 1) echo '<li><a href="' . getPageURL(1) . '"><<</a></li>';
            if ($previous_page > 0) echo '<li><a href="' . getPageURL($previous_page) . '" title="Previous"><</a></li>';

            for ($i = $from_page; $i <= $to_page; $i++) {
                if ($i != $page) echo '<li><a href="' . getPageURL($i) . '">' . $i . '</a></li>';
                else echo '<li class="active"><span>' . $i . '</span></li>';
            }

            if ($next_page <= min($to_page, $total_page + 1)) echo '<li><a href="' . getPageURL($next_page) . '" title="Next">></a></li>';
            if ($page < $total_page) echo '<li><a href="' . getPageURL($total_page) . '">>></a></li>';
        ?>
        <li class="paging-info">
            <?php echo sprintf('%d - %d of %d',($page-1) * $pagesize + 1, $page * $pagesize > $total ? $total : $page * $pagesize,  $total) ?>
        </li>
    </ul>
    <?php endif; ?>
    
</div>
