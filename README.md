SBWPChromeConsoleLog
====================

This Wordpress plugin redirects PHP output errors to Chrome's javascript console instead of the normal "inlined" messages.
Developed for Chrome but kind of works in other browsers supporting the console.log javascript method

It also includes a function named consoleLog() you can use to output debug text to the console.

Examples
-----------
	consoleLog('Debug text');
	consoleLog('Lots of text',$text); // this displays the text in a collapsed group for your convenience
	consoleLog('My array',$array); // you can also output arrays or objects