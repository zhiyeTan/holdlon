$(function(){
	/* 调整主内容容器尺寸 */
	var rpmctw = $(window).width();
	var rpmssh = $(window).height() - 50;
	rpmctw -= !$('#rpm-sidebar').length || $('#rpm-sidebar').css('display') == 'none' ? 40 : 220;
	rpmctw -= !$('#rpm-sidebar-stair').length || $('#rpm-sidebar-stair').css('display') == 'none' ? 0 : 160;
	$('#rpm-content').width(rpmctw);
	$('#rpm-sidebar').height(rpmssh);
	$('#rpm-sidebar-stair').height(rpmssh);
	$('#rpm-content').height(rpmssh - 20);
	
	/* 一级菜单点击事件 */
	$('#rpm-sidebar').find('li a').click(function(){
		$(this).addClass('on-sidebar-firsta').parent().siblings().find('a').removeClass('on-sidebar-firsta');
		$('#rpm-sidebar-stair-title').html($(this).text());
		$('#rpm-sidebar-stair').show().find('ul').eq($(this).parent().index()).show().siblings().not('#rpm-sidebar-stair-title').hide();
		$('#rpm-content').width($(window).width() - 380);
	});
});
$(window).resize(function(){
	/* 重置窗口大小是调整主内容容器尺寸 */
	var rpmctw = $(window).width();
	var rpmssh = $(window).height() - 50;
	rpmctw -= !$('#rpm-sidebar').length || $('#rpm-sidebar').css('display') == 'none' ? 40 : 220;
	rpmctw -= !$('#rpm-sidebar-stair').length || $('#rpm-sidebar-stair').css('display') == 'none' ? 0 : 160;
	$('#rpm-content').width(rpmctw);
	$('#rpm-sidebar').height(rpmssh);
	$('#rpm-sidebar-stair').height(rpmssh);
	$('#rpm-content').height(rpmssh - 20);
});


/*
 * 自动跳转到指定url
 * 
 */
function url_auto_jump(url){
	var second = 5;
	var _t;
	_t = window.setInterval(function(){
		if(second < 1){
			if(url.indexOf('script') < 0){
				window.location.href = url;
			}
			else{
				history.back();
			}
		}
	    $('#jump_seconds').html(second);
	    second--;
    }, 1000);
}
