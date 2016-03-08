
<div id="HY-BOX" style="z-index: 1000;display:none;position: fixed; left: 0px; right: 0px; bottom: 0px; height: 200px; overflow-y: auto; background-color: rgb(245, 245, 245); border-top: 1px solid rgb(224, 224, 224);">
    <div id="HY-CLOSE" style="z-index: 999;font-size: 15pt; color: rgb(0, 0, 0); cursor: pointer; position: absolute; right: 9px; font-weight: bold; padding: 0px 10px;">−</div>
    <div style="z-index: 998;border-bottom: solid 1px #D2D2D2;height: 33px;position: absolute;width: 100%;background-color: #FFF;">
        <ul id="HY-LIST" class="HY1">
            <li class="action">运行</li>
            <li>数据库操作</li>
            <li>文件加载</li>
            <li>类库加载</li>
          

        </ul>
    </div>
    <div id="HY-ID0" style="height: 198px;overflow-y: auto;">
        <ul class="HY">
            <li style="border-top: solid 1px #D2D2D2;">请求参数：<?php echo $url; ?></li>
            <li>控制器与方法：<?php echo $_Action; ?> <?php echo $_Fun; ?></li>
            <li>运行时间：<?php echo round($GLOBALS['END_TIME'] - $GLOBALS['START_TIME'],4); ?> s</li>
            <li>内存使用：<?php echo round((memory_get_usage() - $GLOBALS['START_MEMORY'])/1024); ?> Kb</li>
            <li id="HY-COOKIE">COOKIE：</li>
        </ul>
    </div>
    <div id="HY-ID1" style="height: 198px;overflow-y: auto;display:none;">
        <ul class="HY">
            <li style="border-top: solid 1px #D2D2D2;">SQL查询</li>


                <?php foreach ($DEBUG_SQL as $v): ?>
                    <li><?php echo $v; ?></li>
                <?php endforeach; ?>



        </ul>
    </div>
    <div id="HY-ID2" style="height: 198px;overflow-y: auto;display:none;">
        <ul class="HY">
            <li style="border-top: solid 1px #D2D2D2;">文件加载统计 (<?php echo count(get_included_files()); ?>)</li>
            <?php foreach (get_included_files() as $v): ?>
                <li><?php echo $v; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="HY-ID3" style="height: 198px;overflow-y: auto;display:none;">
        <ul class="HY">
            <li style="border-top: solid 1px #D2D2D2;">new \HY</li>
            <?php foreach ($DEBUG_CLASS as $k => $v): ?>
                <li>new \<?php echo $k; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
   

</div>

<style>
#HY-BOX{
    font-family: "微软雅黑";
}
.HY{

    padding: 0;
margin: 0;
margin-top: 45px;
word-wrap: break-word;word-break:break-all;
}
.HY1{
    padding: 0;
margin: 0;
}
.HY1>li.action{
    background-color: #F3F3F3;
    color: #078600;
}
.HY1>li{
    list-style: none;
    float: left;
    padding: 5px 10px;
background-color: #FFF;
cursor: pointer;
}
.HY>li{
    font-size: 16px;
    list-style: none;
    background-color: #FFF;
    border-bottom: solid 1px #D2D2D2;
    padding: 5px 10px;

}
</style>
<?php


echo '<div id="HY-SHOW" onclick style="position: fixed; right: 0px; bottom: 0px; display: block;background-color: #6E185F;color: #FFFFFF;padding: 5px 10px;font-weight: bold;    cursor: pointer;">程序耗时 ' .round($GLOBALS['END_TIME'] - $GLOBALS['START_TIME'],4) ."毫秒, 内存:". round((memory_get_usage() - $GLOBALS['START_MEMORY'])/1024).'KB</div>';
?>
<script>
(function(){

var cookie   = document.cookie.match(/HY_DEBUG=(\d)/);
var HY_BOX = document.getElementById('HY-BOX');
var HY_open =  document.getElementById('HY-SHOW');
var HY_close =  document.getElementById('HY-CLOSE');

var HY_LI  = document.getElementById('HY-LIST').getElementsByTagName('li');



document.getElementById('HY-COOKIE').innerHTML    = 'COOKIE：'+document.cookie;

if(cookie && typeof cookie[1] != 'undefined')
    cookie = cookie[1];
else
    cookie = 0;


//HY-ID1

if(cookie == 0){
    HY_BOX.style.display = 'none';
    HY_open.style.display = 'block';
}else{
    HY_BOX.style.display = 'block';
	HY_open.style.display = 'none';
}

HY_open.onclick = function(){
	HY_BOX.style.display = 'block';
	HY_open.style.display = 'none';
	document.cookie = 'HY_DEBUG=1';
    //console.log(document.cookie);
}
HY_close.onclick = function(){
    HY_BOX.style.display = 'none';
    HY_open.style.display = 'block';
    document.cookie = 'HY_DEBUG=0'
}


for(var i = 0; i < HY_LI.length; i++){
    //console.log(i);
	HY_LI[i].onclick = (function(i){

		return function(){

			for(var j = 0; j < HY_LI.length; j++){
                //console.log(i);
				HY_LI[j].className  = '';
                document.getElementById('HY-ID'+j).style.display = 'none';
				//HY_LI[j].style.color = '#999';
			}
            HY_LI[i].className = 'action';
            document.getElementById('HY-ID'+i).style.display = 'block';
		}
	})(i)
}

})();
</script>
