<?php
/*
 * 2015/09/13
 * index page for reading quiz of yamauchi project
 *
 * RECEIVE
 * index.php
 * SEND
 * question.php
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
// configuration & get form data
// ====================

$isDebug = false;

$config  = parse_ini_file("config.ini", false);
include("../_class/c_pagestyle.php");
include("../_class/c_mysql.php");

// ====================

$isTest    = $_POST['isTest'];
if($isTest == 0)
{
    $GroupName = '';
}
else
{
    $GroupName = $_POST['GroupName'];
}
$UserName = $_POST['UserName'];

$isFirst = 1;
$sentenceNumber = 1;
$practiceNumber = 1;

$srcDir         = $config["srcDir"];
$pageTitle      = $config["pageTitle"];
$sentenceNumberMax = $config["sentenceNumberMax"];

$aspxURL = $config["aspxURL"];

//$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();


// ====================
// question order
// ====================
/*
// shuffle the question order
$qOrder = range(1, $sentenceNumberMax);
//shuffle($qOrder);

// shift $qOrder so that Qi corresponds to $qOrder[i]
for($i = $sentenceNumberMax-1; $i > -1; $i--)
{
	$qOrder[$i+1] = $qOrder[$i];
}
$qOrder[0] = 0;
*/


// ====================
// trial number
// ====================

	$i_mysql->connect();
	
	$sql_select = "SELECT username, MAX( trial_number ) AS trial_num_max FROM $sqlTableResult  WHERE 'is_test' = $isTest AND username = '$UserName' AND groupname = '$GroupName'";
	$sql_result = mysql_query($sql_select);
	$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
	if($row["trial_num_max"] == NULL)
	{
		$trialNumber = 1;
	}
	else
	{
		$trialNumber = $row["trial_num_max"] + 1;
	}

	$i_mysql->close();


// ====================
// output page
// ====================

$i_pagestyle->print_header();

if($isDebug == true)
{
	echo "
	isTest: $isTest</br>
        isFirst: $isFirst</br>
	trialNumber: $trialNumber</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
        aspxURL: $aspxURL</br>
	sentenceNumberMax: $sentenceNumberMax</br>
        sentenceNumber: $sentenceNumber</br>
        practiceNumber: $practiceNumber</br>
        SQLselect: $sql_select</br>
	";
}

echo <<<EOF
<!-- <form action="question.php" method="post"> -->
	<h2>回答方法の説明</h2>
	こんにちは $UserName さん。<br>
	<br>
	ここでは、音読の練習と、音読しながら、どれだけ内容を理解しているか測定します。<br>
	<br>
	文章は全部で $sentenceNumberMax 文あります。<br>
        <br>
        
<!-- send variables to the next page as hidden -->
<!--        
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" value="1" id="isFirst" name="isFirst" /></p>
	<p><input type="hidden" value="$trialNumber" id="trialNumber" name="trialNumber" /></p>
	<p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
	<p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>
-->
EOF;

// question order
/*
echo "<p>";
for($i = 1; $i < $sentenceNumberMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />";
}
echo "</p>";
*/

echo <<<EOF
<!--
	<p><input type="submit" value="始める" /></p>
</form>
-->        
EOF;

$link = '<a href=' . $aspxURL . '?isTest=' . $isTest . '&isFirst=' . $isFirst . '&trialNumber=' . $trialNumber . '&UserName=' . $UserName . '&GroupName=' . $GroupName . '&sentenceNumber=' . $sentenceNumber . '&practiceNumber=' . $practiceNumber;
//for($i = 1; $i < $qNumMax+1; $i++){
//	$link = $link . '&q' . $i . '=' . $qOrder[$i];
//}
$link = $link . '>問題を始める</a>';
echo $link;


$i_pagestyle->print_footer();
?>