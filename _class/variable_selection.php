<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function set_page_title($with_wav, $is_test)
{
    if($with_wav == 1 && $is_test == 1)
    {
            $page_title = '聴いて答える問題（実力テスト）';
    }
    elseif($with_wav == 0 && $is_test == 1)
    {
            $page_title = '見て答える問題（実力テスト）';
    }
    elseif($with_wav == 1 && $is_test == 0)
    {;
            $page_title = '聴いて答える問題（練習）';
    }
    elseif($with_wav == 0 && $is_test == 0)
    {
            $page_title = '見て答える問題（練習）';
    }

    return $page_title;
}


function set_manifest_json($upload_dir, $with_wav, $is_test)
{
    if($with_wav == 1 && $is_test == 1)
    {
        $manifest_json = $upload_dir . '/lst/test/manifest.json';
    }
    elseif($with_wav == 0 && $is_test == 1)
    {
        $manifest_json = $upload_dir . '/rst/test/manifest.json';
    }
    elseif($with_wav == 1 && $is_test == 0)
    {
        $manifest_json = $upload_dir . '/lst/practice/manifest.json';
    }
    elseif($with_wav == 0 && $is_test == 0)
    {
        $manifest_json = $upload_dir . '/rst/practice/manifest.json';
    }
    
    return $manifest_json;
}
   
?>

