//
// 2013/10/13
// JavaScript timer for working memory
//
// AUTHOR
// Aki Kunikoshi
// yemaozi88@gmail.com
//

function wavPlay() {
	document.getElementById('playbutton').disabled = true;
	document.getElementById('question').play();
	//document.getElementById('wavPlayCounter').value++;
}

function wavCanPlay() {
	document.getElementById('playbutton').disabled = false;
	var wavPlayCounter = 0;
}

function dispQuestion(){
	document.getElementById("dispW").innerHTML 
	= "What was the last word in the sentence? <t><input type='text' id='aWord' name='aWord' value='' />";

	document.getElementById("dispQ").innerHTML 
	= "Question1: blablabla<t><input type='radio' id='answer' name='answer' value='1' />True <input type='radio' id='answer' name='answer' value='0' />False<br />Question2: blablabla<t><input type='radio' id='answer' name='answer' value='1' />True <input type='radio' id='answer' name='answer' value='0' />False";

	document.getElementById("goNext").innerHTML 
	= "<input type='submit' value='Next question' />";	
}

function clickButton(){
		//tid = setInterval('countTime()', 10);
		wavPlay();
}