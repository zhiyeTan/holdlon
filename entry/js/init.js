(function(){
	var win = window,
		doc = win.document,
		docEl = doc.documentElement,
		resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';

	function setRem(){
		var width = docEl.getBoundingClientRect().width,
			height = docEl.getBoundingClientRect().height,
			rem;
		width = width > 1200 ? 1200 : width;
		rem = width / 12;
		docEl.style.fontSize = rem + 'px';
		
		if(!navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone|\(X\d+;)/i) || width > 860){
			docEl.id = '';
		}
		else{
			docEl.id = '_mobile';
		}
		
		// 横屏处理
		//if(window.orientation == 90 || window.orientation == -90){}
	}
	
	win.addEventListener(resizeEvt, setRem, false);
	doc.addEventListener('DOMContentLoaded', setRem, false);
	
	$LAB.script(location.origin + '/js/jquery-1.10.2.min.js').wait()
	.script(location.origin + '/js/func.js').wait();
	
})();





