<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>文件处理</title>
    <meta name="description" content="The small framework with powerful features">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- STYLES -->

    <style {csp-style-nonce}>
        * {
            transition: background-color 300ms ease, color 300ms ease;
        }
        *:focus {
            background-color: rgba(221, 72, 20, .2);
            outline: none;
        }
        body {
            margin: 0;
            padding: 0;
            height: 100vh; /* 设置body的高度为视口高度，确保内容不会超出屏幕 */
            display: flex;
            flex-direction: column;
        }

        header {
            /* 设置顶部(header)样式 */
            height: 70px; /* 设置顶部高度，可以根据实际需要调整 */
            background-color: #f0f0f0;
            position: fixed; /* 固定顶部位置 */
            top: 0; /* 将顶部定位在页面顶部 */
            width: 100%;
            z-index: 999; /* 设置顶部的堆叠顺序，确保它在其他内容之上 */
        }
        header div{
            padding: 5px;
        }

        footer {
            /* 设置底部(footer)样式 */
            height: 40px; /* 设置底部高度，可以根据实际需要调整 */
            line-height: 40px;
            background-color: #f0f0f0;
            position: fixed; /* 固定底部位置 */
            bottom: 0; /* 将底部定位在页面底部 */
            width: 100%;
            text-align: center;
        }

        .content {
            /* 设置中间内容样式 */
            flex: 1; /* 这将使.content元素占据剩余的可用空间，允许中间内容滚动 */
            overflow-y: auto; /* 允许中间内容垂直滚动 */
            padding: 70px 0 40px 0;
        }
        #selectList {
            padding-left: 10px;
        }
        #selectList li{
            display: inline-block;
            cursor: pointer;
            border: 1px solid;
            padding: 1px ;
        }
        .hide {
            display: none;
        }
        .table-striped>tbody>tr{
            height: 10px !important;
        }
    </style>
</head>
<body>
<header>
    <div>
        <div style="width: 20%;display: inline-block">
            <form action="index.php/parseFile" id="formid" method="post" class="form-inline">
                <label for="fileInput">模板文件</label>
                <input name="file" type="file" id="fileInput">
            </form>

        </div>
        <div style="width: 20%;display: inline-block">
            <form action="#" class="form-inline">
                <label for="excelFileInput">数据源excel（注意：修改数量与模板中数量保持一致）</label>
                <input type="file" id="excelFileInput" value="选择excel"/>
            </form>
        </div>
        <div style="display:inline-block;padding-left: 10px;width: 30%">
            <form action="#" class="form-inline">
                <label for="selectList">待修改节点</label>
                <select name="" id="selectList" style="display: inline-block" class="hide"></select>
            </form>
        </div>
        <div style="display:inline-block;padding-left: 10px;">
            <button id="btn">下载</button>
        </div>
    </div>
</header>
<div class="content">
    <!-- 网页主要内容 -->
    <pre id="txtEditor" style="width: 100%; height: 100%;" class="hide" contenteditable="true"></pre>
    <table class="table table-striped" style="width: 100%; height: 100%;" class="hide">
        <thead>
<!--            <tr>-->
<!--                <th>Month</th>-->
<!--            </tr>-->
        </thead>
        <tbody>
<!--            <tr>-->
<!--                <td>January</td>-->
<!--            </tr>-->
        </tbody>
    </table>
    <form class="form-horizontal" id="objform">
<!--        <div class="form-group">-->
<!--            <label for="inputEmail3" class="col-sm-2 control-label"></label>-->
<!--            <div class="col-sm-10">-->
<!--                <input type="text" class="form-control" id="inputEmail3">-->
<!--            </div>-->
<!--        </div>-->
    </form>
</div>

<footer>
    <div class="copyrights">
        <p>&copy; <?= date('Y') ?> 丘华科技</p>
    </div>
