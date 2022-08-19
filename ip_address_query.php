<?php
/*
Plugin Name: 评论区显示ip归属地
Plugin URI: https://ozxc.cn
Description: 评论区显示ip归属地
Version: 1.0.0
Author: GuaiMiu
Author URI: https://ozxc.cn
License: GPL2
*/
function convertip($ip) { 
    $ip1num = 0;
    $ip2num = 0;
    $ipAddr1 ="";
    $ipAddr2 ="";
    $dat_path = './qqwry.dat';        //纯真数据库文件位置
    if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) { 
      return 'IP Address Error'; 
    }  
    if(!$fd = @fopen($dat_path, 'rb')){ 
      return 'IP date file not exists or access denied'; 
    }  
    $ip = explode('.', $ip); 
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];  
    $DataBegin = fread($fd, 4); 
    $DataEnd = fread($fd, 4); 
    $ipbegin = implode('', unpack('L', $DataBegin)); 
    if($ipbegin < 0) $ipbegin += pow(2, 32); 
      $ipend = implode('', unpack('L', $DataEnd)); 
    if($ipend < 0) $ipend += pow(2, 32); 
      $ipAllNum = ($ipend - $ipbegin) / 7 + 1; 
    $BeginNum = 0; 
    $EndNum = $ipAllNum;  
    while($ip1num>$ipNum || $ip2num<$ipNum) { 
      $Middle= intval(($EndNum + $BeginNum) / 2); 
      fseek($fd, $ipbegin + 7 * $Middle); 
      $ipData1 = fread($fd, 4); 
      if(strlen($ipData1) < 4) { 
        fclose($fd); 
        return 'System Error1'; 
      }
      $ip1num = implode('', unpack('L', $ipData1)); 
      if($ip1num < 0) $ip1num += pow(2, 32); 
  
      if($ip1num > $ipNum) { 
        $EndNum = $Middle; 
        continue; 
      } 
      $DataSeek = fread($fd, 3); 
      if(strlen($DataSeek) < 3) { 
        fclose($fd); 
        return 'System Error1'; 
      } 
      $DataSeek = implode('', unpack('L', $DataSeek.chr(0))); 
      fseek($fd, $DataSeek); 
      $ipData2 = fread($fd, 4); 
      if(strlen($ipData2) < 4) { 
        fclose($fd); 
        return 'System Error1'; 
      } 
      $ip2num = implode('', unpack('L', $ipData2)); 
      if($ip2num < 0) $ip2num += pow(2, 32);  
        if($ip2num < $ipNum) { 
          if($Middle == $BeginNum) { 
            fclose($fd); 
            return 'Unknown'; 
          } 
          $BeginNum = $Middle; 
        } 
      }  
      $ipFlag = fread($fd, 1); 
      if($ipFlag == chr(1)) { 
        $ipSeek = fread($fd, 3); 
        if(strlen($ipSeek) < 3) { 
          fclose($fd); 
          return 'System Error'; 
        } 
        $ipSeek = implode('', unpack('L', $ipSeek.chr(0))); 
        fseek($fd, $ipSeek); 
        $ipFlag = fread($fd, 1); 
      } 
      if($ipFlag == chr(2)) { 
        $AddrSeek = fread($fd, 3); 
        if(strlen($AddrSeek) < 3) { 
        fclose($fd); 
        return 'System Error'; 
      } 
      $ipFlag = fread($fd, 1); 
      if($ipFlag == chr(2)) { 
        $AddrSeek2 = fread($fd, 3); 
        if(strlen($AddrSeek2) < 3) { 
          fclose($fd); 
          return 'System Error'; 
        } 
        $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0))); 
        fseek($fd, $AddrSeek2); 
      } else { 
        fseek($fd, -1, SEEK_CUR); 
      } 
      while(($char = fread($fd, 1)) != chr(0)) 
      $ipAddr2 .= $char; 
      $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0))); 
      fseek($fd, $AddrSeek); 
      while(($char = fread($fd, 1)) != chr(0)) 
      $ipAddr1 .= $char; 
    } else { 
      fseek($fd, -1, SEEK_CUR); 
      while(($char = fread($fd, 1)) != chr(0)) 
      $ipAddr1 .= $char; 
      $ipFlag = fread($fd, 1); 
      if($ipFlag == chr(2)) { 
        $AddrSeek2 = fread($fd, 3); 
        if(strlen($AddrSeek2) < 3) { 
          fclose($fd); 
          return 'System Error'; 
        } 
        $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0))); 
        fseek($fd, $AddrSeek2); 
      } else { 
        fseek($fd, -1, SEEK_CUR); 
      } 
      while(($char = fread($fd, 1)) != chr(0)){ 
        $ipAddr2 .= $char; 
      } 
    } 
    fclose($fd);  
    if(preg_match('/http/i', $ipAddr2)) { 
      $ipAddr2 = ''; 
    } 
    $ipaddr = "$ipAddr1 $ipAddr2"; 
    $ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr); 
    $ipaddr = preg_replace('/^s*/is', '', $ipaddr); 
    $ipaddr = preg_replace('/s*$/is', '', $ipaddr); 
    if(preg_match('/http/i', $ipaddr) || $ipaddr == '') { 
      $ipaddr = 'Unknown'; 
    } 
    return iconv("gb18030","utf-8","$ipaddr");
  }
//   echo convertip('218.88.169.198');
  if (!function_exists('ip_address_query')) :
	function ip_address_query($comment_text)
	{
		$comment_ID = get_comment_ID();
		$comment = get_comment($comment_ID);
		if ($comment->comment_author_IP && convertip($comment->comment_author_IP)) {
			$comment_text .= '<div class="comment--location"><svg version="1.1" viewBox="0 0 368.666 368.666"  width="14" height="14"><g><path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515
		c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51
		c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355
		c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552
		c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297
		C313.629,178.709,304.004,206.393,285.795,229.355z" /><path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374
		S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374
		s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z" /></g></svg>来自' . convertip($comment->comment_author_IP) . '</div>';
		}
		return $comment_text;
	}
endif;

if (!function_exists('ip_address_query_styles')) :
	function ip_address_query_styles()
	{
		echo "<style>.comment--location {
			display: flex;
			margin-top: 8px;
			align-items: center;
			font-size: 14px!important;
			padding-left: 10px;
			color: rgba(0,0,0,.5)!important;
			fill: rgba(0,0,0,.5)!important;
		}
		.comment--location svg {
			margin-right: 5px;
		}
		</style>";
	}
endif;

add_filter('comment_text', 'ip_address_query');
add_action('wp_head', 'ip_address_query_styles', 100);
?>