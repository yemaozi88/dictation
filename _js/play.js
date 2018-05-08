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
	document.getElementById('sentence').play();	
}

function clickButton(){
		//tid = setInterval('countTime()', 10);
		wavPlay();
}

function wavPlayNext(num) {
	qNum = "sentence" + num;
	document.getElementById(qNum).play();	
}

function wavCanPlay() {
	document.getElementById('playbutton').disabled = false;
	var wavPlayCounter = 0;
}

function dispQuestion(num){
	for (var i=1; i<=num; i++)
	{
		hNum = "aTitle" + i;
		tNum = "aTrue" + i;
		wNum = "aWord" + i;
		
		document.getElementById(hNum).innerHTML = "<h3/> 第" + i + "文</h3/>";
		document.getElementById(tNum).innerHTML 
			= "（1）文章の内容：<t><input type='radio' id='" + tNum + "' name='" + tNum + "' value='1' />正しい<input type='radio' id='" + tNum + "' name='" + tNum + "' value='0' />誤り<br />";
		//document.getElementById(wNum).innerHTML = "（2）最後の単語：<t><input type='text' id='" + wNum + "' name='" + wNum + "' value='" + qWord1[i] + "' /><br />";
		document.getElementById(wNum).innerHTML = "（2）最後の単語：" + qWord1[i] + "<t><input type='text' id='" + wNum + "' name='" + wNum + "' value='' /><br />";
	}
	document.getElementById("goNext").innerHTML
	= "<input type='submit' value='回答を送信' />";
}