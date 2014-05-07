<?php

if(isset($_REQUEST['switchModes'])) {
	if($_REQUEST['switchModes']=='archive') {
		$_SESSION['archive_mode'] = true;
	} else {
		unset($_SESSION['archive_mode']);
	}
}

if(isset($_REQUEST['table']) && isset($config['forms'][$_REQUEST['table']])) {
	$table = $config['forms'][$_REQUEST['table']]['db'];
} else {
	$formid = key($config['forms']);
	$table = $config['forms'][$formid]['db'];
}

if(isset($_REQUEST['export'])){
	require_once( dirname(__FILE__).'/includes/XSpreadsheet.php' );

	if($_SESSION['archive_mode']==true) {
		$where = ' WHERE _archived=1 ';
	} else {
		$where = ' WHERE _archived!=1 ';
	}

	if(!empty($_POST['date_start']) && !empty($_POST['date_end'])) {
		$date_start = date('Y-m-d', strtotime($_POST['date_start']));
		$date_end = date('Y-m-d', strtotime($_POST['date_end']));
		$where .= " AND DATE(submit_timestamp) >= '".$date_start."' AND DATE(submit_timestamp) <= '".$date_end."' ";
	}

	$qry = $mysqli->query("SELECT `".implode("`, `",$_POST['fields'])."` FROM ".$table ." ".$where) or die($mysqli->error);
	$qHeaders = array(); foreach($qry->fetch_fields() as $qField) { $qHeaders[] = $qField->name; }
	$qData = array(); while($qRow = $qry->fetch_row()) { $qData[] = $qRow; }
	$a = new XSpreadsheet($table.'_ss_'.date('Ymd').'.xml.xls', false);
	$a->AddWorkbook( $table, $qData, $qHeaders )->Generate()->Send($_REQUEST['zipit']);
	exit;
}

if(isset($_GET['sendEmail'])) {
	require_once( dirname(__FILE__).'/../classes/class.phpmailer.php' );

	$mail = new PHPMailer();
	$mail->IsHTML(true);

	$mail->From = 'info@ecreativeworks.com'; // the email field of the form
	$mail->FromName = 'info@ecreativeworks.com'; // the name field of the form

	//$mail->AddAddress('kevin@ecreativeworks.com'); // the form will be sent to this address
	$mail->AddAddress($_POST['email']); // the form will be sent to this address
	$mail->Subject = 'FW: Form Entry from '.$_SERVER['HTTP_HOST']; // the subject of email

	// html text block
	$emailQry = $mysqli->query("SELECT * FROM ".$table." WHERE ".$table."_id = ".$_POST['content']."");
	$row = $emailQry->fetch_assoc();
	$content = '<table>';
	foreach($row as $field=>$value) {
		$content .= '<tr><td><b>'.$admin->cleanName($field).'</b></td><td>';
		if(preg_match('/^FILE:(.*)$/',$row[$field],$file)) {
			if(!$mail->AddAttachment('../'.$file[1], basename($file[1]))) {
				$minor_error = ' The attachment however, was not sent.';
				$content .= 'Error Attaching File';
			} else {
				$content .= '(attached)';
			}
		} else {
			$content .= ''.$row[$field].'&nbsp;';
		}
		$content .= '</td></tr>';
	}
	$content .= '</table>';

	$mail->Body = $content . "\n";

	if($mail->Send()) {
		echo 'Email sent successfully!'.$minor_error;
	} else {
		echo 'There was an error sending the message!';
	}
	exit;
}

if(isset($_POST['withChecked'])) {
	$return = array();
	foreach($_POST['check'] as $key=>$value) {
		$result = $mysqli->query("SELECT * FROM ".$table." WHERE ".$table."_id = '".$value."' LIMIT 1");
		$row = $result->fetch_object();
		if($_POST['withChecked']=='delete') {
			$filesQry = '';
			foreach($row as $kfile=>$sfile) {
				if(preg_match('/^FILE:(.*)$/i',$sfile,$file)) {
					@unlink('../'.$file[1]);
					$filesQry .= ", ".$kfile."='".$file[1]." [deleted]'";
				}
			}
			$mysqli->query("DELETE FROM ".$table." WHERE ".$table."_id = '".$value."' LIMIT 1");
		} elseif($_POST['withChecked']=='archive') {
			$mysqli->query("UPDATE ".$table." SET _archived = 1".$filesQry." WHERE ".$table."_id = '".$value."' LIMIT 1");
		} elseif($_POST['withChecked']=='unarchive') {
			$mysqli->query("UPDATE ".$table." SET _archived = 0".$filesQry." WHERE ".$table."_id = '".$value."' LIMIT 1");
		} elseif($_POST['withChecked']=='deletefileonly') {

			foreach($row as $kfile=>$sfile) {
				if(preg_match_all('/FILE:(.*?);/', $sfile, $files)) {
					$deletefile = $_POST['deletefile'];
					$files = $files[1];

					$new_value = '';
					foreach ($files as $file) {
						if ($deletefile == $file) {
							@unlink('../'.$file);
							$new_value .= "FILE:DELETED:".$file.";";
						} else {
							$new_value .= "FILE:".$file.";";
						}
					}
					$filesQry .= ", ".$kfile."='".$new_value."'";
				}
			}
			$mysqli->query("UPDATE ".$table." SET _archived = _archived".$filesQry." WHERE ".$table."_id = '".$value."' LIMIT 1");
		}
		$return[] = $value;
	}
	if($_POST['withChecked']=='deletefileonly') {
		array_unshift($return,'FILE');
	}

	echo implode('|',$return);
	exit;
}

