<div id="qingmiphp_debug" style="<?php if(!self::$info && !self::$includefile) echo 'display:none;';?>margin:0px;padding:0px;font-size:14px;font-family:'微软雅黑';line-height:20px;text-align:left;border-top:1px solid #ececec;color:#000;background:#fff;position:fixed;_position:absolute;bottom:0;left:0;width:100%;z-index:999999;">
	<div style="padding-left:15px;height:40px;line-height:40px;border-bottom:1px solid #eee;">
		<span onclick="close_qingmiphp_debug()" style="cursor:pointer;float:right;width:25px;color:#333;padding-top:12px;overflow:hidden;">
			<img style="height:15px;vertical-align:top;" title="关闭" alt="关闭" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw==">
		</span>
		<span onclick="min_qingmiphp_debug()" style="cursor:pointer;float:right;color:#333;padding:0 10px;margin-right:10px;" title="最小化">—</span>
		<span style="font-size:16px"><b>运行信息</b>( <span style="color:red"><?php echo self::spent();?></span> 秒):</span>
	</div>
	<div style="clear:both;margin:0px;padding:0 10px;height:200px;overflow:auto;">
	<?php
		if(self::$info){
			echo '<div style="margin-top:5px;">［系统信息］</div>';
			foreach(self::$info as $info){
				echo '<div style="padding-left:20px">'.$info.'</div>';
			}
		}
		if(self::$includefile){
			echo '<div style="margin-top:5px;">［加载信息］</div>';
			foreach(self::$includefile as $includefile){
				echo '<div style="padding-left:20px">'.$includefile.'</div>';
			}
		}
		if(self::$sqls) {
			echo '<div style="margin-top:5px;">［SQL语句］</div>';
			foreach(self::$sqls as $sql){
				echo '<div style="padding-left:20px">'.$sql.'</div>';
			}
		}
		echo '<div style="margin-top:5px;">［其他信息］</div>';
		echo '<div style="padding-left:20px">服务器信息：'.$_SERVER['SERVER_SOFTWARE'].'</div>';
		echo '<div style="padding-left:20px">会话ID：'.session_id().'</div>';
	?>
	</div>
</div>
<div id="qingmiphp_open" onclick="show_qingmiphp_debug()" title="查看详细" style="<?php if(self::$info || self::$includefile) echo 'display:none;';?>height:28px;line-height:28px;border-top-left-radius:3px;z-index:999998;font-family:'微软雅黑';float:right;text-align: right;overflow:hidden;position:fixed;_position:absolute;bottom:0;right:0;background:#232323;color:#fff;font-size:14px;padding:0 8px;cursor:pointer;"><?php echo self::spent();?>s
</div>	
<script type="text/javascript">
	function show_qingmiphp_debug(){
		document.getElementById('qingmiphp_debug').style.display = 'block';
		document.getElementById('qingmiphp_open').style.display = 'none';
	}
	function min_qingmiphp_debug(){
		document.getElementById('qingmiphp_debug').style.display = 'none';
		document.getElementById('qingmiphp_open').style.display = 'block';
	}
	function close_qingmiphp_debug(){
		document.getElementById('qingmiphp_debug').style.display = 'none';
		document.getElementById('qingmiphp_open').style.display = 'none';
	}
</script>