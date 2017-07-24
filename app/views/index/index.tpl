{$widgetView_header}
..
..
<ul>
	{foreach from=$list key=k item=v}
	<li>{$v.0}<span>{$v.s}</span></li>
	{foreach from=$v.child key=kk item=vv}
	<div>{$vv}{if $kk>0}22{/if}</div>
	{/foreach}
	{/foreach}
</ul>
<div id="test"></div>
<script>
	$.getJSON('http://newholdlon:8082/api', function(data) {
		console.log(data)
		$.each(data, function(i, value){
			console.log(value[0]);
		})
	});
</script>
{if $data === 'a'}
成功
{elseif $data === 'b'}
失败
{else}
我最厉害{$data }
{/if}

{$widgetView_footer}
