<?php
/*
 * 2013/10/16
 * result/intermission page of the working memory test of yamauchi project
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
require("../../_class/c_pagestyle.php");
require("../../_class/c_mysqli.php");
require("../../_class/read_manifest_json.php");
require("../../_class/variable_selection.php");


// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$uploadDir = $config["uploadDir"];
$wavDir_   = $config["wavDir"];

$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();

$i_mysqli = new c_mysqli();
$i_mysqli->connect();


// ====================
// get form data
// ====================
$isFirst = $_POST['isFirst'];
$isTest  = $_POST['isTest'];
$withWav = $_POST['withWav'];

$manifest_json = set_manifest_json($uploadDir, $withWav, $isTest);

$trialNum   = $_POST['trialNum'];
$UserName   = $_POST['UserName'];
$GroupName  = $_POST['GroupName'];
$qSet       = $_POST['qSet'];
$QuizNumber = $_POST{'QuizNumber'};

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
// get question information
// ====================
// qNumMax
$manifest_json = set_manifest_json($uploadDir, $withWav, $isTest);
$data = loadManifestJson($manifest_json);
$iQuestionList = get_iQuestionList($data, $qSet);
$qNumMax = count($iQuestionList);

// qOrder
for($i = 1; $i < $qNumMax+1; $i++)
{
	$qOrder[$i] = $_POST["q$i"];
}

// question
$questionInfo = getQuestionInfo($data, $qSet, $qOrder[$QuizNumber]);
// $answers has fields of "correctness", "lastWord", text"
$answers = getAnswers($questionInfo);
if($withWav==0)
{
    $qSentences = $questionInfo['texts'];
}

	
// ====================
// output page
// ====================

$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_pagestyle->print_header();
$i_pagestyle->print_body_begin();
$i_pagestyle->print_main_begin();
$i_pagestyle->print_home_button();


if($isDebug == true)
{
	echo "
	isFirst: $isFirst</br>
	isTest: $isTest</br>
        withWav: $withWav</br>
	trialNum: $trialNum</br>
	UserName: $UserName</br>
	GroupName: $GroupName</br>
	qSet: $qSet</br>
	qOrder: $qOrder[1],$qOrder[2],$qOrder[3]</br>
	QuizNumber: $QuizNumber</br>
	qNumMax: $qNumMax</br>
	sqlTableQuestion: $sqlTableQuestion</br>
	sqlTableResult: $sqlTableResult</br>
        manifest_json: $manifest_json</br>
        answers: $answers[0]</br>            
	";
}


echo <<<EOF
<form action="result.php" method="post" >

	<h2>回答は正常に送信されました</h2>
	
EOF;


// ====================
// load the answer
// ====================

for ($i = 1; $i <= $qSet; $i++) {
    $index = $i-1;

    // question text
    if($withWav==1){
        $question = $answers[$index]["text"];
    }else{
        $question = $qSentences[$index];
    }

    // last word
    $qWord  = $answers[$index]["lastWord"];
    // correct or not
    $qTrue_ = $answers[$index]["correctness"];
    if($qTrue_ == 1)
    {
        $qTrue = 1;
    }
    else 
    {
        $qTrue = 0;
    }

    
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


    // check
    if($isDebug == true)
    {
        echo "
            aWord: $aWord[$i]</br>
            qWord: $qWord</br>
            qWordOK: $qWordOK</br>
            aTrue: $aTrue[$i]</br>
            qTrue_: $qTrue_</br>
            qTrue: $qTrue</br>
            qTrueOK: $qTrueOK</br>
            ----------</br>
            ";
            print_r($answers[$index]);
    }

    // send results to mysql
    $sql_insert = "INSERT INTO $sqlTableResult(
    is_listening, is_test, trial_number, username, groupname, quiz_set, quiz_number, sec_number, 
    a_word, q_word_ok, a_true, q_true_ok, elapsed_time) VALUES (
    '$withWav', '$isTest', '$trialNum', '$UserName', '$GroupName', '$qSet', '$qOrder[$QuizNumber]', '$i', '$UserAnswer', '$qWordOK', '$aTrue[$i]', '$qTrueOK' , '$elapsedTime[$i]')";

    //mysql_query($sql_insert);
    $i_mysqli->mysqli->query($sql_insert);
    #echo $sql_insert;
    if($isTest == 0)
    {
	
echo <<<EOF
		<h3>第 $i 文</h3>
		問題文： <b>$question</b><br>

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
	$link = '<a href=question.php?isTest=' . $isTest . '&isFirst=' . $isFirst . '&withWav=' . $withWav . '&trialNum=' . $trialNum . '&UserName=' . $UserName . '&GroupName=' . $GroupName . '&qSet=' . $qSet . '&qNumMax=' . $qNumMax . '&QuizNumber=' . $QuizNumber;
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


echo <<<EOF
</form>
EOF;

$i_mysqli->close();
$i_pagestyle->print_footer();
?>