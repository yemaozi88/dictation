<?php
/*
 * 2018/05/04
 * introduction page for working memory test of yamauchi project
 *
 * RECEIVE
 * index.php
 * SEND
 * question.php
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration & get form data
// ====================

$isDebug = false;

$config    = parse_ini_file("../config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

//$isListening = $_POST['isListening'];
$isTest    = $_POST['isTest'];
if($isTest == 0)
{
    $GroupName = '';
}
else
{
    $GroupName = $_POST['GroupName'];
}
$UserName  = $_POST['UserName'];
$qSet	   = $_POST['qSet'];

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
if($isTest == 0){
    $qNumMax = 1;
}else{
    $qNumMax = $config["qNumMax_rst"];    
}


$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();


// ====================
// question order
// ====================

// shuffle the question order
$qOrder = range(1, $qNumMax);
//shuffle($qOrder);

// shift $qOrder so that Qi corresponds to $qOrder[i]
for($i = $qNumMax-1; $i > -1; $i--)
{
	$qOrder[$i+1] = $qOrder[$i];
}
$qOrder[0] = 0;


// ====================
// trial number
// ====================

	$i_mysql->connect();
	
	$sql_select = "SELECT username, MAX( trial_number ) AS trial_num_max FROM $sqlTableResult  "
                . "WHERE is_listening = '$isListening' AND is_test = '$isTest' AND username = '$UserName' AND groupname = '$GroupName'";
	$sql_result = mysql_query($sql_select);
	$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
	if($row["trial_num_max"] == NULL)
	{
		$trialNum = 1;
	}
	else
	{
		$trialNum = $row["trial_num_max"] + 1;
	}

	$i_mysql->close();


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
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qNumMax: $qNumMax</br>
	qOrder: $qOrder[0],$qOrder[1],$qOrder[2],$qOrder[3]</br>
        SQLselect: $sql_select</br>
	";
}

echo <<<EOF
<form action="question.php" method="post">
	<h2>回答方法の説明</h2>
	こんにちは $UserName さん。<br>
	<br>
        これはあなたが一度にどれだけの英文を処理できるかを測定するテストです。<br>
        テストが始まると、数文の英文が一定の時間に表示されます。<br>
        時間が切れると、解答欄が表示されます。<br>
        それぞれの文章の最後の単語、および内容の正誤を答えてください。<br>
	<br>
	問題は全部で $qNumMax 問です。<br>

<!-- send variables to the next page as hidden -->
	<p><input type="hidden" value="0" id="isListening" name="isListening" /></p>
        <p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" value="1" id="isFirst" name="isFirst" /></p>
	<p><input type="hidden" value="$trialNum" id="trialNum" name="trialNum" /></p>
	<p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
	<p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>
	<p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
EOF;

// question order
echo "<p>";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />";
}
echo "</p>";

echo <<<EOF
	<p><input type="submit" value="始める" /></p>
</form>
EOF;

$i_pagestyle->print_footer();
?>