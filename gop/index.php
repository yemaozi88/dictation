<?php
/*
 * 2015/09/13
 * index page for reading quiz of yamauchi project
 *
 * SEND
 * intro.php
 *
 * NOTE
 * index.php is based on wm/quiz/index.php
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
include("../_class/c_pagestyle.php");


// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$logoDir   = $config["logoDir"];

//$isTest    = $_GET['isTest'];
$isTest = 0; 

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
	isTest: $isTest</br>
	pageTitle: $pageTitle</br>
	";
}

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="intro.php" method="POST">

	<h2>基本情報</h2>
EOF;


echo <<<EOF
	<h3>お名前（半角英数字のみ、スペースは含めない）を入力してください。</h3>
	<table>
		<tr>
			<td>お名前</td>
			<td><input type="text" name="UserName" size=25></td>
		</tr>
	</table>
EOF;

if($isTest == 1)
{
echo <<<EOF
        </br>
	<h3>学校名または所属先を選択してください</h3>
EOF;

$i_pagestyle->print_groupSelecter($logoDir);
}

echo <<<EOF
	<br>
<!-- send variables to the next page as hidden -->
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<input type="submit" value="OK">
</form>
EOF;

$i_pagestyle->print_footer();
?>