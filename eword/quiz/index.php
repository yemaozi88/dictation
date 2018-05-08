<?php
/*
 * 2012/10/13
 * index page for english word quiz of yamauchi project
 *
 * SEND
 * intro.php
 *
 * NOTE
 * index.php is based on index.cgi
 *
 * HISTORY
 * 2013/06/27 combine practice and test modes.
 * 2013/06/02 changed word_practice into test mode (no result, SQL settings etc.)
 * 2012/10/23 added a question set selector
 * 2012/10/16 modified so that variables are loaded from .ini file
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

//$withWav  = 0;
//$isTest   = 1;
$withWav 	= $_GET['withWav'];
$isTest  	= $_GET['isTest'];

if($withWav == 1 && $isTest == 1)
{
	$qSet = 'R'; // default
	$qNumMax = 42;
	$pageTitle = '聴いて答える問題（実力テスト）';
}
elseif($withWav == 0 && $isTest == 1)
{
	$qSet = 'R';
	$qNumMax = 42;
	$pageTitle = '見て答える問題（実力テスト）';
}
elseif($withWav == 1 && $isTest == 0)
{
	$qNumMax = 6;
	$pageTitle = '聴いて答える問題（練習）';
}
elseif($withWav == 0 && $isTest == 0)
{
	$qNumMax = 6;
	$pageTitle = '見て答える問題（練習）';
}
$srcDir  = $config["srcDir"];
$logoDir = $config["logoDir"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

// ====================


// ====================
// output page
// ====================

$i_pagestyle->print_header();
$i_pagestyle->print_body_begin();
$i_pagestyle->print_main_begin();
$i_pagestyle->print_home_button();



if($isDebug == true)
{
	echo "
	withWav: $withWav</br>
	isTest: $isTest</br>
	";
}

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="intro.php" method="POST">

	<h2>基本情報</h2>
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
	<h3>学校名または所属先を選択してください。</h3>
EOF;

$i_pagestyle->print_groupSelecter($logoDir);
}
else
{
echo <<<EOF
	<h3>どの問題セットに挑戦しますか？</h3>
	<select name="qSet"> 
		<option value="A">練習A</option> 
		<option value="B">練習B</option>
		<option value="1">レベル1</option>
		<option value="2">レベル2</option>
		<option value="3">レベル3</option>
		<option value="4">レベル4</option>
		<option value="5">レベル5</option>
		<option value="6">レベル6</option>
		<option value="7">レベル7</option>
	</select>
EOF;
}

echo <<<EOF
<br>
<!-- send variables to the next page as hidden -->
	<p><input type="hidden" value="$withWav" id="withWav" name="withWav" /></p>
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" value="$qNumMax" id="qNumMax" name="qNumMax" /></p>
	<input type="submit" value="OK">
</form>
EOF;

$i_pagestyle->print_footer();
?>