<?php
/*
 * 2012/10/13
 * introduction page for english word quiz of yamauchi project
 * intro.php loads the question list and sends a question order to quiz.cgi
 *
 * RECEIVE
 * index.php
 * SEND
 * quiz.php
 *
 * NOTE
 * intro.php is based on test/intro.cgi
 *
 * HISTORY
 * 2013/06/27 combine practice and test modes.
 * 2013/06/02 changed word_practice into test mode (no result, SQL settings etc.)
 * 2013/05/19 [bug fix] modified so that $qOrder is shifted 
 * 2012/10/23 added a question set selector
 * 2012/10/16 modified so that variables are loaded from .ini file
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration & get form data
// ====================

$isDebug = false;

$config    = parse_ini_file("config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$withWav   = $_POST['withWav'];
$isTest	   = $_POST['isTest'];
$UserName  = $_POST['UserName'];
$GroupName = $_POST['GroupName'];
$qSet	   = $_POST['qSet'];
$qNumMax   = $_POST['qNumMax'];

if($withWav == 1 && $isTest == 1)
{
	$qSet = 'R';
	$pageTitle = '聴いて答える問題（実力テスト）';
}
elseif($withWav == 0 && $isTest == 1)
{
	$qSet = 'R';
	$pageTitle = '見て答える問題（実力テスト）';
}
elseif($withWav == 1 && $isTest == 0)
{
	$pageTitle = '聴いて答える問題（練習）';
}
elseif($withWav == 0 && $isTest == 0)
{
	$pageTitle = '見て答える問題（練習）';
}
$srcDir   = $config["srcDir"];
$qDir 	  = $config["qDir"];
$qList	  = $qDir . "/set" . $qSet . "/filelist.csv";
$sqlTable = $config["sqlTable"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();


// ====================
// question order
// ====================

// count the number of questions in the list
$fp = fopen($qList, "r");
$qListNumMax = 0;
while($data = fgetcsv($fp))
{
	$qListNumMax += 1;
}

// shuffle the question order
$qOrder = range(1, $qListNumMax);
shuffle($qOrder);
//foreach ($qOrder as $qNum){
//	echo "$qNum ";
//}

// shift $qOrder so that Qi corresponds to $qOrder[i]
for($i = $qNumMax; $i > -1; $i--)
{
	$qOrder[$i+1] = $qOrder[$i];
}
$qOrder[0] = 0;


// ====================
// trial number
// ====================
if($isTest == 1)
{
	$i_mysql->connect();

	$sql_select = "SELECT username, MAX( trial_number ) AS trial_num_max FROM $sqlTable WHERE 'with_wav' = $withWav AND 'is_test' = $isTest AND username = '$UserName' AND groupname = '$GroupName'";
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
}


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
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qNumMax: $qNumMax</br>
	qList: $qList</br>
	qListNumMax: $qListNumMax</br>
	qOrder: $qOrder[0],$qOrder[1],$qOrder[2],$qOrder[3],$qOrder[4],$qOrder[5],$qOrder[6]</br>
	sqlTable: $sqlTable</br>
	sqlSelect: $sql_select</br>
	";
}

echo <<<EOF
<form action="quiz.php" method="post">
	<h2>回答方法の説明</h2>
	こんにちは $UserName さん。<br>
	<br>
EOF;

if( $withWav == 0 )
{
echo <<<EOF
	これは英単語を見て、その意味を答えるクイズです。<br>
	<br>
	問題画面に表示されている単語の和訳として、もっとも適当なものを選択肢の中から選んでください。<br>
	回答は一度選択されるとすぐに送信され、後から変更することはできません。<br>
	またタイマーは問題が表示されると同時に動き出し、回答が送信されるまで止まりません。<br>
EOF;
}
else
{
echo <<<EOF
	これは英単語を聴いて、その意味を答えるクイズです。<br>
	<br>
	Playボタンをクリックすると、タイマーが動き出し、問題の音声が流れます。<br>
	（ブラウザが音声を読み込むまでに数秒かかる場合もあります）<br>
	その音声の和訳として、もっとも適当なものを選択肢の中から選んでください。<br>
	<br>
	音声は何度聞きなおしてもかまいません。<br>
	音声を聞いている途中で回答してもかまいません。<br>
	ただし回答は一度選択されるとすぐに送信され、後から変更することはできません。<br>
	また一度動き出したタイマーは回答が送信されるまで止まりません。<br>
EOF;
}

echo <<<EOF
	<br>
	問題は全部で $qNumMax 問です。<br>

<!-- send variables to the next page as hidden -->
	<p><input type="hidden" value="$withWav" id="withWav" name="withWav" /></p>
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" value="1" id="isFirst" name="isFirst" /></p>
	<p><input type="hidden" value="$trialNum" id="trialNum" name="trialNum" /></p>
	<p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
	<p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>	
	<p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
	<p><input type="hidden" value="$qNumMax" id="qNumMax" name="qNumMax" /></p>	
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