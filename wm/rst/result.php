<?php
/*
 * 2018/05/07
 * result/intermission page of the working memory test of yamauchi project
 *
 * RECEIVE
 * question.php
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// configuration
// ====================

$isDebug = false;

$config   = parse_ini_file("../config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];

$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();


// ====================
// get form data
// ====================
$isFirst    = $_POST['isFirst'];
$isListening = $_POST['isListening'];
$isTest     = $_POST['isTest'];
$trialNum   = $_POST['trialNum'];
$UserName   = $_POST['UserName'];
$GroupName  = $_POST['GroupName'];
$qSet       = $_POST['qSet'];
$QuizNumber = $_POST{'QuizNumber'};

if($isTest == 0){
    $qNumMax = 1;
}else{
    $qNumMax = $config["qNumMax_rst"];    
}

for($i = 1; $i < $qNumMax+1; $i++)
{
	$qOrder[$i] = $_POST["q$i"];
}

$ElapsedTimeAll = $_POST{'ElapsedTimeAll'};
$TFall = $_POST{'TFall'};

$elapsedTime_ = explode(",", $ElapsedTimeAll);
$aTrue_       = explode(',', $TFall);

$aWord[0]       = 0;
$elapsedTime[0] = 0;
$aTrue[0]       = 0;
for($i = 1; $i < $qSet+1; $i++){
    $elapsedTime[$i] = $elapsedTime_[$i-1];
    $aTrue[$i]       = $aTrue_[$i-1];
    
    if(isset($_POST["aWord$i"]) && $_POST["aWord$i"] != '')
    {
        $aWord[$i] = $_POST["aWord$i"];
    }
    else
    {
        $aWord[$i] = '';
    }
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
	isFirst: $isFirst</br>
        isListening: $isListening</br>
	isTest: $isTest</br>	
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qOrder: $qOrder[1],$qOrder[2],$qOrder[3]</br>
	QuizNumber: $QuizNumber</br>
	qNumMax: $qNumMax</br>
	sqlTableQuestion: $sqlTableQuestion</br>
	sqlTableResult: $sqlTableResult</br>
            
        aWord: $aWord[1],$aWord[2]</br>
        ElapsedTimeAll: $ElapsedTimeAll</br>
        elapsedTime: $elapsedTime[1],$elapsedTime[2]</br>
        TFall: $TFall</br>
        aTrue: $aTrue[1],$aTrue[2]</br>
	";
}


echo <<<EOF
	<h2>回答は正常に送信されました</h2>
	
EOF;


// ====================
// load the answer
// ====================

for ($i = 1; $i <= $qSet; $i++) {
	$sql_select = "SELECT quiz_num, question, last_word, answer
		FROM $sqlTableQuestion
		WHERE is_listening = 0 AND is_test = $isTest AND quiz_set = $qSet AND quiz_num = ($QuizNumber -1) * $qSet + $i";        
	$sql_result = mysql_query($sql_select);

	$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);

	$qNum = $row["quiz_num"];

	/*
	 * extract the last word from the question sentence
	 * a period should be removed
	 */
	$question_ = $row["question"];
	$question  = preg_split("/[\s]+/", $question_);
	$wNum      = count($question);
	//$qWord    = $question[$wNum-1];

	$qWord = $row["last_word"];
	$qTrue = $row["answer"];


	// ====================
	// is user answer correct
	// ====================
	$qWordOK = 0;
	$qTrueOK = 0;
	$UserAnswer = $qWord[0] . $aWord[$i];
	if($UserAnswer === $qWord)
	{
		$qWordOK = 1;
	}
	if($aTrue[$i] == $qTrue)
	{
		$qTrueOK = 1;
	}
        

        /*
	// check
	echo "
        i: $i</br>
	aWord: $aWord[$i]</br>
	qWord: $qWord</br>
	qWordOK: $qWordOK</br>
	aTrue: $aTrue[$i]</br>
	qTrue: $qTrue</br>
	qTrueOK: $qTrueOK</br>
	----------</br>
	";
         * 
         */
        

	// send results to mysql
	$sql_insert = "INSERT INTO $sqlTableResult(
	is_listening, is_test, trial_number, username, groupname, quiz_set, quiz_number, sec_number, a_word, q_word_ok, a_true, q_true_ok, elapsed_time
        ) VALUES (
        '$isListening', '$isTest', '$trialNum', '$UserName', '$GroupName', '$qSet', '$qOrder[$QuizNumber]', '$i', '$UserAnswer', '$qWordOK', '$aTrue[$i]', '$qTrueOK', '$elapsedTime[$i]')";

	mysql_query($sql_insert);

	if($isTest == 0)
	{
	
echo <<<EOF
		<h3>第 $i 文</h3>
		問題文： <b>$question_</b><br>

EOF;
		/*
		　* 文章の正誤 
		 */
		$qTrueWord  = array('False', 'True');
		$aTrue_ = $qTrueWord[$aTrue[$i]];
		$qTrue_ = $qTrueWord[$qTrue];
		if ($qTrueOK == 0)
		{
			if ($aTrue_ == '')
			{
echo <<<EOF
			(1)文章の内容： あなたは回答しませんでしたが、正解は <font color="red"><b>$qTrue_</b></font> でした。<br>
EOF;
			}
			else
			{
echo <<<EOF
			(1)文章の内容： あなたは　<b>$aTrue_</b> と回答しましたが、正解は <font color="red"><b>$qTrue_</b></font> でした。<br>
EOF;
			}
		}
		else
		{
echo <<<EOF
			(1)文章の内容： あなたの回答　<font color="blue"><b>$aTrue_</b></font> は正解です。<br>
EOF;
		}

		
		/*
		　* 最後の単語 
		 */
		if ($qWordOK == 0)
		{
echo <<<EOF
			(2)最後の単語： あなたは　<b>$UserAnswer</b> と回答しましたが、正解は <font color="red"><b>$qWord</b></font> でした。<br>
EOF;
		}
		else
		{
echo <<<EOF
			(2)最後の単語： あなたの回答 <font color="blue"><b>$UserAnswer</b></font> は正解です。<br>
EOF;
		}

		echo "<br>";
	} // isTest
        
}

echo <<<EOF
	<!-- Hidden Variables -->
EOF;

// question order
echo "<p>";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />";
}
echo "</p>";

// go to next question
if ($QuizNumber < $qNumMax)
{
	$QuizNumber += 1;
	$link = '<a href=question.php?isTest=' . $isTest . '&isListening=' . $isListening . '&isFirst=' . $isFirst . '&trialNum=' . $trialNum . '&UserName=' . $UserName . '&GroupName=' . $GroupName . '&qSet=' . $qSet . '&qNumMax=' . $qNumMax . '&QuizNumber=' . $QuizNumber;
	for($i = 1; $i < $qNumMax+1; $i++){
		$link = $link . '&q' . $i . '=' . $qOrder[$i];
	}
	$link = $link . '>次の問題</a>'; 
	
	echo $link;
}
else
{
	echo "<p>これで問題は終わりです。お疲れさまでした。</p>\n";
}        

        
$i_mysql->close();
$i_pagestyle->print_footer();
?>