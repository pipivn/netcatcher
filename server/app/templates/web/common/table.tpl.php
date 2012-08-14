<?php
/******************************************************************************************

HOW TO USE THIS TEMPLATE? HERE IS AN EXAMPLE:

echo P("tpl")->html('common/table.tpl.php', array(
	    'title' => '',
	    'columns' => array(
			'site' => array(
	            'display' => 'Site'
	        ),
	        'user_name' => array(
	            'display' => 'Employee'
	        ),
	        'tags' => array(
	            'display' => 'Action'
	        ),
	        'method' => array(
	            'display' => 'Method',
	        	'cell_html_function' => 'entry_list_render_method'
	        ),
	        'params' => array(
	            'display' => 'Params'
	        ),
	         'time' => array(
	            'display' => 'Time'
	        )
	    ),
	    'items' => $entry_list
	));

*******************************************************************************************/
?>
<table class="datatable<?php echo_if_ne($table_class, " $table_class") ?>" <?php echo_if_ne($table_id, "id='$table_id'") ?> <?php echo_if_ne($table_style, "style='$table_style'")?> >
    <?php if (!empty($title)): ?>
    <tr>
        <th colspan="<?php echo count($columns)?>"><?php echo $title; ?></th>
    </tr>
    <?php endif; ?>
    <tr>
        <?php foreach($columns as $column) {
            if (!empty($column['header_style'])) {
                echo "<td class='column_header' style='" . $column['header_style'] . "'>";
            } else {
                echo "<td class='column_header'>";
            }
            if (isset($column['header_text'])) {
                echo $column['header_text'];
            }
            echo "</td>";
        } ?>
    </tr>
    <?php if (!empty($items)) {
        $flop = 0;
        foreach($items as $item) {
            echo "<tr class='" . (($flop==0) ? 'even' : 'odd') . "'>";
            foreach($columns as $key=>$column) {
                if (isset($column['cell_style'])) {
                    echo "<td style='" . $column['cell_style'] . "'>";
                } else {
                    echo "<td>";
                }
                if (empty($column['cell_html_function'])) {
                    echo $item[$key];
                } else {
                    echo call_user_func($column['cell_html_function'], $item);
                }
                echo "</td>";
            }
            $flop = 1-$flop;;
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='" . count($columns) . "'>There is no item in this list</td></tr>";
    }?>
</table>