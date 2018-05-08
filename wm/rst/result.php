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

$isDebug = true;

$config   = parse_ini_file("../config.ini", false);
include("../../_class/c_pagestyle.php");
include("../../_class/c_mysql.php");

// ====================

$srcDir    = $config["srcDir"];
$pageTitle = $config["pageTitle"];
$qNumMax   = $config["qNumMax"];

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
$isTest     = $_POST['isTest'];
$trialNum   = $_POST['trialNum'];
$UserName   = $_POST['UserName'];
$GroupName  = $_POST['GroupName'];
$qSet       = $_POST['qSet'];
$QuizNumber = $_POST{'QuizNumber'};

for($i = 1; $i < $qNumMax+1; $i++)
{
	$qOrder[$i] = $_POST["q$i"];
}

$lastWord[0] = 0;
for($i = 1; $i < $qSet+1; $i++){
   if(isset($_POST["lastWord$i"]) && $_POST["lastWord$i"] != '')
   {
       $lastWord[$i] = $_POST["lastWord$i"];
   }
   else
   {
       $lastWord[$i] = '';
   }
}

$ElapsedTimeAll = $_POST{'ElapsedTimeAll'};
$TFall = $_POST{'TFall'};


//$aTF[0] = 0;
//   if(isset($_POST["aTrue$i"]) && $_POST["aTrue$i"] != '')
//   {
//       $aTrue[$i] = $_POST["aTrue$i"];
//   }
//   else
//   {
//       $aTrue[$i] = '';
//   }

$textNum = $qSet * ( $qOrder[$QuizNumber] - 1 ) + 1;


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
            
        aWord: $lastWord[1],$lastWord[2]</br>
        ElapsedTimeAll: $ElapsedTimeAll</br>
        TFall $TFall</br>
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
		WHERE is_listening = 0 AND is_test = $isTest AND quiz_set = $qSet AND quiz_num = $QuizNumber + $i - 1";        
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
	$aTF   = $row["answer"];
}


$i_mysql->close();
$i_pagestyle->print_footer();
?>