?>

	<script language="javascript">

		// Vars section
		var currentSort = '';
		var currentPage = 0;

		$(document).ready(function() {

			$("#filterBox, #ui-datepicker-div").click(function(e) {
				e.stopPropagation();
			});

			$("#filterClick").click(function(e) {
				if($("#clicker").is(':visible')) {
					$("#clicker").hide();
				} else {
					$("#clicker").show();
					e.stopPropagation();
					$(document).one('click', function(e) { $("#clicker").hide(); });
				}
			});
			$("#filterForm input:checkbox").click(function(e) {
				getTableData(null, currentPage, currentSort);
			});

			$("#filterForm .filterdate").change(function(e) {
				if($("#date_start").val()!='' && $("#date_end").val()!='') {
					getTableData(null, currentPage, currentSort);
				}
			});

			$(".datepicker").datepicker();

			getTableData(true);

			//$(window).wresize(reshapeBox);

			//reshapeBox(true);

			if($.browser.msie==true && $.browser.version == 6) {
				$("#archiveButton").css({marginTop: -25});
			}

		});

		function allCheckboxes(checked) {
			$("input[name='fields[]']").attr('checked', checked);
			getTableData();
		}

		function getTableData(initialSetup, sendPageNumber, sendSortOrder) {
			var page = '';
			var sortOrder = '';
			var init = '';

			var filters = $("#filterForm").serializeArray();

			if(initialSetup) init = '&init=true';
			if(sendPageNumber) page = '&page='+sendPageNumber;
			if(sendSortOrder) sortOrder = '&sort='+sendSortOrder;

			$.post('<?=$config['current_page'];?>?ajax<?php if(isset($_GET['table'])) echo '&table='.$_GET['table']; ?>'+init+page+sortOrder, filters, function(data) {
				$("#filterTable").html(data);
				updateTable();
			});
		}
		function gotoPage(page, sort) {
			getTableData(null, page, sort);
		}
		function beginSort(page, sort) {
			getTableData(null, page, sort);
		}
		function performAction() {
			var check = $("#withChecked").val();
			var approved = false;
			if(check == 'delete') {
				if(confirm('Are you sure you wish to delete the selected entries? This cannot be undone!')) {
					approved = true;
				}
			} else if(check == 'deletefileonly') {
				if(confirm('Are you sure you wish to delete the selected entry files? This cannot be undone!')) {
					approved = true;
				}
			} else if(check == 'archive') {
				approved = true;
			} else if(check == 'unarchive') {
				approved = true;
			}
			if(approved) {
				$.post('<?=$config['current_page'];?><?php if(isset($_GET['table'])) echo '?table='.$_GET['table']; ?>', $("#rowForm").serializeArray(), function(data) {
					var rows = data.split("|");

					if(rows[0] == 'FILE') {
						for(var i in rows) {
							$("#row_"+rows[i]+" td").animate({backgroundColor:'#FFCCCC'},400).animate({backgroundColor:'transparent'},400);
							$("#row_"+rows[i]).find(':input').attr('checked', false);
						}
					} else {
						for(var i in rows) {
							$("#row_"+rows[i]).fadeOut('slow', function(e) { updateTable(); });
						}
					}
				});
			}
		}

		function deleteFileOnly(id, file, link) {
			if(confirm('Are you sure you wish to delete the selected entry files? This cannot be undone!')) {

				$.post('<?=$config['current_page'];?><?php if(isset($_GET['table'])) echo '?table='.$_GET['table']; ?>', {'check[]': [id], 'withChecked': 'deletefileonly', 'deletefile':file}, function(data) {
					var rows = data.split("|");

					if(rows[0] == 'FILE') {
						var title = 'DELETED:'+$(link).prev('a').attr('href').replace('../', '');
						var p = $(link).parent().parent();
						//RAY- WORK ON THIS
						$(link).parent().remove();
						p.append($('<a title="'+title+'">[deleted]</a>'));
/*
						var text = '[deleted]';//$(link).prev('a').attr('href').replace('../','') + ' [deleted]';
						$(link).prev('a').html(text);
						$(link).attr('title', $(link).prev('a').attr('href'));
						$(link).attr('href', ''); */
						$("#row_"+rows[1]+" td").animate({backgroundColor:'#FFCCCC'},400).animate({backgroundColor:'transparent'},400);
					} else {
						alert(data);
					}
				});
			}
		}

		function openDialog(id) {
			$("#dialog_"+id).clone().appendTo("body").show().dialog({
				bgiframe: true,
				close: function(ev, ui) {
					$(this).remove();
				},
				title:'User Entry '+id, height: 500, width:350
			});

		}
		function sendEmail(id) {
			var emailAddress = prompt('Please enter the email address you would like to send this entry to:','');
			if(emailAddress) {
				$.post('<?=$config['current_page'];?>?<?php if(isset($_GET['table'])) echo 'table='.$_GET['table']; ?>&sendEmail=true',{content:id, email:emailAddress}, function(data) {
					if(data) {
						alert(data);
					}
				});
			}
		}
		function printEntry(id) {
			var printContent = '<div><h2>User Entry '+ id +'</h2><table>' + $('#emailTable_'+id).html() + '</table></div>';
			$(printContent).printarea();
		}

		function updateTable() {
			//$(".tablesorter").tablesorter();
			$("#filterTable tr td").removeClass("odd");
			$("#filterTable tr:odd td").addClass("odd");
			$("#filterTable tr td a").click(function(e) { e.stopPropagation(); });
			$("#filterTable tr td input").click(function(e) { $(this).parent('td').parent('tr').click(); });
			$("#filterTable tr").click(function(e) {
				var mCheck = $(this).find('td:first input:checkbox');
				if(mCheck.attr('checked')==true) {
					$(this).removeClass('rowSelected');
					mCheck.attr('checked', false);
				} else {
					$(this).addClass('rowSelected');
					mCheck.attr('checked', true);
				}
			});
			//$("#filterTable tr td input:checkbox").change(function(e) { alert('changed!!!'); });
			/*$("#filterTable tr td:first input:checkbox").click(function(e) {
				if($(this).attr('checked')==true) {
					$(this).parent('tr').addClass('rowSelected');
				} else {
					$(this).parent('tr').removeClass('rowSelected');
				}
			});*/
		}

		function updateTableHighlights() {
			$("#filterTable tr").removeClass('rowSelected');
			$("#filterTable tr").each(function(i) {
				if($(this).find('input').attr('checked')==true) {
					$(this).addClass('rowSelected');
				}
			});
		}

		function switchModes(uid) {
			$.get('<?=$config['current_page'];?>', {switchModes: uid}, function(data) {
				getTableData();
			});
		}

