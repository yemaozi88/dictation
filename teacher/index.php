<?php
/*
 * 2014/07/25
 * index page for the teacher page
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

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$logoDir   = $config["logoDir"];

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
	";
}

echo <<<EOF
	<h2>先生用ページ</h2>
	<img src="teacher_slim.jpg" alt="" class="head_photo" border="0" />
	
	<h3>英単語クイズ</h3>
	<ul>
		<li>
<a href="../uploader/upload.cgi" target="_blank">問題のアップロード</a>
		</li>

		<li>
<a href="./statistics/select_group.php">成績の閲覧</a>
		</li>
	</ul>
	
	技術的な問題のお問い合わせは<a href="mailto:428968@gmail.com?subject=question">こちら</a>へどうぞ。</br>
EOF;

$i_pagestyle->print_footer();
?>