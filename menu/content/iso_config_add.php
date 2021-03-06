<?php

/* ********************************************************************\
|                                                                      |
|   Copyright (c) 2011 Thimbleweed Consulting. All Rights Reserved     |
|                                                                      |
|                  This file is part of All-In-USB.                    |
|                                                                      |
| All-In-USB is free software: you can redistribute it and/or modify   |
| it under the terms of the GNU General Public License as published    |
| by the Free Software Foundation, either version 3 of the License,    |
| or (at your option) any later version.                               |
|                                                                      |
| All-In-USB is distributed in the hope that it will be useful, but    |
| WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANT- |
| ABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General     |
| Public License for more details.                                     |
|                                                                      |
| You should have received a copy of the GNU General Public License    |
| along with All-In-USB. If not, see <http://www.gnu.org/licenses/>    |
|                                                                      |
\******************************************************************** */

// ############################################################################
// # Define some base variables
// ############################################################################

include "functions.php";
$Root = getRoot();

// ############################################################################
// # If Saving Build, then Write, .TWC file
// ############################################################################

if($_REQUEST["action"] == "save" && $_REQUEST["image"])
	{
	$file = $Root."\\isos\\".str_replace(".","",strtoupper(uniqid("BOOT-",true))).".twc";

	unset($_REQUEST["action"]);
	unset($_REQUEST["grub_defaults"]);
	$_REQUEST["grub"] = base64_encode(stripslashes(trim($_REQUEST["grub"])));
	foreach($_REQUEST AS $Field => $Value) { $bootIso["iso"][$Field] = $Value; }
	writeConfig($file,$bootIso);
	unset($_REQUEST);
	$Msg = "Saved Boot Menu Item";
	}

?><html>
<head>
<title>Edit Configuration Files</title>
<link type="text/css" href="css/<?php echo $config["jQuery"]["ui"]; ?>/jquery-ui-<?php echo $config["jQuery"]["ui_ver"]; ?>.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-<?php echo $config["jQuery"]["jq_ver"]; ?>.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-<?php echo $config["jQuery"]["ui_ver"]; ?>.min.js"></script>
<script>

$(function() {
	$(".refresh").button( { icons: { primary: "ui-icon-refresh" } });
	$(".tool_add").button( { icons: { primary: "ui-icon-plus" } });
	$(".tool_cnf").button( { icons: { primary: "ui-icon-link" } });
	$(".tool_del").button( { icons: { primary: "ui-icon-close" } });
	$(".zebra tr:even").addClass("alt");
	});

</script>
<style type="text/css">

BODY { font-size: 70%; }
BUTTON { width: 250px; }
FORM { padding: 0px; margin: 0px; }

.zebra { margin-bottom: 10px; }
tr.alt td { background-color: <?php echo $config["colors"]["zebra"]; ?>; }
tr.activeRow tr td { background-color: #FF0000; }

</style>
<script type="text/javascript">

function useDefaultRecipe(Fld,FldGrub)
	{
	tVal = Fld.options[Fld.selectedIndex].value;
	tFld = document.getElementById(FldGrub);

	if(tFld.value.length > 1)
		{
		if(!confirm("Replace Current Recipe?"))
			{ return; }
		}

	switch(tVal)
		{
<?php

		foreach($Recipes AS $Recipe => $Lines)
			{
			echo '		case "'.$Recipe.'":'."\n";
			echo '			tFld.value = ""'."\n";
			foreach($Lines AS $Line)
				{
				echo '			tFld.value += "'.$Line.'\\n"'."\n";
				}
			echo '			break;'."\n\n";
			}

?>
		}
	}

function validateEdit(F)
	{
	<?php

		foreach($isoFields AS $isoField => $Params)
			{
			echo "\n";
			if($Params["required"])
				{
				echo "tFld = F.".$isoField.";\n";
				switch($Params["type"])
					{
					case "text":
					case "area-grub":
						echo "if(tFld.value.length < 1) { alert('Missing ".$Params["label"]."'); return false; }\n";
						break;

					case "sel-tab":
						echo "if(tFld.selectedIndex < 1) { alert('Missing ".$Params["label"]."'); return false; }\n";
						echo "tFldOth = F.".$isoField."_other;\n";
						echo "if((tFld.options[tFld.selectedIndex].value == 'new-tab') && tFldOth.value.length < 1) { alert('Missing Other Value for ".$Params["label"]."'); return false; }\n";
						break;

					case "sel-iso":
						echo "if(tFld.selectedIndex < 1) { alert('Missing ".$Params["label"]."'); return false; }\n";
						break;
					}
				}
			}

	?>
	return true;
	}


</script>
</head>
<body>

<?php echo $Msg; ?>

	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all" style="width: 800px; padding: 10px; text-align: center; margin-left: auto; margin-right: auto; margin-bottom: 10px; ">
		<form method="post" action="<?php echo basename(__FILE__); ?>" onsubmit="return validateEdit(this);">
		<input type="hidden" name="action" value="save">
		<table border='0' cellspacing="0" cellpadding="5" width="800" class="zebra">
			<?php foreach($isoFields AS $isoField => $Params) { ?>
				<tr valign="top">
					<td><?php echo $Params["label"]; ?>: </td>
					<td>
						<?php
							switch($Params["type"])
								{
								case "sel-iso":
									echo '<select name="'.$isoField.'" id="'.$isoField.'">'."\n";
									echo "	<option></option>\n";
									$isos = glob($Root."\\isos\\*.*");
									foreach($isos AS $iso)
										{
										if(substr($iso,-4) != ".twc")
										{
										echo "	<option value='".basename($iso)."'";
										echo ">".basename($iso)."</option>\n";
										}
									}
								echo "</select>\n";
								break;

								case "text":
									echo '<input type="text" name="'.$isoField.'" id="'.$isoField.'" value="'.$tCap[$isoField].'" size="'.$Params["width"].'" />'."\n";
									break;

								case "area-grub":
									echo '<textarea name="'.$isoField.'" id="'.$isoField.'" rows="'.$Params['height'].'" cols="'.$Params['width'].'" wrap="virtual">';
									if($tCap["grub"]) { echo base64_decode($tCap["grub"]); }
									echo '</textarea>'."\n";
									echo "<br />Insert %image% where the file name should appear in your recipe.\n";
									echo "<br />Default Recipes &raquo;\n";
									echo "<select name='".$isoField."_defaults' onchange=\"useDefaultRecipe(this,'".$isoField."')\">\n";
									echo "	<option value=''></option>\n";
									foreach(array_keys($Recipes) AS $Recipe)
										{
										echo "	<option value='".$Recipe."'>".$Recipe."</option>\n";
										}
									echo "</select>\n";
									break;

								default:
									pecho($Params);
									break;

								}
						?>
					</td>
				</tr>
			<?php } ?>
			</table>
			<button type="submit" class="tool_cnf">Save Configuration</button>
		</form>
	</div>

</body>
</html>