<?php include(dirname(__FILE__).'/js/jquery.printarea.js'); ?>

	</script>

<?php if( isset($_SESSION['archive_mode']) && $_SESSION['archive_mode']==true) { ?>
	<div style="float: right; margin: 0px 3px 10px 0px; >margin-right: 0px;"><button class="hoverPointer" onclick="switchModes('live');location.href='<?=$config['current_page'];?>?switchModes=live<?php if(isset($_GET['table'])) echo '&table='.$_GET['table']; ?>';">Switch to <strong>Live Mode</strong></button></div>
	<div style="float: right; color:#C00; font-weight:bold; font-size:18px; margin: 0px 10px 10px 0px;">Archive Mode</div>
<?php } else { ?>
	<div style="float: right; margin: 0px 3px 10px 0px; >margin-right: 0px;"><button class="hoverPointer" onclick="switchModes('archive');location.href='<?=$config['current_page'];?>?switchModes=archive<?php if(isset($_GET['table'])) echo '&table='.$_GET['table']; ?>';">Switch to <strong>Archive Mode</strong></button></div>
	<div style="float: right; color:#000; font-weight:bold; font-size:18px; margin: 0px 10px 10px 0px;">Live Mode</div>
<?php } ?>
<div style="clear: right;"></div>

<div style="-moz-border-radius:4px; background:#ededed; padding:3px; margin-bottom:12px; /*height:24px;*/ ">
	<div id="filterBox">
		<!--<a href="javascript:void(0);" id="filterClick" style="background:url(../images/btn_filter.png) no-repeat; width:100px; height:24px; display:block; margin:0px;"></a>-->
		<button id="filterClick">Filter/Sort</button>
		<div id="clicker" style="display:none; ">
		<span id="clickerContents">
		<span class="clickable" onClick="allCheckboxes(true)">[Select All]</span> &mdash; <span class="clickable" onClick="allCheckboxes(false)">[Clear All]</span>

		<form style="margin-top:10px;" method="post" action="<?=$config['current_page'];?><?php if(isset($_GET['table'])) echo '?table='.$_GET['table']; ?>" onsubmit="" id="filterForm">
		<input type="hidden" name="updateTableData" value="true" />
		Dates From <input type="text" class="datepicker filterdate" name="date_start" id="date_start" style="width:80px;" /> to <input type="text" class="datepicker filterdate" name="date_end" id="date_end" style="width:80px;" />
		<a class="clickable" href="javascript:void(0);" onclick="$('.filterdate').val(''); getTableData(); ">[Clear Dates]</a>
		<div style="height:10px;">&nbsp;</div>
		<?php
		$fieldQry = $mysqli->query("Show Columns From `{$table}`;");

		if ( $fieldQry !== false ) {
			$fieldsEmpty = !isset($_POST['fields']);
			#$count = 0;
			$opts = "";

			while($fieldRow = $fieldQry->fetch_array()) {
				$field = $fieldRow['Field'];
				#if($sort === '1') { $sort = $field; }
				if($fieldsEmpty) { $_POST['fields'][] = $field; }

				$opts .= '<option value="'.$field.'" '.((isset($_POST['sort']) && $field == $_POST['sort'])? 'selected="selected"' : '').'>'. $admin->cleanName( $field ) .'</option>';
				#$count++;
			}
			// REMOVE EXTRA FIELDS ON LOAD...
			$allowedFields = $admin->manipulateFields($_POST['fields']);
			foreach($_POST['fields'] as $field) {
				echo '<label><input type="checkbox" name="fields[]" value="'.$field.'" '.((in_array($field, $allowedFields))?'checked="checked"':'').' />'. $admin->cleanName($field) . '</label>';
			}
		}
		?>

		<div style="display:none;">
		<input type="submit" name="submits" id="filterButton" onclick="getTableData(); return false;" value="Update Form">
		<input type="submit" name="export" id="exportButton" value="Export"><input type="checkbox" name="zipit" value="true" style="vertical-align: middle;">Zip
		</div>
		</form>
		</span>
		</div>
	</div>
	<div style="float:right; padding-right:20px;">
		<button onclick="$('#exportButton').click();">Export To Excel</button>
	</div>
