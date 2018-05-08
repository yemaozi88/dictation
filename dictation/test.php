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
include("../_class/c_mysql.php");
include("../_class/c_dtw.php");


// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$logoDir   = $config["logoDir"];
$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();

$i_dtw = new c_dtw();


$qSet = 0;
$qNum = 1;
$aSentence = "I met hr at the party.";


// ====================
// load the answer
// ====================

$sql_select = "SELECT quiz_num, question
    FROM $sqlTableQuestion
    WHERE quiz_set = $qSet AND quiz_num = $qNum";
$sql_result = mysql_query($sql_select);

$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
$qSentence = $row["question"];

$i_dtw->set_variables($aSentence, $qSentence);
$i_dtw->dtw();


// ====================
// output page
// ====================

$i_pagestyle->print_header();
$i_pagestyle->print_body_begin();
$i_pagestyle->print_main_begin();
$i_pagestyle->print_home_button();

$i_dtw->dispDmat();

if($isDebug == true)
{
    echo "
        </br>
        srcDir: $srcDir</br>
        pageTitle: $pageTitle</br>
        logoDir: $logoDir</br>
        sqlTableQuestion: $sqlTableQuestion</br>
        sqlTableResult: $sqlTableResult</br>
        qNum = $qNum</br>
        qSentence = $qSentence</br>
        aSentence = $aSentence</br>
        qWordNum  = $i_dtw->wordNumA</br>
        aWordNum  = $i_dtw->wordNumB</br>
    ";
}

echo <<<EOF
<!--<img src="florence_slim.JPG" alt="" class="head_photo" border="0" />-->
<form action="intro.php" method="POST">

   <h2>test</h2>

EOF;

$i_pagestyle->print_footer();
?>