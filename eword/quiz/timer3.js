//
// 2010/04/13
// JavaScript timer for word quiz
//
// NOTE
// RemainingTime needs to load $timeMax
//
// HISTORY
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
var RemainingTimeMS = 0; // msec part of Remaining Time
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

	//document.getElementsByName("ElapsedTime").value = ElapsedTimeS_str + ":" + ElapsedTimeMS_str;
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
}

function clickButton(){
		tid = setInterval('countTime()', 10);
		wavPlay();
}