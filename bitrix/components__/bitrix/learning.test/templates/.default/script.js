function checkForEmpty(warnMessage)
{
	var answers = document.getElementsByName('answer[]');
	if(!answers || answers.length==0)
		answers = document.getElementsByName('answer');
	if(!answers || answers.length==0)
		return true;
	var j = 0;
	for(var i=0;i<answers.length;i++)
		if(answers[i].selected || answers[i].checked)
			j++;
	if(j>0)
		return true;
	else
		return confirm(warnMessage);
}

function UpdateClock(seconds)
{
	if(clockID)
		clearTimeout(clockID);

	if (!(seconds >= 0))
		return;

	var SecondsToEnd = seconds;

	var strTime = "";
	var hours = Math.floor(seconds/3600);

	if (hours > 0)
	{
		strTime += (hours < 10 ? "0" : "") + hours + ":";
		seconds = seconds - hours*3600;
	}
	else
	{
		strTime += "00:";
	}

	var minutes = Math.floor(seconds/60);

	if (minutes > 0)
	{
		strTime += (minutes < 10 ? "0" : "") + minutes + ":";
		seconds = seconds - minutes*60;
	}
	else
	{
		strTime += "00:";
	}
	
	var sec = (seconds%60);
	strTime += (sec < 10 ? "0" : "") + sec;

	//alert(strTime);

	document.getElementById("learn-test-timer").innerHTML = strTime;

	clockID = setTimeout("UpdateClock("+(SecondsToEnd-1)+")", 950);
}