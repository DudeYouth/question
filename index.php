<?php

class QuestionsCrawler{
    public function __construct(){
        $this ->enter = 'http://www.tiku.cn';
        $this ->getContent();
        $this ->res = [];
        //$this ->get_level_data($this ->enter);
    }
    public function get_level_data($url){
        $text = file_get_contents($url);
        $reg1 = "/<div class=\"subject\">([\s\S]*)<\/div>[\n\s\r]*<div class=\"data\">/"; // 获取题库类型内容主题
        $reg2 = "/<table class=\"inner_table\">([\s\S]*?)<\/table>/"; // 获取每个类型的内容
        if( preg_match($reg1,$text,$res1) ){
            if( !empty($res1[1]) ){
                if( preg_match_all($reg2,$res1[1],$res2)  ){
                    if( !empty($res2[1]) ){
                        foreach( $res[1] as $k=>$v ){
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
        $reg = "/<span>([\u4e00-\u9fa5]+) | <\/span>[\r\n\s]*<a href=\"(\/index\/index\/questions?cid=(\d+)&cno=1)\" target=\"_blank\">试题<\/a>\"/";
        if( preg_match_all($reg,$text,$res) ){
            foreach( $res[2] as $k=>$v ){
                $arr[$res[1][$k]] = $this ->get_question_type($this ->enter.$v);
            }
        }
    }
    // 获取课目下的题目类型
    public function get_question_type($url){
        $arr = [];
        $text = file_get_contents($url);
        $reg1 = "/<ul class=\"ul-ques\">([\s\S]*?)<\/ul>/";
        $reg2 = "/<b onclick=\"tourl\((\d+),'unitid'\)\"[\s\S]+?>([\s\S]+?)<\/b>/";
        if( preg_match($reg1,$text,$res1) ){
            if( empty($res1[1]) ){
                if( preg_match_all($reg2,$res1[1],$res2) ){
                    foreach( $res[1] as $k=>$v ){
                        $arr[] = [
                            'type'=>$res[2][$k],
                            'content'=>$this ->get_all_questions($url.'&unitid='.$v)
                        ];
                    }
                }
            }
        }
        return $arr;
    }
    // 获取课目下的年级类型
    public function get_question_nia_id($text){
        $arr = [];
        $reg1 = "/<div class=\"tp-sub-list\">([\s\S]*?)<\/div>[\s\n\r]*?<\/div>/";
        $reg2 = "/<div class=\"tp-select type-c [\s[0-9a-zA-Z]*?\" id=\"[0-9a-zA-Z]+?\" onclick=\"tourl\((\d+),'bid'\)\">([\u4e00-\u9fa5]+)<\/div>/";
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
        $reg1 = "/<div class=\"tp-sub-list\">([\s\S]*?)<\/div>[\s\n\r]*?<\/div>/";
        $reg2 = "/<div class=\"tp-select type-c [\s[0-9a-zA-Z]*?\" id=\"[0-9a-zA-Z]+?\" onclick=\"tourl\((\d+),'bid'\)\">单选题<\/div>/";
        if( preg_match($reg1,$text,$res1) ){
            if( preg_match($reg2,$res1[1],$res2) ){
                return $res2[1];
            }           
        }
        return false;
    }
    public function get_all_questions($url){
        $arr = [];
        $text = file_get_contents($url);
        $nia_id = $this ->get_question_nia_id($text);
        $radio_id = $this ->get_question_radio_id($text);
        foreach( $nia_id as $v ){
            $arr[] = [
                        'name'=>$v['name'],
                        'content'=>$this ->get_all_page_question( $url.'&bid='.$v['id'] ),
            ];
        }
        return $arr;

    }
    // 获取所有页面的题目数据
    public function get_all_page_question($url){
        $arr = [];
        $text = file_get_contents($url);
        $page = $this ->get_question_page($text);
        for( $i=1;$i<=$page;$i++ ){
            $data = $this ->get_questions($url.'&page='.$i);
            $arr = array_merge($arr,$data);
        }
        return $arr;
    }
    // 获取单页面的所有题目数据
    public function get_questions($url){
        $text = file_get_contents($url);
        $reg1 = "/<div class=\"ques\">>([\s\S]+?)<!--<div class=\"clearfix\"><\/div>-->[\r\n\s]*<\/div>/";  // 获取某道题
        $reg2 = "/<div class=\"q-title\">([\s\S]+?)<\/div>/";  // 获取问题
        $reg3 = "/<div class=\"answer\">[a-zA-Z]{1}、([\s\S]+?)<\/div>/";  // 获取选项
        $arr = [];
        if( preg_match_all($reg1,$text,$res1) ){
            foreach( $res1[1] as $v1 ){
                if( preg_match($reg2,$v1,$res2) ){
                    $question = $res2[1];
                    $answer = [];
                    if( preg_match_all($reg3,$title,$res3) ){
                        foreach( $res3[1] as $v2 ){
                            $answer[] = $v2;
                        }
                    }
                }
                $arr[] = ['question'=>$question,'answer'=>$answer];
            }

        }
        return $arr;
    }
    // 获取某个类型单选题的分页数量
    public function get_question_page($text){
        $reg1 = "/<ul class=\"pagination\">([\s\S]+?)<\/ul>/";
        $reg2 = "/<a href=[\s\S]+?>(\d+?)<\/a>[\r\n\s]*<a href=[\s\S]+?>»<\/a>/";
        if( preg_match($reg1,$text,$res1) ){
            if( preg_match($reg2,$res1[1],$res2) ){
                return $res2[1];
            }
        }
        return false;
    }
}

new QuestionsCrawler();