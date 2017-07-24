$(function(){
	var host = location.origin;
	var isMobi = navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone|\(X\d+;)/i) ? 1 : 0;
	var __docW = $(window).width();
	// 320,480,640,800,960,1280,1440
	//var thumbW = __docW < 320 ? 320 : __docW < 480 ? 480 : __docW < 640 ? 640 : __docW < 800 ? 800 : __docW < 960 ? 960 : __docW < 1280 ? 1280 : __docW < 1440 ? 1440 : 0;
	
	thumbW = __docW > 750 ? 0 : 800;
	
	$LAB.script(host + '/js/lazyload.min.js').wait(function(){$('.lazy').lazyload({'threshold' : 300, 'effect': "fadeIn", 'skip_invisible' : false})})
	//.script('//hm.baidu.com/hm.js?1651acfb5771c82210471e698c16ea4c').wait();
	
	// 加载默认自动加载的图片
	$('.autolazy').each(function(){
		//*
		var tmp = $(this),
			org = tmp.attr('data-original'),
			prefix = org.substring(0, org.lastIndexOf('.')),
			suffix = org.substring(org.lastIndexOf('.')),
			mayPath = prefix + '@' + thumbW + 'x0' + suffix;
		$.ajax({
			type:"get",
			url:mayPath,
			async:true,
			success:function(){
				var img = new Image();
				img.onload = function(){
					tmp[0].src = this.src;
					tmp.removeAttr('data-original').removeClass('autolazy');
				}
				img.src = mayPath;
			},
			error:function(){
				var img = new Image();
				img.onload = function(){
					tmp[0].src = this.src;
					tmp.removeAttr('data-original').removeClass('autolazy');
				}
				img.src = tmp.attr('data-original');
			}
		});
		//*/
	});
	
	// 焦点图
	if((focus_fb = $('.focus-figure-box')).length){
		// 设置焦点图基本布局
		var ffb_w = focus_fb.width(),
			ffbln = focus_fb.find('li').length,
			ffbul = focus_fb.find('ul'),
			ffb_t;
		ffbul.width(ffb_w * ffbln).find('li').css({'float':'left', 'width':ffb_w, 'display':'block'});
		
		// 重设视口时调整布局
		$(window).resize(function(){
			var curr = focus_fb.find('.f-f-b-on').index();
			window.clearInterval(ffb_t);
			ffb_w = focus_fb.width();
			ffbul.width(ffb_w * ffbln).find('li').css({'width':ffb_w});
			ffbul.css('margin-left', -(ffb_w * curr));
			if(isMobi){
				ffb_t = window.setInterval(rotationAuto, 3000);
			}
		});
		
		// 移动端只给划动事件
		if(isMobi && !(window.orientation == 90 || window.orientation == -90)){
			_swipe(focus_fb[0], 'left', function(){
				var curr = focus_fb.find('.f-f-b-on').index(),
					next = curr + 1;
				next = next < ffbln ? next : 0;
				focus_fb.find('.focus-figure-btn span').eq(next).addClass('f-f-b-on').siblings().removeClass('f-f-b-on');
				ffbul.animate({'margin-left': -(ffb_w * next)}, 300);
			});
			_swipe(focus_fb[0], 'right', function(){
				var curr = focus_fb.find('.f-f-b-on').index(),
					next = curr - 1;
				next = next < 0 ? ffbln - 1 : next;
				focus_fb.find('.focus-figure-btn span').eq(next).addClass('f-f-b-on').siblings().removeClass('f-f-b-on');
				ffbul.animate({'margin-left': -(ffb_w * next)}, 300);
			});
		}
		else{
			// 自动轮播
			ffb_t = window.setInterval(rotationAuto, 3000);
			
			// 鼠标悬停事件
			focus_fb.find('.focus-figure-btn span').mouseover(function(){
				window.clearInterval(ffb_t);
				$(this).addClass('f-f-b-on').siblings().removeClass('f-f-b-on');
				ffbul.animate({'margin-left': -(ffb_w * $(this).index())}, 300);
			})
			.mouseout(function(){
				ffb_t = window.setInterval(rotationAuto, 3000);
			});
			
			// 自动轮播函数
			function rotationAuto(){
				var curr = focus_fb.find('.f-f-b-on').index(),
					next = curr + 1;
				next = next < ffbln ? next : 0;
				focus_fb.find('.focus-figure-btn span').eq(next).addClass('f-f-b-on').siblings().removeClass('f-f-b-on');
				ffbul.animate({'margin-left': -(ffb_w * next)}, 300);
			}
		}
	}
	
	// 快捷栏目交互
	if((qbx = $('.quick-box li a')).length){
		qbx.not('.ql-curr').mouseover(function(){
			$(this).addClass('ql-curr');
		})
		.mouseout(function(){
			$(this).removeClass('ql-curr');
		})
	}
	
	// 首页列表交互
	if((idxl = $('.idx-gary-bg')).length){
		idxl.mouseover(function(){
			$(this).addClass('idx-list-curr');
		})
		.mouseout(function(){
			$(this).removeClass('idx-list-curr');
		})
	}
	
	// 手机端底部合作交互
	$('.mobile-footer .four-list-box li a').not(':last').click(function(){
		$('.mobile-footer-nav').hide();
		$('.mobile-footer-cover').hide();
		$('.mobile-footer-list div').eq($(this).parent().index()).toggle().siblings().hide();
	});
	
	// 手机端底部导航展开交互
	$('.mobile-footer .four-list-box li a:last').click(function(){
		$('.mobile-footer-nav').toggle();
		$('.mobile-footer-cover').toggle();
		$('.mobile-footer-list div').hide();
	});
	$('.mobile-footer-cover').click(function(){
		$(this).hide();
		$('.mobile-footer-nav').hide();
	})
	$('.mobile-footer-cover')[0].addEventListener('touchmove', function(){
		$(this).hide();
		$('.mobile-footer-nav').hide();
	}, false)
	
	// 加载视频及其支持
	if((vpr = $('.video-player')).length){
		var vi = 0;
		vpr.each(function(vi){
			var source = $(this).attr('data-source'),
				vprsign = 'video_player_' + vi,
				vprw = $(this).parent().width();
				
			vi++;
			vprw = vprw > 720 ? 720 : vprw;
			vprh = vprw * 405 / 720;
			$(this).attr('id', vprsign).css({'width':vprw, 'height':vprh, 'margin':'0 auto'});
			
			$LAB.script(host + '/includes/ckplayer/ckplayer.js').wait(function(){
				// 初始化视频播放器
				var flashvars={
			        f:'http://v.refor178.com/'+source,
			        c:0,
			        h:1,
			        my_url:encodeURIComponent(window.location.href),
			        my_title:encodeURIComponent(document.title)
			    };
			    var params={bgcolor:'#FFF',allowFullScreen:true,allowScriptAccess:'always',wmode:'transparent'};
			    var video=['http://v.refor178.com/' + source + '->video/mp4'];
			    CKobject.embed('/ckplayer/ckplayer.swf', vprsign, vprsign, vprw, vprh, true, flashvars, video, params);
			});
		})
	}
	
	
});

/**
 * 为对象绑定手势滑动事件
 */
function _swipe(obj, direction, callback){
	var StartX = 0,
		x = 0;
	
	var _swipestart = function(e){
		StartX = e.targetTouches[0].pageX;
	}
	var _swipemove = function(e){
		e.preventDefault();
		x = e.targetTouches[0].pageX - StartX;
	}
	var _swipeend = function(e){
		if(x < -20 && direction == 'left'){
			callback();
		}
		if(x > 20 && direction == 'right'){
			callback();
		}
	}
	
	obj.addEventListener('touchstart', _swipestart, false);
	obj.addEventListener('touchmove', _swipemove, false);
	obj.addEventListener('touchend', _swipeend, false);
}












