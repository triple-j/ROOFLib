<?php

$form_dir = '../ROOFLib/lib/classes/';

require_once($form_dir.'class.form.php');


$title = $h1 = 'FormsRM Docs';

?>

<!DOCTYPE html>
<html>
	<head>
	<title><?= $title ?></title>
	<link href='http://fonts.googleapis.com/css?family=Arvo:700' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script>
$(function() {
	$(".block_comment").mouseover(function() {
		$(this).addClass("focus");
		$("#output").addClass("focus");
	}).mouseout(function() {
		$(this).removeClass("focus");
		$("#output").removeClass("focus");
	});
});
	</script>
	<style>

		* { font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 11px; }
		table { font-size:12px; line-height:1.6em; border:1px solid #ccc; border-collapse:collapse; }
		.datatable-wrap { width: 100%; overflow: auto; }
		table td b { color:#00c; }
		code { font-family: Courier, monospace; background-color: #ffc; display:block; padding:10px; font-size: 11px;  }
		.row_odd { background-color:#fff; }
		.row_even { background-color:#efe; }
		.row_stephen { background-color:#eee; }
		th { border:1px solid #ccc; padding:5px; text-align: left; font-style:italic;; }
		td { border:solid #ccc; padding:5px; border-width:0px 1px; }
		h1 {
			font-family: 'Arvo', Rockwell, Georgia;
			font-size: 24px;
			margin:0px 0px 10px;
			border-bottom: 1px solid #ccc;
		}

		.code, .block_comment {
		  -webkit-transition-property: background-color, color;
			-moz-transition-property: background-color, color;
			-o-transition-property: background-color, color;
			-ms-transition-property: background-color, color;
			transition-property: background-color, color;
			-webkit-transition-duration: 200ms;
			-moz-transition-duration: 200ms;
			-o-transition-duration: 200ms;
			-ms-transition-duration: 200ms;
			transition-duration: 200ms;
			-webkit-transition-timing-function: ease-out;
			-moz-transition-timing-function: ease-out;
			-o-transition-timing-function: ease-out;
			-ms-transition-timing-function: ease-out;
			transition-timing-function: ease-out;  
		}

		.code.focus {
			background-color:#333;
			color:#ccc;
		}

		.block_comment.focus {
			background-color:#fff;
			color:#000;
		}

		#css_name { float:left; padding-right: 15px; }
		#css_type { float:left; }
		#css_description { clear:both; }
		#css_description textarea { width:348px; }
		.fldName { font-weight: bold; margin-top: 10px; }
		.fbu { margin:10px 0px; }
		.fbu * { margin-right:10px; }
		#push { height:50px; }
		#page { padding:10px; }
		#page_container { height:auto !important; min-height:100%; margin:0px auto -50px;}

		#footer { height:49px; border-top: 1px solid #ccc; color:#666; }

		html, body { height:100%; padding:0px; margin:0px; }

		.entry { width:300px; text-align:center; float:left; }
		.entry img { width:64px; margin: 0px auto; display:block;  }
		.entry .main { color:#333; font-family:Arial; font-size:16px; text-decoration:none; font-weight:bold;}

		#text_w { width:800px; height:400px; }

		#css_output, #css_text { float:left;}
		#css_output {  margin:0px 10px; }
		#results .fldValue { padding:10px; margin-top:2px; border:1px solid #ccc; }
		.fbu { clear:both; }

		.block_comment { font-weight:bold; color:#0099FF; font-family:monospace; }
		.code, .code * { font-family:monospace; }

		.control_structs { color:#CC6600; }
		.access { color: #f00; }
		.structures { color: #083; }
		.modifiers { color: #00f; }
		.system_functions { color: #c0f; }
		.preg { color: #083; }
		.php { color: #830; }

		#func_nav li a { text-decoration:none; }

	</style>
</head>
<body>
<div id="page_container">
<div id="page">
<h1><?= $h1.(isset($_GET['file'])?' &ndash; '.$_GET['file']:'') ?></h1>
<?php

	$success = false;


	if ( isset($_GET['file'])) {
		$success = true;
		$file = str_replace('..', '', $_GET['file']);
		$fullpath = $form_dir.$file;
		if (file_exists($fullpath)) {

			function process($regex, $input, $before, $after) {
				global $limit;
				$output = '';

				while (preg_match($regex, $input, $matches, PREG_OFFSET_CAPTURE)) {
					$output .= substr($input, 0, $matches[1][1]).$before.$matches[1][0].$after;
					$input = substr($input, $matches[1][1] + strlen($matches[1][0]));
					$limit --;
					if ($limit < 0) {
						break;
					}
				}

				$output .= $input;
				return $output;
			}

			$full_text = htmlentities(file_get_contents($fullpath));
			$limit = 100;

			$token_lists = Array(
				'control_structs' => Array('return', 'foreach', 'function', 'while', 'if', 'else', '=>', '::', 'extends'),
				'access' => Array('require', 'require_once', 'include', 'include_once', '__construct'),
				'structures' => Array('class', 'array', 'object', 'this', 'parent', 'self'),
				'modifiers' => Array('public', 'protected', 'private'),
				'system_functions' => Array('is_empty', 'is_null', 'length', 'sizeof', 'reset', 'array_keys', 'key'),
				'preg' => Array('preg_match', 'preg_match_all', 'preg_split', 'preg_replace'),
				'php'=> Array('<\?php', '\?>', '<\?=', '<\?'),
			);

			$output = process('/function\s*([a-zA-Z_]+)\s*\(/s', $full_text, '<a class="func_def" name="{content}">', '</a>');
			$output = process('/(\/\*.*?\*\/)/s', $output, '<rmc class="block_comment">', '</rmc>');

			$output = preg_replace('/(\{content\})([^\>]*)\>([^\<]+)\</s', '$3$2 func_def="$3">$3<', $output);

			preg_match_all('/func_def="([^\"]+)"/s', $output, $matches);

			$nav = '<div id="func_nav"><h2>Functions:</h2><ul>';
			$functions = $matches[1];
			asort($functions);
			foreach ($functions as $func_name) {
				$nav .= '<li><a href="#'.$func_name.'">'.$func_name.'</a></li>';
			}
			$nav .= '</ul>';




			foreach ($token_lists as $class => $token_list) {
				foreach ($token_list as $token) {
					$output = process('/[\?\;\.\:\}]?('.$token.'[\(]?)[\ \(;]/si', $output, '<rmc class="'.$class.'">', '</rmc>');
				}
			}







			echo $nav;

			echo '<pre class="code" id="output">'.$output.'</pre>';
		} else {
			$success = false;
		}
	}

	if (! $success) {
		echo '<h2>Form</h2><ul><li><a href="?file=class.form.php">Form</a></li></ul>';
		echo '<h2>Form Items</h2>';
		echo "<ul>";
		foreach (Form::$FORMITEMS as $name => $description) {
			echo '<li><a href="?file=class.'.strtolower($name).'.php">'.$name.'</a> &ndash; '.$description.'</li>';
		}
		echo "</ul>";
	}


?>
</div>
<div id="push">
</div>
</div>
<div id="footer">
	<div style="padding:10px;">
		&nbsp;
	</div>
</div>
</body>
</html>