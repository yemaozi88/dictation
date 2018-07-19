<?php
/*
 * 2018/05/05
 * show the question for the working memory test of yamauchi project
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
require("../../_class/read_manifest_json.php");
require("../../_class/variable_selection.php");


// ====================
// load query string / config.ini
// ====================

$srcDir    = $config["srcDir"];
$jsDir     = $config["jsDir"];
$uploadDir = $config["uploadDir"];;
$wavDir_   = $config["wavDir"];

$i_pagestyle = new c_pagestyle();


// ====================
// wav files to be loaded
// ====================

$isFirst = 0;
if(isset($_POST['isFirst']))
{
    $isFirst = $_POST['isFirst'];
}

if($isFirst == 1) // if it is the first time
{
    $withWav    = $_POST['withWav']; 
    $isTest     = $_POST['isTest'];
    $trialNum   = $_POST['trialNum'];
    $UserName   = $_POST['UserName'];
    $GroupName  = $_POST['GroupName'];
    $qSet       = $_POST['qSet'];
    $QuizNumber = 1;
}
else
{
    $withWav    = $_GET['withWav'];
    $isTest     = $_GET['isTest'];
    $trialNum   = $_GET['trialNum'];
    $UserName   = $_GET['UserName'];
    $GroupName  = $_GET['GroupName'];
    $qSet	= $_GET['qSet'];
    $QuizNumber = $_GET['QuizNumber'];
}
$pageTitle = set_page_title($withWav, $isTest);


// ====================
// get question information from the json file.
// ====================
$manifest_json = set_manifest_json($uploadDir, $withWav, $isTest);
if($withWav == 1)
{
    if($isTest == 0)
    {
        $wavDir  = $wavDir_ . '/practice';
    }
    else 
    {
        $wavDir  = $wavDir_ . '/test';        
    }
}
$data          = loadManifestJson($manifest_json);
$iQuestionList = get_iQuestionList($data, $qSet);
$qNumMax       = count($iQuestionList);

// load question order.
for($i = 1; $i < $qNumMax+1; $i++)
{
    if($isFirst == 1) // if it is the first time
    {
        $qOrder[$i] = $_POST["q$i"];
    }
    else
    {
        $qOrder[$i] = $_GET["q$i"];
    }	    
}

$questionInfo  = getQuestionInfo($data, $qSet, $qOrder[$QuizNumber]);
if($withWav==0)
{
    $qSentences = $questionInfo['texts'];
    $timeLimit  = $questionInfo['timeLimit'];
}
else
{
    $wavs = getWavNames($questionInfo);
    $timeLimit  = 300;
}
$answers = getAnswers($questionInfo);


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
    withWav: $withWav</br>
    isTest: $isTest</br>	
    trialNum: $trialNum</br>
    UserName: $UserName</br>
    GroupName: $GroupName</br>
    qSet: $qSet</br>
    qOrder: $qOrder[1],$qOrder[2]</br>
    QuizNumber: $QuizNumber</br>
    qNumMax: $qNumMax</br>
    timeLimit: $timeLimit</br>
    ";
    
    if($withWav == 1){
        echo "wavDir: $wavDir</br>";
    }
}


echo <<<EOF

	<h2>第 $QuizNumber 問</h2>
	<script type="text/javascript"> var qWord1 = []; </script>
	<script type="text/javascript"> var qSentence = []; </script>
	<script type="text/javascript"> var qWav = []; </script>    
	<script type="text/javascript"> var wavPlayCounter = 0; </script>    
            
        <script type="text/javascript"> timeMax = $timeLimit</script>
        <script type="text/javascript" src="$jsDir/timer3.js"></script>
	<!--<script type="text/javascript" src="$jsDir/play.js"></script>-->        
EOF;

for ($i = 1; $i <= $qSet; $i++)
{
    $index = $i-1;

    if($withWav == 0)
    {
        $qSentence = $qSentences[$index];
    }
    else
    {
        $qSentence = $answers[$index]["text"]; 
    }

    $qWord  = $answers[$index]["lastWord"];
    $qWord1 = $qWord[0]; // the first letter of the last word.

    if($isDebug == true)
    {
        echo "</br>-----</br>
        qSentence: $qSentence</br>
        qWord: $qWord</br>	
        qWord1: $qWord1</br>";
        if($withWav==1){
            echo "wav: $wavDir/$wavs[$index]</br>";
        }
    }
    
    /*
     * send variable 'qWord' to Javascript
     */
    echo <<<EOF

    <script type="text/javascript"> 
        qWord1[$i]    = "$qWord1";
        qSentence[$i] = "$qSentence";
