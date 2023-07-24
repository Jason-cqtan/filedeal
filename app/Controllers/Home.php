<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function parseFile() {
        $file = $this->request->getFile('file');
        $MimeType = $file->getMimeType();
        //echo $MimeType;exit;
        if(!in_array($MimeType,['text/plain','text/xml','application/x-wine-extension-ini'])) {
            die(json_encode([
                'code' => 1,
                'msg' => '不支持的文件类型'
            ]));
        }

        $temp = $file->getPath() . "/". $file->getFilename();
        if($MimeType == 'text/xml' || $MimeType == 'text/plain') {
            $type = 1;
            // 直接返回xml格式
            $content = file_get_contents($file);
            //$content = $this->parseXml($temp);
        } else {
            $type = 2;
            $content = $this->parseIni($temp);
        }

        die(json_encode([
            'code' => 0,
            'type' => $type,
            'msg' => '',
            'data' => $content
        ]));
    }


    protected function parseXml($file) {
        //$this->xmlToJson($file);
        // 创建一个新的 DOMDocument 对象
        // 读取 XML 文件内容
        $xmlString = file_get_contents($file);
        //echo $xmlString;exit;
        // 将 XML 字符串加载为 SimpleXML 对象
        $xml = simplexml_load_string($xmlString);

        // 将 SimpleXML 对象转换为关联数组，并添加根节点键名
        $arrayData = array($xml->getName() => json_decode(json_encode($xml), true));

        // 将关联数组转换为 JSON 格式
        return $arrayData;
        return json_encode($arrayData, JSON_PRETTY_PRINT);
    }

    protected function parseIni($file) {
        return $this->parseCustomIniFile($file);
    }

    function parseCustomIniFile($filePath)
    {
        $iniContent = file_get_contents($filePath);
        $lines = explode("\n", $iniContent);

        $parsedData = [];
        $currentSection = null;
        $headers = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // 跳过空行和注释行
            if (empty($line) || $line[0] === ';' || $line[0] === '#') {
                continue;
            }

            // 解析段落
            if (preg_match('/^\[([^\]]+)\]$/', $line, $matches)) {
                $currentSection = $matches[1];
                $parsedData[$currentSection] = [];
                continue;
            }

            // 解析表格数据
            if ($currentSection !== null) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    $parsedData[$currentSection][$key] = $value;
                } else {
                    $rowData = preg_split('/\s+(?=(?:[^"]*"[^"]*")*[^"]*$)/', $line, -1, PREG_SPLIT_NO_EMPTY);
                    if(!isset($headers[$currentSection])) {
                        $headers[$currentSection] = $rowData;
                        $parsedData[$currentSection][] = $rowData;
                    } else {
                        // 存在数据，把空表头去掉
                        if(count($parsedData[$currentSection]) == 1 && isset($parsedData[$currentSection][0][0])) {
                            $parsedData[$currentSection] = [];
                        }

                        // 使用表头的每一项作为表体每项数据的key，形成关联数组
                        $rowAssocData = array_combine($headers[$currentSection], $rowData);
                        $parsedData[$currentSection][] = $rowAssocData;
                    }
                }

            }
        }

        return $parsedData;
    }


    public function saveFile() {
        helper('filesystem');
        // todo 将修改后的文件保存为文件
        $type = $this->request->getPost('type');
        $data = $this->request->getPost('data');
        if($type == 1) { // 已废弃，直接使用js下载
            // xml
            $filePath = "files/" . time() . '.xml';
            if(!write_file($filePath, preg_replace('/\>\s+\</m', '><', urldecode($data)))){
                die(json_encode([
                    'code' => 1,
                    'path' => ''
                ]));
            } else {
                die(json_encode([
                    'code' => 0,
                    'path' => base_url() . $filePath
                ]));
            }
        } else {
            $arr = json_decode($data, true);
            $iniData = '';
            //[OptimizeOptionsPerBeam] => Array
            //        (
            //            [0] => Array
            //                (
            //                    [IDNUM] => 1
            //                    [Head] => 1
            //                    [OptOptions] => "0000000"
            //                    [CarryoverDiffNum] => 0
            //                    [OnlineSetupFeederNum] => 0
            //                )
            //
            //        )
            foreach ($arr as $section => $data) {
                $iniData .= "[$section]\n";
                // 三种数据结构
                if(isset($data[0])) {
                    if(isset($data[0][0])) {// 纯表头
                        $iniData .= join(" ", $data[0]);
                        $iniData .= "\n";
                    } else {
                        // 关联数组，先表头再列表值
                        $headers = array_keys($data[0]);
                        $iniData .= join(" ", $headers);
                        $iniData .= "\n";
                        foreach ($data as $index => $val) {
                            $items = array_values($val);
                            $iniData .= join(" ", $items);
                            if($index + 1 < count($data)) $iniData .= "\n";
                        }
                        $iniData .= "\n";
                    }
                } else {
                    foreach ($data as $key => $value) {
                        $iniData .= "$key = $value\n";
                    }
                }

                $iniData .= "\n";
            }

            $filename = $this->request->getPost('filename');

            $filePath = "files/" . $filename. time();
            if(!write_file($filePath, $iniData)){
                die(json_encode([
                    'code' => 1,
                    'path' => ''
                ]));
            } else {
                die(json_encode([
                    'code' => 0,
                    'path' => $filePath
                ]));
            }
        }
    }

    public function download() {
        $filename = $this->request->getGet('filename');
        $path = $this->request->getGet('path');
        // 设置响应头，告诉浏览器将要下载一个文件
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Type: text/plain');

        // 创建 INI 格式的内容
        $iniContent = file_get_contents($path);


        // 输出 INI 内容
        echo $iniContent;
        exit;
    }
}
