//
// 2010/04/13
// JavaScript timer for word quiz
//
// NOTE
// RemainingTime needs to load $timeMax
//
// HISTORY
// 2018/05/07 added functions for wm/rst
// 2012/10/23 remove wav related functions
// 2012/10/19 bugfix; 2nd augment of setInterval in clickButton() is fixed
// 2012/09/23 rename variables, changed function clickButton() and added wavPlay() and wavCanPlay() 
// 2012/09/13 Arnaud van Galen modified the format of display time
//
// AUTHOR
// Aki Kunikoshi
// yemaozi88@gmail.com
//

var ElapsedTimeS 	= 0; // sec part of Elapsed Time
var ElapsedTimeMS 	= 0; // msec part of Elapsed Time
var ElapsedTimeS_str;
var ElapsedTimeMS_str;

var RemainingTimeS 	= 0; // sec part of Remaining Time
var RemainingTimeMS     = 0; // msec part of Remaining Time
var RemainingTimeS_str;
var RemainingTimeMS_str;

function countTime(){
	ElapsedTimeMS++;

	//--------------
	// Elapsed Time 
	//--------------

	if(ElapsedTimeMS == 100){
		ElapsedTimeS++;
		ElapsedTimeMS = 0;
	}

	ElapsedTimeS_str 	= ElapsedTimeS;
	ElapsedTimeMS_str 	= ElapsedTimeMS;

	if(ElapsedTimeS		< 10) ElapsedTimeS_str 	= "0" + ElapsedTimeS_str;
	if(ElapsedTimeMS 	< 10) ElapsedTimeMS_str = "0" + ElapsedTimeMS_str;

	document.getElementById("ElapsedTime").value = ElapsedTimeS_str + "" + ElapsedTimeMS_str;
        
	//----------------
	// Remaining Time
	//----------------

	RemainingTimeS 	= timeMax - ElapsedTimeS - 1;
	RemainingTimeMS = 100 - ElapsedTimeMS;
	if(RemainingTimeMS == 100) RemainingTimeMS = 0;

	if(RemainingTimeS < 0){
		RemainingTimeS 	= 0;
		RemainingTimeMS = 0;
	}

	RemainingTimeS_str 	= RemainingTimeS;
	RemainingTimeMS_str = RemainingTimeMS;

	if(RemainingTimeS 	< 10) RemainingTimeS_str 	= "0" + RemainingTimeS_str;
	if(RemainingTimeMS 	< 10) RemainingTimeMS_str 	= "0" + RemainingTimeMS_str;

	document.getElementById("RemainingTime").firstChild.nodeValue = RemainingTimeS_str + ":" + RemainingTimeMS_str;
	document.getElementById("hiddenRemainingTime").value = RemainingTimeS_str + "" + RemainingTimeMS_str;

	
	//----------------
	// Document Color
	//----------------
	if( RemainingTimeS <= 5 && RemainingTimeS > 2 ){
		document.fgColor='orange';
	}else if( RemainingTimeS <= 2 ){
		document.fgColor='red';
	}
}

function wavPlay() {
	document.getElementById('playbutton').disabled = true;
	document.getElementById('question').play();
	document.getElementById('wavPlayCounter').value++;
}

function wavCanPlay() {
	document.getElementById('playbutton').disabled = false;
        var wavPlayCounter = 0;
}

function clickButton(){
	tid = setInterval('countTime()', 10);
	wavPlay();
}

function clickButton_show(qNum, qSet, withWav){

    if(withWav===1){
        document.getElementById("qTitle").innerHTML = qSet + " 文中 第　" + qNum + " 文目</center>";	        
        document.getElementById("qText").innerHTML = "<center><font size=\"4\">文章の正誤を答えてください。</font></center>";
    }
    else{
        tid = setInterval('countTime()', 10);
    }
    show_question_text(qNum, qSet, withWav);
}