</div>
<form action="" method="post" onsubmit="performAction(); return false;" id="rowForm" style="/*position:relative;*/">
	<div style="/*position:absolute; left:2px; top:-39px; >top:-46px; */">
		<div style="/*float:left; margin:3px;*/">
		Select Form: <select name="table" onchange="document.location.href = '<?=$config['current_page'];?>?table='+this.value;">
			<?php
				foreach($config['forms'] as $key=>$value) {
					echo '<option value="'.$key.'"'. ( ($_REQUEST['table']==$key) ? ' selected="selected"' : '' ).'>'.$value['name'].'</option>';
				}
			?>
		</select>
		</div>

		<div style="/*float:left; padding-left:20px; margin:1px;*/">
			With Checked:
			<select name="withChecked" id="withChecked">
				<?php if($_SESSION['archive_mode']!=true) { ?>
					<option value="archive">Archive</option>
				<?php } else { ?>
					<option value="unarchive">Un-Archive</option>
				<?php } ?>
				<option value="deletefileonly">Delete File</option>
				<option value="delete">Delete</option>
			</select>

			<input type="submit" value="Submit" />
		</div>
	</div>

<div id="tableHolder">
<table class="tablesorter" id="filterTable" cellpadding="4" cellspacing="1" border="0">
	<?php
	 /* AJAX IS CALLED ON PAGE LOAD TO FILL THIS TABLE */
	?>
</table>
</div>
<div id="pager"></div>
<div id="subGreyBar" style="-moz-border-radius:4px; background:#ededed; padding:4px; margin-top:12px;">
	<table id="subGreyBarTable" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="150">
				<a href="javascript:void(0);" class="clickable" onclick="$('#rowForm input:checkbox').attr('checked',true); updateTableHighlights(); ">[Check All]</a>
				<a href="javascript:void(0);" class="clickable" onclick="$('#rowForm input:checkbox').attr('checked',false); updateTableHighlights(); ">[Uncheck All]</a>
			</td>
			<td align="center">
				<span style="color:#C00; font-size:10px;">Notice: Records are archived automatically after 90 days.  All file attachments older than 90 days will be deleted.</span>
			</td>
		<td width="150" align="right"><!--a href="login.php?out">Logout</a--></td>
	</tr>
	</table>
</div>
</form>
