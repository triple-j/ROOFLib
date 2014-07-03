<?php

if(isset($_PARAMS['table']) && isset($config['forms'][$_PARAMS['table']])) {
	$table = $config['forms'][$_PARAMS['table']]['db'];
} else {
	$formid = key($config['forms']);
	$table = $config['forms'][$formid]['db'];
}

if(empty($_PARAMS['page'])) $_PARAMS['page'] = 0;


if(isset($_PARAMS['updateTableData'])) {

	if(!empty($_PARAMS['date_start']) && !empty($_PARAMS['date_end'])) {
		$date_start = date('Y-m-d', strtotime($_PARAMS['date_start']));
		$date_end = date('Y-m-d', strtotime($_PARAMS['date_end']));
		$whereDates = " AND DATE(submit_timestamp) >= '".$date_start."' AND DATE(submit_timestamp) <= '".$date_end."' ";
	} else {
		$whereDates = '';
	}

	/* BEGIN PARSE SORT DATA */
	if(!empty($_PARAMS['sort'])) {
		list($sort_col, $sort_dir) = explode('-|-',$_PARAMS['sort']);
		$sort_qry=' ORDER BY '.$sort_col.' ';
		if($sort_dir=='d') {
			$sort_qry.=' DESC ';
			$extra_class = ' headerSortUp';
		} else {
			$extra_class = ' headerSortDown';
		}
	} else {
		$sort_col = null;
		$sort_dir = null;
		$sort_qry=' ORDER BY submit_timestamp DESC ';
		$extra_class = '';
	}
	/* END PARSE SORT */

	if(isset($_SESSION[$_PREFIX.'archive_mode']) && $_SESSION[$_PREFIX.'archive_mode']==true) {
		$counting = $mysqli->query("SELECT COUNT(*) as count FROM ".$table ." WHERE _archived=1".$whereDates);
		$qry_str = "SELECT * FROM ".$table ." WHERE _archived=1".$whereDates;
	} else {
		$counting = $mysqli->query("SELECT COUNT(*) as count FROM ".$table ." WHERE _archived!=1".$whereDates);
		$qry_str ="SELECT * FROM ".$table ." WHERE _archived!=1".$whereDates;
	}

	if ( $counting === false ) { echo "<tr><td><span class=\"error\">Error retrieving form entries.</span></td></tr>"; exit; }
	$count = $counting->fetch_assoc();

	$qry_str .= $sort_qry;
	if($count['count']>$config['results_per_page']) {
		if(!isset($_PARAMS['page']) || $_PARAMS['page']<0) $_PARAMS['page']=0;
		$qry_str .= " LIMIT ".($_PARAMS['page']*$config['results_per_page']).",".$config['results_per_page']."";
		$page_counter = '';
		$startCount = (($_PARAMS['page']*$config['results_per_page'])+1);
		$finalCount = ($startCount+$config['results_per_page']-1);
		if($finalCount>$count['count'])
			$finalCount = $count['count'];

		$page_counter .= '<strong>'.$startCount.'-'.$finalCount.' of '.$count['count'] . ' &nbsp;&nbsp;&nbsp; ';

		$num_pages = ceil($count['count']/$config['results_per_page']);
		$sort_param = isset($_PARAMS['sort']) ? $_PARAMS['sort'] : "";
		if($_PARAMS['page']>0) {
			$page_counter .= '<a href="javascript:gotoPage('.($_PARAMS['page']-1).',\''.$sort_param.'\');">&laquo; Prev</a> ';
		}
		for($x=0; $x<$num_pages; $x++) {
			if($_PARAMS['page']==$x) {
				$page_counter .= '<strong>' . ($x+1) . '</strong> ';
			} else {
				$page_counter .= '<a href="javascript:gotoPage('.$x.',\''.$sort_param.'\');">'.($x+1).'</a> ';
			}
		}
		if($_PARAMS['page']<($num_pages-1)) {
			$page_counter .= '<a href="javascript:gotoPage('.($_PARAMS['page']+1).',\''.$sort_param.'\');">Next &raquo;</a>';
		}
	}

	$qry = $mysqli->query($qry_str) or die($mysqli->error);

	$fields = $qry->field_count;
	if(isset($_PARAMS['init']) && $_PARAMS['init']=='true') {
		$_PARAMS['fields'] = $admin->manipulateFields($_PARAMS['fields']);
	}
	echo '<thead><tr>';
	echo '<th>&nbsp;</th>';
	$dialog_fields = array();
	//foreach($_PARAMS['fields'] as $field) {
	for ($i = 0; $i < $fields; $i++) {
		$qry->field_seek($i);
		$finfo = $qry->fetch_field();
		$dbfield = $finfo->name;
		if ($dbfield !== '_archived') {
			if(in_array($dbfield,$_PARAMS['fields']))	echo '<th class="header'.( ($sort_col==$dbfield) ? $extra_class : '' ).'" onclick="beginSort('.$_PARAMS['page'].',\''.$dbfield.'-|-'.( ($sort_dir=='d' && $sort_col==$dbfield) ? 'a' : 'd' ).'\'); ">'.$admin->cleanName( $dbfield ).'</th>';
			$dialog_fields[] = $admin->cleanName( $dbfield );
		}
	}
	echo '<th class="header">&nbsp;</th>';
	echo '</tr></thead><tbody>';

	$ca = 0;
	while($row = $qry->fetch_assoc()) {
		$dialog_values = array();
		$rowid = $row[ $table.'_id' ];
		echo '<tr '.(++$ca % 6 == 0 ? 'class="alt"' : (($ca + 3) % 6 == 0?'class="alt2"':'') ).' id="row_'.$rowid.'">';
		echo '<td><input type="checkbox" name="'.$_PREFIX.'check[]" value="'.$rowid.'" /></td>';
		$cols = 0;
		foreach($row as $field=>$na) {
			if ($field !== '_archived') {
				$row[$field] = strip_tags($row[$field], '<br>');
				if(preg_match_all('/FILE:(.*?);/',$row[$field],$files)) {
					$print_val = '';//print_r($files[1], true);
					foreach ($files[1] as $file_name) {
						if (! $file_name) { continue; }
	//					$print_val .= "<b>".$file_name."</b>";
						$matches = preg_match('/^DELETED:/', $file_name, $out);
//						$print_val .= "<h1>match $matches</h1>";
						if ($matches) {
							$print_val .= '<div style="white-space:nowrap"><a style="white-space:nowrap" title="'.$file_name.'">[deleted]</a></div>';
						} else {
							$print_val .= $dialog_values[] = '<div style="white-space:nowrap"><a href="'.$file_name.'" target="_blank">Download File</a> [<a href="javascript:void(0);" onclick="deleteFileOnly('.$row[$table.'_id'].', \''.$file_name.'\', this);">Delete</a>]</div>';
						}
					}
				} elseif( preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$row[$field]) ) {
					$print_val = $dialog_values[] = date('M j, Y g:i a',strtotime($row[$field]));
				} elseif(preg_match('/https?:\/\/(www\.)?(.*)(\.com|\.org|\.net|.[a-z]{2,3})$/i',$row[$field])) {
					$print_val = $dialog_values[] = '<a target="_blank" href="'.$row[$field].'">'.$row[$field].'</a>';
				} else {
					$print_val = $dialog_values[] = $row[$field];
				}
				if(in_array($field,$_PARAMS['fields'])) {
					echo '<td>'.$print_val.'&nbsp;</td>'."\r\n\t\t";
				}

				$cols++;
			}
		}
		echo '<td><a href="javascript:void(0);" onclick="openDialog('.$rowid.'); ">View Details</a>';
		/* DIALOG */
		echo '<div id="dialog_'.$rowid.'" style="display:none; ">';
			echo '<small><a href="javascript:sendEmail('.$rowid.');">Send Email</a> | <a href="javascript:printEntry('.$rowid.');">Print</a></small>';
			echo '<table id="emailTable_'.$rowid.'">';
			foreach($dialog_fields as $key=>$title) {
				if ($title !== 'Archived') {
					echo '<tr valign="top"><td><b>'.$title.': </b></td><td>'.$dialog_values[$key].'</td></tr>';
				}
			}
			echo '</table>';
		echo '</div>';

		/* Assign Vars on parent page */
		?>
		<script type="text/javascript">currentSort = '<?php echo $_PARAMS['sort']; ?>'; currentPage = '<?php echo $_PARAMS['page']; ?>'; </script>
		<?php
		echo '</td>';

		echo '</tr>';
	}

	echo '</tbody>';
	if(!empty($page_counter)) {
		echo '<tfoot>';
		echo '<tr><td colspan="'.($cols+2).'">'.$page_counter.'</td></tr>';
		echo '</tfoot>';
	}

}

exit; // HACK: this bypasses ob_start()/ob_get_clean() and just outputs the buffer as is
