<?php
/*
 * 2013/10/09
 * play a sound file for the working memory test of yamauchi project
 *
 * RECEIVE
 * SEND
 *
 * HISTORY
 * 2018/07/08 question info is loaded from manifest.json, instead of from database.
 * 2014/01/25 display the first letter of the last word
 * 2014/01/24 change the order of the answer forms
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
require("../../_class/c_mysql.php");
require("../../_class/read_manifest_json.php");

$json_dir = '../../uploader/upload/lst';

// ====================

$srcDir    = $config["srcDir"];
$jsDir     = $config["jsDir"];
$pageTitle = $config["pageTitle"];
$wavDir_   = $config["wavDir"];
        
//$qNumMax   = $config["qNumMax"];

$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_pagestyle = new c_pagestyle();
$i_pagestyle->set_variables($pageTitle, $srcDir);

$i_mysql = new c_mysql();
$i_mysql->connect();


// ====================
//  get form data
// ====================
$isFirst = 0;
if(isset($_POST['isFirst']))
{
    $isFirst = $_POST['isFirst'];
}

if($isFirst == 1) // if it is the first time
{
	$isTest    = $_POST['isTest'];
        if($isTest == 0){
            //$qNumMax = 1;
            $wavDir  = $wavDir_ . '/practice';
            $manifest_json = $json_dir . '/' . 'practice' . '/manifest.json';
        }else{
            //$qNumMax = $config["qNumMax_lst"];
            $wavDir  = $wavDir_ . '/test';
            $manifest_json = $json_dir . '/' . 'test' . '/manifest.json';
        }        
	$trialNum  = $_POST['trialNum'];
	$UserName  = $_POST['UserName'];
	$GroupName = $_POST['GroupName'];
	$qSet	   = $_POST['qSet'];
	$QuizNumber = 1;
        
        // ====================
        // get qNumMax.
        // ====================
        $data = loadManifestJson($manifest_json);
        $iQuestionList = get_iQuestionList($data, $qSet);
        $qNumMax = count($iQuestionList);		

	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_POST["q$i"];
	}	    
}
else // if it is NOT the first time
{
    	$isTest     = $_GET['isTest'];
        if($isTest == 0){
            //$qNumMax = 1;
            $wavDir  = $wavDir_ . '/practice';
            $manifest_json = $json_dir . '/' . 'practice' . '/manifest.json';
        }else{
            //$qNumMax = $config["qNumMax_lst"];
            $wavDir  = $wavDir_ . '/test';
            $manifest_json = $json_dir . '/' . 'test' . '/manifest.json';
        }
        
	$trialNum   = $_GET['trialNum'];
	$UserName   = $_GET['UserName'];
	$GroupName  = $_GET['GroupName'];
        
        // number of sentences which the user listens at once.
	$qSet	    = $_GET['qSet'];
	$QuizNumber = $_GET['QuizNumber'];
        
        // ====================
        // get qNumMax.
        // ====================
        $data = loadManifestJson($manifest_json);
        $iQuestionList = get_iQuestionList($data, $qSet);
        $qNumMax = count($iQuestionList);
        
	for($i = 1; $i < $qNumMax+1; $i++)
	{
		$qOrder[$i] = $_GET["q$i"];
	}
}

//$wavNum = $qSet * ( $qOrder[$QuizNumber] - 1 ) + 1;


// ====================
// get question information from the json file.
// ====================
//$questionInfo = getQuestionInfo($data, $qSet, $QuizNumber);
$questionInfo = getQuestionInfo($data, $qSet, $qOrder[$QuizNumber]);
$wavs = getWavNames($questionInfo);
// $answers has fields of "correctness", "lastWord", text"
$answers = getAnswers($questionInfo);


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
	wavDir: $wavDir</br>
	wavOut: $wavOut</br>
	sqlTableQuestion: $sqlTableQuestion</br>
        manifest_json: $manifest_json</br>
        wavs: $wavs[0]</br>
        answers: $answers[0]</br>
	";
        
        //print_r($data);
        //print_r($questionInfo);
        //print_r($wavs);
}

echo <<<EOF
<form action="result.php" method="post" >
	<h2>第 $QuizNumber 問</h2>
	<script type="text/javascript"> var qWord1 = []; </script>
	<script type="text/javascript" src="../../_js/play.js"></script>
	<button type="button" id="playbutton" onclick="clickButton()" disabled="true">問題の再生</button>
EOF;


for ($i = 1; $i <= $qSet; $i++)
{
    $index = $i-1;
    /*
	$sql_select = "SELECT quiz_num, question, last_word, answer
		FROM $sqlTableQuestion
		WHERE quiz_set = $qSet AND quiz_num = $wavNum";
	$sql_result = mysql_query($sql_select);

	$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);

	$qNum = $row["quiz_num"];
    */
    
	/*
	 * extract the last word from the question sentence
	 * a period should be removed
	 */
	//$question_ = $row["question"];
	//$question  = preg_split("/[\s]+/", $question_);
        //$wNum      = count($question);
        $question = $answers[$index][text];

	//$qWord     = $row["last_word"];
        $qWord     = $answers[$index][lastWord];
	$qWord1    = $qWord[0];
	
        /*
        echo "</br>-----</br>
	question: $question</br>
	qWord: $qWord</br>	
	qWord1: $qWord1</br>
  	";
         */
            
	/*
	 * send variable 'qWord' to Javascript
	 */
echo <<<EOF

	<script type="text/javascript"> 
EOF;

echo "qWord1[$i] = \"$qWord1\"";
	
echo <<<EOF
	</script>
EOF;

	if ($i == 1)
	{
echo <<<EOF
	
	<audio id="sentence" preload="auto" oncanplay="wavCanPlay();" onended="setTimeout('wavPlayNext(2)',1500);">

EOF;
	}
	elseif ($i < $qSet)
	{
		$iNext = $i + 1;
echo <<<EOF
	
	<audio id="sentence$i" preload="auto" onended="setTimeout('wavPlayNext($iNext)',1500);">

EOF;
	}
	else
	{
echo <<<EOF
	
	<audio id="sentence$i" preload="auto" onended="setTimeout('dispQuestion($qSet)',1500);">

EOF;
	}
	
echo <<<EOF
            <!--
		<source src="$wavDir/set$qSet/$wavNum.wav" type="audio/wav" />
		<source src="$wavDir/set$qSet/$wavNum.mp3" type="audio/mp3" />
            -->
		<source src="$wavDir/$wavs[$index]" type="audio/wav" />
        
		<p>Your browser can not open wav file. Please install appropriate plugin.</p>
	</audio>
	
EOF;
	
	//$wavNum = $wavNum + 1;	
}

for ($i = 1; $i <= $qSet; $i++)
{
echo <<<EOF

	<div id="aTitle$i"></div>
	<div id="aTrue$i"></div>
	<div id="aWord$i"></div>

EOF;
}

echo <<<EOF
<div id="goNext"></div>

	<!-- Hidden Variables -->
	<p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
	<p><input type="hidden" value="$isFirst" id="isFirst" name="isFirst" /></p>
	<p><input type="hidden" value="1" id="isFirst" name="isFirst" /></p>
	<p><input type="hidden" value="$trialNum" id="trialNum" name="trialNum" /></p>
	<p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
	<p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>
	<p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
	<p><input type="hidden" id="QuizNumber" name="QuizNumber" value="$QuizNumber" /></p>
EOF;


// question order
echo "\n<p>\n";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />\n";
}
echo "</p>\n";

echo <<<EOF
</form>
EOF;

$i_mysql->close();
$i_pagestyle->print_footer();
?>