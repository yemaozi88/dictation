<?php
/*
 * 2018/05/04
 * index page for working memory test of yamauchi project
 * 
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = false;

$config  = parse_ini_file("../config.ini", false);
require("../../_class/c_pagestyle.php");
require("../../_class/variable_selection.php");


// ====================

$srcDir    = $config["srcDir"];
$logoDir   = $config["logoDir"];

$isTest    = $_GET['isTest'];
$withWav   = $_GET['withWav'];

$pageTitle = set_page_title($withWav, $isTest);

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);
         

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
    isTest: $isTest</br>
    withWav: $withWav</br>
    pageTitle: $pageTitle</br>
    ";
}

echo <<<EOF

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
    
	<h3>学校名または所属先を選択してください</h3>
EOF;

$i_pagestyle->print_groupSelecter($logoDir);
}

echo <<<EOF


	<h3>何文連続再生しますか？</h3>
	<select name="qSet"> 
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
	</select>
	文連続再生</br>

	<br>
<!-- send variables to the next page as hidden -->
	<p><input type="hidden" value="$withWav" id="withWav" name="withWav" /></p>
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>        
	<input type="submit" value="OK">
</form>
EOF;

$i_pagestyle->print_footer();
?>