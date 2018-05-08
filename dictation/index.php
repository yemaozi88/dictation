<?php
/*
 * 2015/06/02
 * index page for dictation test of yamauchi project
 *
 * SEND
 * intro.php
 *
 * NOTE
 * index.php is based on eword/quiz/index.php
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

$config  = parse_ini_file("config.ini", false);
include("../_class/c_pagestyle.php");


// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$logoDir   = $config["logoDir"];
$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

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
	srcDir: $srcDir</br>
	pageTitle: $pageTitle</br>
        logoDir: $logoDir</br>
        sqlTableQuestion: $sqlTableQuestion</br>
        sqlTableResult: $sqlTableResult</br>
	";
}

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="intro.php" method="POST">

	<h2>test</h2>
EOF;

$i_pagestyle->print_footer();
?>