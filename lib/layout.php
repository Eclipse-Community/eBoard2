<?php
//Layout functions, by Nikolaj

function cell() {
	global $cell;
	$cell = ($cell == 0 ? 1 : 0);
	return $cell;
}

function tab($label, $id) {
	global $firstBtn;
	if (!$firstBtn) $sel = " selected";
	else $sel = "";
	$firstBtn = true;
	Write("
				<button id=\"{0}Button\" class=\"tab{1}\" onclick=\"showEditProfilePart('{0}');\">{2}</button>
	", $label, $sel, $id);
}

function row($label, $input = array(), $settings = false) {
	if ($input['id']) $id = $input['id'];
	else $id = $settings['id'];
	if ($settings['content'])
		$in = $settings['content'];
	else {
		$in = "";
		if ($settings['prepend']) $in .= $settings['prepend'];
		$in .= '<input type="'.$input['type'].'" name="'.$input['name'].'"';
		if (!$input['id']) $in .= ' id="'.$input['name'].'"';
		else $in .= ' id="'.$id.'"';
		if ($input['class']) $in .= ' class="'.$input['class'].'"';
		if ($input['value']) $in .= ' value="'.htmlspecialchars($input['value']).'"';
		if ($input['ext']) $in .= " ".$input['ext'];
		$in .= ' />';
		if ($settings['append']) $in .= $settings['append'];
	}
	Write('
			<tr class="cell{0}">
				<td>
					<label for="{1}">
						{2}
					</label>
				</td>
				<td>
					{3}
				</td>
			</tr>', cell(), $id, $label, $in);


}

function head($value, $type = 0) {
	global $cell;
	Write('
			<tr class="header{1}">
				<th colspan="2">
					{0}
				</th>
			</tr>', $value, $type);
}

function table($id, $hidden = false, $width = 50) {
	global $cell;
	$cell = 1;
	Write('
		<table class="outline margin width{1} eptable"'.($hidden == true ? 'style="display: none;"' : '').' id="{0}">
	', $id, $width);
}

function endTable() { //For consistancy's sake.
	Write("
		</table>
	");
}