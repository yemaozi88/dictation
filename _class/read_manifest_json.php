<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function loadManifestJson($manifest_json)
{
    # read all contents in json file.
    $json = file_get_contents($manifest_json);
    if ($json === false) {
        throw new RuntimeException("$manifest_json not found.");
    }
    $data = json_decode($json, true);

    return $data;
}

function get_iQuestionList($data, $sentenceNumAtOnce)
{
    # get index of questions included in the sentenceNumAtOnce.
    $qNumMax = count($data["questionSets"]);
    $iQuestionList = array();
    for($i = 0; $i < $qNumMax; $i++)
    {
        $qSet = $data["questionSets"][$i]["questionNum"];
        if($qSet == $sentenceNumAtOnce)
        {
            array_push($iQuestionList, $i);
        }
    }
    
    return $iQuestionList;
}

function getQuestionNumMax($data, $sentenceNumAtOnce)
{
    $iQuestionList = get_iQuestionList($data, $sentenceNumAtOnce);
    return count($iQuestionList);
}

function getQuestionInfo($data, $sentenceNumAtOnce, $qNum)
{
    $iQuestionList = get_iQuestionList($data, $sentenceNumAtOnce);
    $qNumMax = count($iQuestionList);
    
    if($qNum-1 > $qNumMax)
    {
        exit('quiz number specified exceed the quiz number registered.');
    }
    else
    {
        $questionInfo = $data["questionSets"][$iQuestionList[$qNum-1]]; 
    }
    
    return $questionInfo;
}

function getWavNames($questionInfo)
{    
    $sentenceNumMax = count($questionInfo['wavs']);
    if(!empty($questionInfo))
    {
        for($i = 0; $i < $sentenceNumMax; $i++)
        {
            $wavs[$i] = $questionInfo["wavs"][$i];
        }
    }
    return $wavs;
}

function getAnswers($questionInfo)
{
    //$sentenceNumMax = count($questionInfo['wavs']);
    $sentenceNumMax = $questionInfo["questionNum"];
    for($i = 0; $i < $sentenceNumMax; $i++)
    {
        $answers[$i] = $questionInfo["answers"][$i];
    }

    return $answers;
}
        


//$manifest_json = '../uploader/upload/rst/practice/manifest.json';
//$sentenceNumAtOnce = 2;
//$qNum = 1;

//$data = loadManifestJson($manifest_json);
//$iQuestionList = get_iQuestionList($data, $sentenceNumAtOnce);
//$qNumMax = count($iQuestionList);

//$questionInfo = getQuestionInfo($data, $sentenceNumAtOnce, $qNum);

// $wavs = getWavNames($questionInfo);
//$answers = getAnswers($questionInfo);
/*
print($answers[0]['correctness']);
if($answers[0]['correctness'] == 1)
{
    print('correct!');
}
*/
//print_r($answers);

?>