EOF;
    
    if($withWav==1){
        echo "qWav[$i] = \"$wavDir/$wavs[$index]\"";
    }
    
    echo <<<EOF
    
    </script>
EOF;
}

$i = 1;
echo <<<EOF

<h3>文の正誤</h3>
<center>
<table border="10" cellpadding="2" cellspacing="0" style="border-collapse: collapse; border-style: solid; border-width: 2px;">
    <tr>
        <td width="300" align="center">
            <div id="qTitle">$qSet 文中 第 0 文目</div>
        </td>
        <td width="150" align="center" bgcolor="#FFFFFF">
            <span id="RemainingTime" style="font-size:24px;">$timeLimit:00</span> 秒
        </td>
    </tr>
    <tr>
        <td colspan="2" height="100">
            <div id="qText"><center>ここに問題が表示されます。</center></div>            
        </td>
    </tr>

    <tr >
        <td align="center" valign="middle">
            <div id="aTF"></div>
        </td>
        
        <td><center>
EOF;

if($withWav == 0)
{
echo <<<EOF
    
    <button type="button" id="showbutton" style="color:white;background-color: #4d1a00;border-color:white" onclick="clickButton_show($i, $qSet, $withWav)">
        問題を見る
    </button>
EOF;
}
else
{
echo <<<EOF
    
    <button type="button" id="playbutton" onclick="clickButton_show($i, $qSet, $withWav)" disabled="true">問題の再生</button>
    <audio id="question" preload="auto" oncanplay="wavCanPlay();" onended="show_question_tf($i, $qSet, $withWav);">
    <source id="src_wav" src="$wavDir/$wavs[0]" type="audio/wav" />
    </audio>\n
EOF;
}

echo <<<EOF

        </center></td>
    </tr>
</table>
</center>

<br>
<h3>最後の単語</h3>
<form action="result.php" method="post">
EOF;

for ($i = 1; $i <= $qSet; $i++){
echo <<<EOF
    
    <div id="aWord$i"></div>
EOF;
}

echo <<<EOF

<div id="sendResult"></div>
    <!-- Hidden Variables -->
    <p><input type="hidden" value="$isTest" id="isTest" name="isTest" /></p>
    <p><input type="hidden" value="$withWav" id="isTest" name="withWav" /></p>
    <p><input type="hidden" value="$isFirst" id="isFirst" name="isFirst" /></p>
    <p><input type="hidden" value="$trialNum" id="trialNum" name="trialNum" /></p>
    <p><input type="hidden" value="$UserName" id="UserName" name="UserName" /></p>
    <p><input type="hidden" value="$GroupName" id="GroupName" name="GroupName" /></p>
    <p><input type="hidden" value="$qSet" id="qSet" name="qSet" /></p>
    <p><input type="hidden" value="$QuizNumber" id="QuizNumber" name="QuizNumber" /></p>

    <!-- not used, just to let timer work. -->
    <p><input type="hidden" value="" id="ElapsedTime" name="ElapsedTime" /></p>
    <p><input type="hidden" value="" id="hiddenRemainingTime" name="hiddenRemainingTime" /></p>          
    <p><input type="hidden" value="0" id="wavPlayCounter" name="wavPlayCounter" /></p>
        
    <!-- User answers. -->
    <p><input type="hidden" value="" id="ElapsedTimeAll" name="ElapsedTimeAll" /></p>
    <p><input type="hidden" value="" id="TFall" name="TFall" /></p>

EOF;

// question order
echo "\n<p>\n";
for($i = 1; $i < $qNumMax+1; $i++){
	echo "<input type=\"hidden\" value=\"" . $qOrder[$i] . "\" id=\"q$i\" name=\"q" . $i . "\" />\n";
}
echo "</p>\n";
echo "</form>";

$i_pagestyle->print_footer();
?>