</footer>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="<?php echo base_url('/layer.js')?>"></script>
<script>

    // '清空'文件域
    function clearFileInput(fileid) {
        // 获取文件选择域元素
        const fileInput = document.getElementById(fileid);

        // 创建一个新的文件选择域，并替换现有的文件选择域
        const newFileInput = document.createElement('input');
        newFileInput.type = 'file';
        newFileInput.id = fileid;
        //newFileInput.addEventListener('change', handleFile, false);

        // 将新的文件选择域插入到现有文件选择域的父元素中，并移除现有文件选择域
        fileInput.parentNode.insertBefore(newFileInput, fileInput);
        fileInput.parentNode.removeChild(fileInput);
    }
    // 格式化XML字符串
    function formatXml(xmlString) {
        const PADDING = "    "; // 每级缩进4个空格
        let formatted = "";
        let indent = 0;

        // 使用正则表达式匹配XML标签
        const reg = /(>)(<)(\/*)/g;
        xmlString = xmlString.replace(reg, "$1\r\n$2$3");

        // 拆分为每一行
        const lines = xmlString.split("\r\n");

        // 调整缩进
        lines.forEach(line => {
            let padding = 0;

            if (line.match(/.+<\/\w[^>]*>$/)) {
                padding = 0;
            } else if (line.match(/^<\/\w/)) {
                if (indent != 0) {
                    indent -= 1;
                }
            } else if (line.match(/^<\w[^>]*[^\/]>.*$/)) {
                padding = 1;
            } else {
                padding = 0;
            }

            formatted += PADDING.repeat(indent) + line + "\r\n";
            indent += padding;
        });

        return formatted;
    }

    function resetFileInput(fielid) {
        const fileInput = document.getElementById(fielid);
        fileInput.value = null;
    }

    $(document).ready(function() {
        var headers = []
        var result = {}// ini数据
        var xmldata = ''; // xml数据
        var fileType = 1;
        var currentArr = []
        var currentKey = ''
        // 监听文件输入字段的 change 事件
        $("#formid").on("change", function() {
            var fileInput = $(this);
            //console.log(fileInput);
            // 获取文件
            var file = fileInput[0][0].files[0];
            // 如果有文件选择
            if (file) {
                // 清空源文件excel
                clearFileInput('excelFileInput')
                // 创建 FormData 对象并添加文件
                var formData = new FormData();
                formData.append("file", file);
                var index = layer.load(2);
                // 发起 AJAX 请求
                $.ajax({
                    url: $(this).attr("action"),
                    type: $(this).attr("method"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        layer.close(index);
                        response = JSON.parse(response)
                        if(response.code == 0) {
                            // 获取所有修改项
                            fileType = response.type
                            if(response.type == 2) {
                                for (k in response.data) {
                                    headers.push(k)
                                }

                                // 渲染修改项
                                var str = '';
                                $.each(headers,function(i,v){
                                    str += '<option>'+v+'</option>';
                                })
                                $('#selectList').html(str)
                                $('#selectList').select2({
                                    placeholder: '请选择',
                                    width: '400'
                                });
                                $('#selectList').removeClass('hide')
                                $('#txtEditor').addClass('hide')
                            } else {
                                xmldata = response.data
                                $('#txtEditor').removeClass('hide')
                                $('table').addClass('hide')
                                $('#objform').addClass('hide')
                                $("#selectList").next().css("display", "none");

                                // 格式化XML字符串并显示
                                const formattedXml = formatXml(response.data);
                                const formattedXmlElement = document.getElementById("txtEditor");
                                formattedXmlElement.textContent = formattedXml;
                                //$('#txtEditor').html(formattedXml)
                            }
                            // 结果数据
                            result = response.data
                        } else {
                            layer.alert(response.msg, {icon: 7});
                        }
                    },
                    error: function(xhr, status, error) {
                        layer.close(index);
                        // 处理错误
                        console.error("提交出错:", error);
                        layer.alert(error)
                    }
                });

                // 清空文件输入字段的值
                fileInput.val("");
            }
        });


        // 监听文件选择器的变化
        $('body').on('change','#excelFileInput',handleFile)
        //document.getElementById('excelFileInput').addEventListener('change', handleFile, false);

        // excel 文件上回调
        function handleFile(event) {
            if((fileType == 1 && !xmldata) || (fileType == 2 && !result)) {
                layer.alert('请先选择模板文件!')
                return false
            }

            if(fileType == 2 && result && !currentArr) {
                layer.alert('请先选择待修改节点!')
                return false
            }

            const file = event.target.files[0];
            // 获取文件的 MIME 类型
            const mimeType = file.type;
            if($.inArray(mimeType,['application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) == -1) {
                layer.alert('请选择excel文件!')
                return false
            }

            const reader = new FileReader();
            var index = layer.load(2);
            reader.onload = function (e) {
                const data = e.target.result;
                const workbook = XLSX.read(data, {type: 'binary'});

                // 假设你的 Excel 文件只有一个 sheet，如果有多个 sheet 需要根据需要进行处理
                const sheetName = workbook.SheetNames[0];
                const sheetData = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName]);

                if(fileType == 1) {
                    // 假设你的 XML 字符串保存在变量 xmlString 中
                    const xmlString = xmldata;

                    console.log(xmlString)
                    // 创建 DOMParser 对象并解析 XML 字符串为 DOM
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(xmlString, 'text/xml');

                    // 获取 name 标签的节点列表
                    const nameNodes = xmlDoc.getElementsByTagName('Name');
                    const DescriptionNodes = xmlDoc.getElementsByTagName('Description');

                    // 假设你要替换的值为 "New Name"
                    for (let i = 0;i < sheetData.length;i++) {
                        if(!nameNodes[i]) break;
                        nameNodes[i].textContent = sheetData[i]['Name'];
                        DescriptionNodes[i].textContent = sheetData[i]['Description'];
                    }
                    // 将修改后的 DOM 对象转换回 XML 字符串
                    xmldata = new XMLSerializer().serializeToString(xmlDoc);
                    $('#txtEditor').removeClass('hide')
                    $('table').addClass('hide')
                    $('#objform').addClass('hide')
                    $("#selectList").next().css("display", "none");

                    // 格式化XML字符串并显示
                    const formattedXml = formatXml(xmldata);
                    const formattedXmlElement = document.getElementById("txtEditor");
                    formattedXmlElement.textContent = formattedXml;
                } else {
                    $('table').find('tbody').html('')
                    // 处理ini格式文件
                    // 当前选择节点，使用
                    for (let i = 0;i < sheetData.length;i++) {
                        if(!result[currentKey][i]) break;
                        result[currentKey][i]['NAME'] = '"' + sheetData[i]['NAME'] + '"';
                        result[currentKey][i]['C'] = '"' + sheetData[i]['C'] + '"';
                    }
                    // 重新渲染
                    var tbody = ''
                    $.each(result[currentKey],function(i,v) {
                        tbody += '<tr>'
                        $.each(v,function (k,v2){
                            tbody += '<td contenteditable="true" class="editable" index="'+i+'" key="'+k+'" parent="'+currentKey+'">'+v2+'</td>'
                        })
                        tbody += '</tr>'
                    })

                    //console.log(tbody)
                    $('table').find('tbody').html(tbody)
                }

                layer.close(index);
            };

            reader.readAsBinaryString(file);
        }

        // 节点选择
        $('#selectList').on('select2:select', function (e) {
            var index = layer.load(2);
            //console.log(result)
            var key = e.params.data.text;
            var arr = result[key]
            currentArr = arr
            currentKey = key
            // 清空源文件excel
            clearFileInput('excelFileInput')
            if(isArray(arr)) {
                $('table').removeClass('hide')
                $('#objform').addClass('hide')
                if(isTwoDimensionalArray(arr)) {
                    //只是表头
                    var th = '<tr>'
                    $.each(arr[0],function(i,v) {
                        th += '<th>'+v+'</th>'
                    })
                    th += '</tr>'
                    console.log(th)
                    $('table').find('thead').html(th)
                    $('table').find('tbody').html('')
                } else {
                    // 表头表体
                    var th = ''
                    $.each(arr[0],function(i,v) {
                        th += '<th>'+i+'</th>'
                    })
                    th += '</tr>'
                    //console.log(th)
                    $('table').find('thead').html(th)

                    var tbody = ''
                    $.each(arr,function(i,v) {
                        tbody += '<tr>'
                        $.each(v,function (k,v2){
                            tbody += '<td contenteditable="true" class="editable" index="'+i+'" key="'+k+'" parent="'+key+'">'+v2+'</td>'
                        })
                        tbody += '</tr>'
                    })

                    //console.log(tbody)
                    $('table').find('tbody').html(tbody)
                }
            } else {
                $('table').addClass('hide')
                $('#objform').removeClass('hide')
                // 对象
                var objfromstr = ''
                $.each(arr,function(k,v){
                    objfromstr += '<div class="form-group">'
                    objfromstr += '<label class="col-sm-2 control-label">' +k+ '</label>'
                    objfromstr += '<div class="col-sm-10">'
                    objfromstr += '<input parent="'+key+'"  key="'+k+'" type="text" style="width: 40%" class="form-control" value="'+v+'">'
                    objfromstr += '</div></div>'
                })
                $('#objform').html(objfromstr)
            }
            layer.close(index);
        });

        // 点击下载
        $('#btn').on('click',function(){
            var index = layer.load(2);
            if(fileType == 1) {
                if(xmldata) {
                    const blob = new Blob([document.getElementById("txtEditor").textContent.replace(/>\s+</g, "><")], { type: "text/xml" });
                    // 创建虚拟链接
                    const a = document.createElement("a");
                    a.href = URL.createObjectURL(blob);
                    a.download = showFileName();// 选择的文件名

                    // 触发虚拟链接的点击事件
                    document.body.appendChild(a);
                    a.click();
                    // 清理虚拟链接
                    document.body.removeChild(a);
                }else {
                    layer.alert('无需要下载的数据', {icon: 7});
                }
                layer.close(index);
            } else {
                if(result) {
                    // 直接使用result进行上传json后下载成ini格式
                    $.ajax({
                        type: "POST",
                        url: "index.php/saveFile",
                        dataType: "json",
                        data: {
                            type: 2,
                            data: JSON.stringify(result),
                            filename: showFileName()
                        },
                        success: function(res){
                            if(res.code == 0) {
                                window.open("<?php echo base_url('index.php/download')?>" + "?filename="+showFileName()+"&path="+res.path, "_blank");
                            } else {
                                layer.alert('下载出错，请重试', {icon: 7});
                            }
                        }
                    });
                } else {
                    layer.alert('无需要下载的数据', {icon: 7});
                }
                layer.close(index);
            }
        })

        // 表格数据变更
        $('table tbody').on("keyup", "tr td.editable",function() {
            var key = $(this).attr('parent')
            var index = $(this).attr('index')
            var item = $(this).attr('key')
            //console.log(result)
            result[key][index][item] = $(this).html();
            //console.log(result)
        });

        // 表单数据变更
        $('#objform').on('keyup','input.form-control',function(){
            var key = $(this).attr('parent')
            var item = $(this).attr('key')
            //console.log(result)
            result[key][item] = $(this).val();
            //console.log(result)
        })
    });
    // 文件名
    function showFileName() {
        const fileInput = document.getElementById("fileInput");
        const selectedFiles = fileInput.files;

        if (selectedFiles.length > 0) {
            const fileName = selectedFiles[0].name;
            return fileName;
            console.log("选择的文件名：", fileName);
        } else {
            console.log("没有选择文件。");
            return '';
        }
    }
    function isArray(obj) {
        return Array.isArray(obj);
    }
    function isTwoDimensionalArray(obj) {
        if (Array.isArray(obj)) {
            return obj.every(item => Array.isArray(item));
        }
        return false;
    }
    function isObject(obj) {
        return typeof obj === "object" && !Array.isArray(obj);
    }

</script>
</body>
</html>
