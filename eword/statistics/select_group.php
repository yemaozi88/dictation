<?php
/*
 * 2013/12/31
 * group selector of the statistics for english word quiz of yamauchi project
 *
 * SEND
 * select_date.php
 *
 * HISTORY
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = false;

$config  = parse_ini_file("config.ini", false);
include("../../_class/c_pagestyle.php");


// ====================

$pageTitle = $config["pageTitle"];
$srcDir    = $config["srcDir"];
$logoDir   = $config["logoDir"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

// ====================


// ====================
// output page
// ====================

$i_pagestyle->print_header();

if($isDebug == true)
{
	echo "
	pageTitle: $pageTitle</br>
	";
}

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="select_date.php" method="POST">

	<h2>どの学校のどのクイズの統計を見ますか？</h2>
    <h3>学校名</h3>
EOF;

$i_pagestyle->print_groupSelecter($logoDir);

echo <<<EOF
    <h3>クイズの種類</h3>
    <b>英単語クイズ</b></br>
    <input type="radio" name="withWav" value="0" checked="checked">
    見て答える問題</br>
    <input type="radio" name="withWav" value="1">
    聴いて答える問題</br>
    </br>
    <input type="submit" value="OK">
</form>
EOF;

$i_pagestyle->print_footer();
?>