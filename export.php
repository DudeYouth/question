<?php
    // require_once 'vendor/autoload.php';
    // use PHPExcel\PHPExcel;
    // use PHPExcel\Writer\Excel2007;
    // use PHPExcel\Writer\Excel5;
    require 'PHPExcel/Classes/PHPExcel.php';
    require 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
    require 'PHPExcel/Classes/PHPExcel/Writer/Excel5.php';
    set_time_limit(0);
    function getImages(){
        try{
            $data = file_get_contents('res.json');
            $arr = json_decode($data,true);
            $ret = json_last_error();
            print_r($ret);
        }catch(Exception $e){
            echo $e;
        }
        
        $reg = "/<img[\s\S]+src=\"(.*?)\".*?>/";
        $type_reg = "/\.[a-zA-Z0-9]+$/";
        foreach($arr as &$v){
            if( preg_match($reg,$v['question'],$res1) ){
                $image_src = $res1[1];
                if( $image_src ){
                    if( preg_match($type_reg,$image_src,$res3) ){
                        $images_name = 'question_'.time().'_'.rand(1,100000);
                        $type = $res3[0];
                        try{
                            $image = file_get_contents($image_src);
                            $v['images'] = $images_name.$type;
                            file_put_contents('./images/'.$images_name.$type,$image);
                        }catch(Exception $e){

                        }

                    }

                }
            }
            $v['images'] = [];
            foreach($v['option'] as $key=>&$value ){
                if( preg_match($reg,$value,$res2) ){
                    $image_src = $res2[1];
                    if( $image_src ){
                        if( preg_match($type_reg,$image_src,$res4) ){
                            $images_name = 'option_'.time().'_'.rand(1,100000);
                            $type = $res4[0];
                            try{
                                $image = file_get_contents($image_src);
                                $v['images'][$key] = $images_name.$type;
                                file_put_contents('./images/'.$images_name.$type,$image);
                            }catch(Exception $e){

                            }

                        }
                    }
                }              
            }

        }
        return $arr;
    }
    function exportExcel2($expTitle,$expCellName,$expTableData,$tablename=''){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称 
        $fileName = $xlsTitle;//$_SESSION['account'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);//列
        $dataNum = count($expTableData);//行
		$objPHPExcel = new \PHPExcel();
        //p([123,$objPHPExcel]);die;
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tablename );
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        for($i=0;$i<$cellNum;$i++){
            if(strpos($expCellName[$i][1],'=') === 0){
                $expCellName[$i][1] = "'".$expCellName[$i][1];
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        }
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
              if( isset($expTableData[$i][$expCellName[$j][0]]) ){
                    if( strpos($expTableData[$i][$expCellName[$j][0]],'=') === 0 ){
                        $expTableData[$i][$expCellName[$j][0]] = "'".$expTableData[$i][$expCellName[$j][0]];
                    }
                    $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
              }

                
                //$objPHPExcel->getActiveSheet()->getStyle($cellName[$j].($i+3))->getAlignment()->setWrapText(true);
          }             
        }  
        for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$j])->setAutoSize(true);
            //$objPHPExcel->getActiveSheet()->getStyle($cellName[$j].($i+3))->getAlignment()->setWrapText(true);
      }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印      
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }
    //平台导出报量结果
    function exp_company(){
        // $data = file_get_contents('result.json');
        // $xlsData = json_decode($data,true);
        $xlsData =  getImages();
        die;
        $xlsName  = "题库表";
        $tablename = "题库表";
        $new_data = [];
        $questions = [];
        $xlsCell  = [
            ['question','题目'],
            ['answer1','选项1'],
            ['answer2','选项2'],
            ['answer3','选项3'],
            ['answer4','选项4'],
            ['type','题目类型'],
            ['level','题目等级'],
            ['answer','答案'],
            ['analysis','解析'],
            ['image1','图1'],
            ['image2','图2'],
            ['image3','图3'],
            ['image4','图4'],
        ];
        foreach( $xlsData as $v){
            if( !in_array($v['question'],$questions) ){
                $questions[] = $v['question'];
                $json = [];
                $json['question'] = $v['question'];
                $json['answer1'] = $v['option'][0];
                $json['answer2'] = $v['option'][1];
                if( isset($v['option'][2]) ){ 
                    $json['answer3'] = $v['option'][2];
                }
                if( isset($v['option'][3]) ){ 
                    $json['answer4'] = $v['option'][3];
                }
                if( isset($v['images'][0]) ){ 
                    $json['image1'] = $v['images'][0];
                }
                if( isset($v['images'][1]) ){ 
                    $json['image2'] = $v['images'][1];
                }
                if( isset($v['images'][2]) ){ 
                    $json['image3'] = $v['images'][2];
                }
                if( isset($v['images'][3]) ){ 
                    $json['image4'] = $v['images'][3];
                }
                $json['answer'] =  $v['answer'];
                $json['analysis'] =  $v['analysis'];
                switch( $v['name'] ){
                    case '语文':
                        $json['type'] = 1;
                        break;
                    case '数学':
                        $json['type'] = 2;
                        break;
                    case '英语':
                        $json['type'] = 3;
                        break;  
                    default:
                        $json['type'] = 1;
                        break;                     
                }
                $level = substr($v['level'],0,3);
                switch( $level ){
                    case '一':
                        $json['level'] = 1;
                        break;
                    case '二':
                        $json['level'] = 2;
                        break;
                    case '三':
                        $json['level'] = 3;
                        break;  
                    case '四':
                        $json['level'] = 4;
                        break; 
                    case '五':
                        $json['level'] = 5;
                        break; 
                    case '六':
                        $json['level'] = 6;
                        break; 
                    default:
                        $json['level'] = 1;
                        break;                     
                }
                $new_data[] = $json;
            }

            
        }
        //$xlsData  = $xlsModel->Field('id,act_id,drug_id,status')->select();
        exportExcel2($xlsName,$xlsCell,$new_data,$tablename);    
    }
    exp_company();