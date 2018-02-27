<?php
	/**
	* 酷狗无损音质下载
	*/
	class KuGou
	{
		# 下载url 加密方式md5(hash +"kgcloudv2")
		private $kugou_v2_pc = 'http://trackercdnbj.kugou.com/i/v2/?cmd=23&pid=1&behavior=download';
		# 搜索url
		private $search_api = 'http://songsearch.kugou.com/song_search_v2';

		public function search($song_name){
			# 搜索音乐资源，将返回的json歌曲信息解析
			$res = json_decode(file_get_contents($this->search_api.'?keyword='.$song_name.'page=1'),true);
			# 判断搜索返回的列表是否为空，不为空则有资源。
			if (!empty($res['data']['lists'])) {
				$Songs = [];
				$SongsCount = 0;
				foreach ($res['data']['lists'] as $value) {
					# SQLFileHash = 32个0说明没有无损音质的资源，排除掉。
					if ($value['SQFileHash'] != '00000000000000000000000000000000') {
						# 通过得到的SQFileHash加密key后去请求单个无损音质音乐的资源信息
						$Song_res = json_decode(file_get_contents($this->kugou_v2_pc.'&hash='.$value['SQFileHash'].'&key='.md5($value['SQFileHash'].'kgcloudv2')),true);
						$Songs[$SongsCount] = [
							'SongName' => $value['SongName'],		//歌曲名称
							'SingerName' => $value['SingerName'],	//歌手名字
							'SongExt' => $Song_res['extName'],		//歌曲后缀
							'SongSize' => round($Song_res['fileSize'] /1024/1024,2).' MB',	//歌曲资源大小
							'SongTime' => ltrim(date('i:s', $Song_res['timeLength']),0),	//歌曲的时长
							'SongUrl' => $Song_res['url']	//下载URL
						];
						$SongsCount++;
					}
				}
				echo json_encode(['status' => true, 'message' => '加载资源成功', 'count' => count($Songs), 'data' => $Songs]);
			}else{
				echo json_encode(['status' => false, 'message' => '找不到相关匹配的资源']);
			}
		}
	}
	$KuGou = new KuGou();
	$KuGou->search($_GET['song_name']);
?>