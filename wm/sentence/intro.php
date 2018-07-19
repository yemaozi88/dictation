<?php
/*
 * 2018/05/04
 * introduction page for working memory test of yamauchi project
 * 
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */


// ====================
// user define
// ====================

$isDebug = false;

$config    = parse_ini_file("../config.ini", false);
require("../../_class/c_pagestyle.php");
require("../../_class/c_mysqli.php");
require("../../_class/read_manifest_json.php");
require("../../_class/variable_selection.php");


// ====================
// load query string / config.ini
// ====================

$withWav = $_POST['withWav'];
$isTest  = $_POST['isTest'];

$pageTitle = set_page_title($withWav, $isTest);

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

$srcDir           = $config["srcDir"];
$uploadDir        = $config["uploadDir"];
$sqlTableQuestion = $config["sqlTableQuestion"];
$sqlTableResult   = $config["sqlTableResult"];

$i_mysqli = new c_mysqli();
$i_pagestyle = new c_pagestyle();

// ====================
// get question information from the json file.
// ====================
$manifest_json = set_manifest_json($uploadDir, $withWav, $isTest);
$data          = loadManifestJson($manifest_json);
$iQuestionList = get_iQuestionList($data, $qSet);
$qNumMax       = count($iQuestionList);


// ====================
// make question order
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
// get trial number
// ====================
$i_mysqli->connect();

$sql_select = "SELECT username, MAX( trial_number ) AS trial_num_max FROM $sqlTableResult  "
        . "WHERE is_listening = '$withWav' AND is_test = '$isTest' AND username = '$UserName' AND groupname = '$GroupName'";
//$sql_result = mysql_query($sql_select);
$sql_result = $i_mysqli->mysqli->query($sql_select);
//$row = mysql_fetch_array($sql_result, MYSQL_ASSOC);
$row = $sql_result->fetch_assoc();
if($row["trial_num_max"] == NULL)
{
    $trialNum = 1;
}
else
{
    $trialNum = $row["trial_num_max"] + 1;
}

$i_mysqli->close();


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
    isTest: $isTest</br>
    trialNum: $trialNum</br>
    UserName: $UserName</br>
    GroupName: $GroupName</br>
    qSet: $qSet</br>
    qNumMax: $qNumMax</br>
    qOrder: $qOrder[0],$qOrder[1]</br>
    SQLselect: $sql_select</br>
    ";
}

echo <<<EOF

<form action="question.php" method="post">
    <h2>回答方法の説明</h2>
    こんにちは $UserName さん。</br>
    <br>
EOF;

if($withWav)
{
echo <<<EOF
    
    これはあなたが一度にどれだけの英文を聴いて理解しながら記憶できるかを測定するクイズです。</br>
    「問題を再生」ボタンを押すと、$qSet 文の英文が連続して流れます。<br>
    英文は一度しか流れず、聞きなおすことはできません。<br>
    <br>
    再生が終了すると、文の正誤についての解答欄が表示されます。</br>
    それぞれの英文の内容の正誤を選び，次の問題に進んでください。</br>
    <br>
    連続した英文の正誤判定が終わったら，各文の最後の単語についてタイピングして答えてください。</br>
EOF;
}
else
{
echo <<<EOF
    
    これはあなたが一度にどれだけの英文を読んで理解しながら記憶できるかを測定するクイズです。</br>
    クイズが始まると、いくつかの英文が一定時間表示されます。</br>
    </br>
    各々の英文が内容的に正しければ true, 正しくなければ false を選んでください。</br>
    （表示された直後は，すべて trueになっているので，必要に応じて，falseを選んでください）</br>
    </br>
    一定時間がたつと、解答欄が表示されます。<br>
    今，読んだ英文の最後の単語を答えてください。<br>
EOF;
}

echo <<<EOF

    最初のスペリングが表示されているので，それに続けてタイプしてください。<br>
    クイズをしている間，メモは取らないでください。</br>
    </br>
EOF;

if($isTest == 1)
{
    print("本番クイズですので，入力した解答が正しかったかどうかは表示されず，次の問題に進むようになっています。</br></br>");
}

echo <<<EOF

    問題は全部で $qNumMax 問です。<br>
    では [始める] ボタンを押しましょう。</br>
        
<!-- send variables to the next page as hidden -->
    <p><input type="hidden" value="$withWav" id="withWav" name="withWav" /></p>
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