function show_question_text(qNum, qSet, withWav){
    aTF_i = "aTF" + qNum;
    
    if(withWav===0){
        document.getElementById("qTitle").innerHTML = qSet + " 文中 第　" + qNum + " 文目</center>";	
        document.getElementById("qText").innerHTML = "<center><font size=\"5\"><b>" + qSentence[qNum] + "</b></font></center>";
        show_question_tf(qNum, qSet, withWav);
    }else{
        document.getElementById('question').play();
        
        document.getElementById('playbutton').disabled = true;
        document.getElementById("qText").innerHTML = "<center><font size=\"4\">文章の正誤を答えてください。</font></center>";
    }
}

function show_question_tf(qNum, qSet, withWav){
    if(withWav===1){
        tid = setInterval('countTime()', 10);
    }
    document.getElementById("aTF").innerHTML
	= "<form id=\"formTF\">\n\
<input type='radio' id='" + aTF_i + "' name='" + aTF_i + "' value='1' onclick='clickButton_next(" + qNum + ',' + qSet + ',' + withWav + "); ' />正しい\n\
<input type='radio' id='" + aTF_i + "' name='" + aTF_i + "' value='0' onclick='clickButton_next(" + qNum + ',' + qSet + ',' + withWav + "); ' />誤り\n\
</form>";
    //document.getElementById("goNext").disabled = false;
    document.getElementById("TFall").innerHTML = "";
}

function clickButton_next(qNum, qSet, withWav){
    //document.getElementById('showbutton').disabled = false;

    // check which of T/F is chosen.
    aTF_i = "aTF" + qNum;
    var element = document.getElementById("formTF");
    var a = element[aTF_i].value;
    document.getElementById("TFall").value += a + ",";
 
 
    // reset the timer.
    clearInterval(tid);
    document.getElementById("ElapsedTimeAll").value += ElapsedTimeS_str + "" + ElapsedTimeMS_str + ",";        
    document.getElementById("RemainingTime").innerHTML = timeMax + ":00";
    ElapsedTimeS    = 0; // sec part of Elapsed Time
    ElapsedTimeMS   = 0; // msec part of Elapsed Time	
    RemainingTimeS  = 0; // sec part of Remaining Time
    RemainingTimeMS = 0; // msec part of Remaining Time
        
            
    // clear the previous question.
    document.getElementById("qText").innerHTML = "";		
    document.getElementById("aTF").innerHTML = "";

    // reset the buttons.
    qNum += 1;    
    if(qNum <= qSet){
        document.getElementById("qTitle").innerHTML = qSet + " 文中 第　" + qNum + " 文目</center>";	        
        if(withWav===0){
            document.getElementById('showbutton').disabled = false;
        }
        else{
            document.getElementById('playbutton').disabled = false;
        }        
    }else{
        show_question_last_word(qSet, withWav);
    }
 
    // update the button.
    var newAttribute = "clickButton_show(" + qNum + ", " + qSet + ", " + withWav + ")";
    if(withWav===0){
        document.getElementById("showbutton").setAttribute("onclick", newAttribute);
    }
    else
    {        
        document.getElementById("playbutton").setAttribute("onclick", newAttribute);
        newAttribute = "show_question_tf(" + qNum + ", " + qSet + ", " + withWav + ")";
        document.getElementById("question").setAttribute("onended", newAttribute);
        document.getElementById("src_wav").setAttribute("src", qWav[qNum]);
        if(qNum <= qSet){
            document.getElementById('question').load();
            document.getElementById('playbutton').disabled = false;
        }
    }
}

function show_question_last_word(num, withWav){
    /* students may not answer the previous questions after seeing the question about the last words. */
    if(withWav===0){
        document.getElementById('showbutton').disabled = true;
    }
    else{
        document.getElementById('playbutton').disabled = true;
    }       
    for (var i=1; i<=num; i++){
	wNum = "aWord" + i;
	document.getElementById(wNum).innerHTML
            = "第" + i + "文目：　" + qWord1[i] + "<t><input type='text' id='" + wNum + "' name='" + wNum + "' value='' /><br />";
    }
    document.getElementById("sendResult").innerHTML 
            = "<input type='submit' style=\"color:white;background-color: #4d1a00;border-color:white\" value='回答を送信' />";
}