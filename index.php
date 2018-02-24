<?php

function request($url,$type='get',$param=[]){
	$ch = curl_init(); 
	// 返回结果存放在变量中，不输出 
	curl_setopt($ch, CURLOPT_URL, $url);
	if( $type=='post' ){
		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
		$headers_login = [
			"User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.18 Safari/537.36",
			"Host:www.tiku.cn",
			"Origin:http://www.tiku.cn",
			"X-Requested-With:XMLHttpRequest",
			"Accept:application/json, text/javascript, */*; q=0.01",
			"Cookie:PHPSESSID=1p6fpkemmn3isc5kltr3uk2suu; Hm_lvt_02f32149c7ea90d0cd47ed89025e457c=1518074988,1518315926,1519368479,1519374574; token=ea8d1a2b2e637c665e6640b8bc4619cc; userId=10678; Hm_lpvt_02f32149c7ea90d0cd47ed89025e457c=".time(),
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login); 
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	//curl_setopt($ch, CURLOPT_COOKIEFILE, 'PHPSESSID=sjfask4v7cmtlkqe65vqvkhnot; Hm_lvt_02f32149c7ea90d0cd47ed89025e457c=1517987272,1518074988,1518315926,1519368479; token=ea8d1a2b2e637c665e6640b8bc4619cc; userId=10678; Hm_lpvt_02f32149c7ea90d0cd47ed89025e457c=1519372429');
	//curl_setopt($ch, CURLOPT_HEADER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); 
	//curl_setopt($ch,CURLOPT_COOKIEJAR,'./cookie.txt'); 
	//curl_setopt($ch,CURLOPT_COOKIEFILE,'./cookie.txt'); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$result= curl_exec($ch);
	curl_close($ch);
    file_put_contents('request.log',$url."\r\n",FILE_APPEND);
    return $result;
}

class QuestionsCrawler{
    public function __construct(){
        $this ->enter = 'http://www.tiku.cn';
		//$reg = "/<span class=\"view-err\" onclick=\"show_anaylis\(\\$\(this\),(\d+)\)\"/";  // 获取答案id
		//$content = file_get_contents('test1.html');
		//preg_match($reg,$content,$res);
		//echo json_encode([$res,$content]);die;
		
        // $this ->getContent();
        $this ->res = [];
        $this ->sub_name = '';  // 科目名
        $this ->sub_type = '';  // 题目类型
        $this ->sub_level = ''; // 题目等级
		//request('http://www.tiku.cn/api/question/anylysis/','post',['id'=>3025439]);
        //$this ->get_question_type();
        //$this ->get_questions($text);
		$this ->answer = ['A'=>1,'B'=>2,'C'=>3,'D'=>4,'a'=>1,'b'=>2,'c'=>3,'d'=>4];
        $this ->get_level_type_data('');
        // $text = request('test.html');
        // $this ->get_question_radio_id($text);
    }
    public function get_level_data($url){
        $text = request($url);
        $reg1 = "/<div class=\"subject\">([\s\S]*)<\/div>[\n\s\r]*<div class=\"data\">/"; // 获取题库类型内容主题
        $reg2 = "/<table class=\"inner_table\">([\s\S]*?)<\/table>/"; // 获取每个类型的内容
        if( preg_match($reg1,$text,$res1) ){
            if( !empty($res1[1]) ){
                if( preg_match_all($reg2,$res1[1],$res2)  ){
                    if( !empty($res2[1]) ){
                        foreach( $res2[1] as $k=>$v ){
                            $this ->res[] = $this ->get_level_type_data($v);
                        }
                    }
                    // echo json_encode($res2[1]);
                    // file_put_contents('test.html','<html><head><meta charset="utf-8 />"</head><body>'.$res2[1].'</body><html>');
                }
            }
            
        }

    }
    // 获取中、小学的所有课目数据
    public function get_level_type_data($text){
        $arr = [];
        $reg = "/<span>([\x80-\xff]{1,}) \| <\/span>[\n\s\r]*<span><a href=\"(\/index\/index\/questions\?cid=(\d+)&cno=1)\"[\n\s\r]*?target=\"_blank\">试题<\/a>/";
        //file_put_contents('test.html','<html><head><meta charset="utf-8 />"</head><body>'.$text.'</body><html>');
        // if( preg_match_all($reg,$text,$res) ){
            //echo json_encode($res);
            //file_put_contents('test.html','<html><head><meta charset="utf-8 />"</head><body>'.$res[1][0].'</body><html>');
        //     foreach( $res[2] as $k=>$v ){
        //         $arr[$res[3][$k]] = [
        //             'name'=>$v,
        //             'content'=>$this ->get_question_type($this ->enter.$v)
        //         ];
        //     }
        // }
        $types = [['/index/index/questions?cid=14&cno=1','数学'],['/index/index/questions?cid=21&cno=1','语文'],['/index/index/questions?cid=23&cno=1','英语']];
            foreach( $types as $k=>$v ){
                $this ->sub_name = $v[1];
                $this ->get_question_type($this ->enter.$v[0]);
                // $arr[$k] = [
                //     'name'=>$v[1],
                //     'content'=>$this ->get_question_type($this ->enter.$v[0])
                // ];
            }
    }
    // 获取课目下的题目类型
    public function get_question_type($url=''){
        $arr = [];
        $text = request($url);
        $reg1 = "/<ul class=\"ul-ques\">([\s\S]*?)<\/ul>/";
        $reg2 = "/<b onclick=\"tourl\((\d+),'unitid'\)\"[\s\S]+?>([\s\S]+?)<\/b>/";
        if( preg_match($reg1,$text,$res1) ){
            if( !empty($res1[1]) ){
                if( preg_match_all($reg2,$res1[1],$res2) ){
                    foreach( $res2[1] as $k=>$v ){
                        $this ->sub_type = $res2[2][$k];
                        $this ->get_all_questions($url.'&unitid='.$v);
                        // $arr[] = [
                        //     'type'=>$res2[2][$k],
                        //     'content'=>$this ->get_all_questions($url.'&unitid='.$v)
                        // ];
                    }
                }
            }
        }
        return $arr;
    }
    // 获取课目下的年级类型
    public function get_question_nia_id($text){
        $arr = [];
        $reg1 = "/<div class=\"nav-select-list\" id=\"book\">([\s\S]*?)<\/div>[\s\n\r]*?<\/div>[\s\n\r]*?<\/div>/";
        $reg2 = "/<div class=\"tp-select type-c [\s\S]+?onclick=\"tourl\((\d+),'bid'\)\">([\x80-\xff]{1,})/";
        if( preg_match($reg1,$text,$res1) ){
            if( preg_match_all($reg2,$res1[1],$res2) ){
                foreach( $res2[1] as $k=>$v ){
                    $arr[] = [
                        'name'=>$res2[2][$k],
                        'id'=>$v,
                    ];
                }
            }
        }
        return $arr;
    }
    // 获取单选的id
    public function get_question_radio_id($text){
        $reg2 = "/<div class=\"tp-select type-d [\s[0-9a-zA-Z]*?\" id=\"[0-9a-zA-Z]+?\" onclick=\"tourl\((\d+),'typeid'\)\">单选题<\/div>/";
        if( preg_match($reg2,$text,$res2) ){
            return $res2[1];
        }           
        return false;
    }
    public function get_all_questions($url){
        $arr = [];
        $text = request($url);
        $nia_id = $this ->get_question_nia_id($text);
        $radio_id = $this ->get_question_radio_id($text);
        echo $radio_id;
        if( $radio_id ){
            foreach( $nia_id as $v ){
                $this ->sub_level = $v['name'];
                $this ->get_all_page_question( $url.'&bid='.$v['id'].'&typeid='.$radio_id );
                // $arr[] = [
                //             'name'=>$v['name'],
                //             'content'=>$this ->get_all_page_question( $url.'&bid='.$v['id'].'&typeid='.$radio_id ),
                // ];
            }
        }
        return $arr;

    }
    // 获取所有页面的题目数据
    public function get_all_page_question($url=''){
        $arr = [];
        $text = request($url);
        $page = $this ->get_question_page($text);
        echo $page;
        for( $i=1;$i<=$page;$i++ ){
            $data = $this ->get_questions($url.'&page='.$i);
            $arr = array_merge($arr,$data);
        }
        return $arr;
    }
    // 获取单页面的所有题目数据
    public function get_questions($url){
        sleep(1);
        $text = request($url);
        $reg1 = "/<div class=\"ques\">([\s\S]+?)<!--<div class=\"clearfix\"><\/div>-->[\r\n\s]*<\/div>/";  // 获取某道题
        $reg2 = "/<div class=\"q-title\">([\s\S]+?)<br>/";  // 获取问题
        $reg3 = "/<div class=\"answer\">[a-zA-Z]{1}、([\s\S]+?)<\/div>/";  // 获取选项
		$reg4 = "/<span class=\"view-err\" onclick=\"show_anaylis\(\\$\(this\),(\d+)\)\"/";  // 获取答案id
        $arr = [];
		$answer = '';
		$analysis = '';
        if( preg_match_all($reg1,$text,$res1) ){
            foreach( $res1[1] as $v1 ){
                if( preg_match($reg2,$v1,$res2) ){
                    $question = $res2[1];
                    $option = [];
                    if( preg_match_all($reg3,$v1,$res3) ){
                        foreach( $res3[1] as $v2 ){
                            $option[] = trim($v2);
                        }
                    }
					$answer = '';
					$analysis = '';
					//preg_match($reg4,$v1,$res4);
					if( preg_match($reg4,$v1,$res4) ){
						echo $res4[1];
						$result = request('http://www.tiku.cn/api/question/anylysis/','post',['id'=>$res4[1]]);
						$result = json_decode($result,true);
						if( $result['msg']=='success' ){
							$answer = $this ->answer[$result['data']['answer']];
							$analysis = $result['data']['anylysisHtml'];			
						}
					}
                }
                if( !empty($option) ){
                    $res = ['question'=>trim($question),'option'=>$option,'answer'=>$answer,'analysis'=>$analysis,'type'=>$this ->sub_type,'level'=>$this ->sub_level,'name'=>$this ->sub_name];
                    $arr[] = $res;
                    file_put_contents('result.json',json_encode($res,JSON_UNESCAPED_UNICODE).',',FILE_APPEND);
                }

            }
            
        }
        return $arr;
    }
    // 获取某个类型单选题的分页数量
    public function get_question_page($text){
        $reg1 = "/<ul class=\"pagination\">([\s\S]+?)<\/ul>/";
        $reg2 = "/<a href=[\s\S]+?>(\d+?)<\/a><\/li>/";
        if( preg_match($reg1,$text,$res1) ){
            if( preg_match_all($reg2,$res1[1],$res2) ){
                $len = count( $res2[1] );
                if( $len ){
                    return $res2[1][$len-1];
                }
                
            }
        }
        return false;
    }
}

new QuestionsCrawler();