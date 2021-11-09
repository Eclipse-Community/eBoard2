<?php
/* To install the captcha, download Securimage from
 *
 *	--->	http://www.phpcaptcha.org	<---
 *
 * and extract it into a /securimage folder.
 */

include 'securimage/securimage.php';

$img = new securimage();

$img->image_width = 160;
$img->image_height = 60;
$img->iscale = 1;
$img->image_bg_color = new Securimage_Color(0x8d, 0x8d, 0x8d);
$img->use_transparent_text = true;
$img->num_lines = 30;
$img->line_color = new Securimage_Color(0x6d, 0x6d, 0x6d);
$img->draw_lines_over_text = false;

//This is basically the default setting, but in the Securimage directory instead of the board root.
$img->ttf_file = "securimage/AHGBold.ttf";

$img->show('');
