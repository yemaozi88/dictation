<?php
/*
 * 2013/07/03
 * statistics
 *
 * SEND
 *
 * NOTE
 * student.php is based on quiz/result.php
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

$isDebug = true;

$pageTitle  = '実力テストの結果';

$srcDir		= '../../../_src';
$logoDir	= '../../_image/logo';

include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");


// ====================

$qSet 		= 'R'; // default

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();

$GroupName = 'kansai-u';
$sqlTable = 'eword_quiz';

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
	GroupName: $GroupName</br>
	";
}

// get results from mysql
$i_mysql->connect();
$sql_select = "SELECT username, with_wav
	FROM $sqlTable 
	WHERE is_test = 1
	GROUP BY username
	ORDER BY username ASC";
$sql_result = mysql_query($sql_select);
$i_mysql->close();
//AND groupname = '$GroupName'


echo <<<EOF
<h3> 関西大学 </h3>
<p align="center"><table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
	<tr>
		<th align="center">お名前</th>
		<th align="center"></th>	
		<th align="center">prev</th>		
		<th align="center">prev</th>		
</tr>
EOF;

$i = 1;
while ($row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) 
{
	if($i > 1)
	{
		printf("<tr>
		<td>%s</td>
		<td>%d</td>
		<td>%s</td>
		<td>%d</td>
		</tr>", $row["username"], $row["with_wav"], $username_, $with_wav_);

	}

	$username_ = $row["username"];
	$with_wav_ = $row["with_wav"];
	$i += 1;
}
echo("</table></p>\n");
EOF;

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="intro.php" method="POST">

	<h2>回答者の情報</h2>
EOF;

if($isTest == 1)
{
echo <<<EOF
	お名前（半角英数字のみ、スペースは含めない）とご所属を入力してください。<br>
	<table>
		<tr>
			<td>お名前</td>
			<td><input type="text" name="UserName" size=25></td>
		</tr>
	</table>
	<br>
	学校名または所属先を選択してください。<br>
EOF;

$i_pagestyle->print_groupSelecter($logoDir);
}
else
{
echo <<<EOF
	お名前（半角英数字のみ、スペースは含めない）を入力してください。<br>
	<table>
		<tr>
			<td>お名前</td>
			<td><input type="text" name="UserName" size=25></td>
		</tr>
	</table>
	
	<br>
	どの問題セットに挑戦しますか？
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