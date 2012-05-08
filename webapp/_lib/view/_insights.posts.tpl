{foreach from=$i->related_data key=uid item=p name=bar}
    {include file="_insights.post.tpl" post=$p}
 {/foreach}
