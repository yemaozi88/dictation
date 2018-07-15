<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require("../../_class/c_wav_control.php");

$wav_dir = '../../uploader/upload/lst/practice';
$wav1 = '1.wav';
$wav2 = '2.wav';
$wav7 = '7.wav';
$wavs = array($wav1, $wav2, $wav7);

$wav_out = '../../uploader/upload/lst/practice/test.wav';



wavConcatenate($wav_dir, $wavs, $wav_out);
?>
