<?php
    ini_set('display_errors', 1);   // 에러 메시지 출력 On

    // 변수 초기화
    $mode = "select";
    $err = "";

    // Post 파라미터 변수에 할당
    extract($_POST);

    //MySQL  3307포트로 연결
    $mysqli = new mysqli("localhost:3307", "root", "", "201924480");
    if ($mysqli->connect_errno) 
        die('{"err": "DB에 연결할 수 없습니다."}');
    
    if ($mode == "insert")
    {        
        // 중복 검사 수행
        //php .연산자 문자열 연결을 사용
        $sql = "select * from student where studentId='".$studentId."'";
        if ( $result = $mysqli->query($sql)) {
            if ($result->num_rows > 0) {
                echo '{"err": "이미 있는 학번 입니다."}';
            }
            else {
                $sql = "insert into student set studentId = '".$studentId."', name = '".$name."'";
                $mysqli->query($sql);
            }
            $result->close();
        }
    }
    else if ($mode == "delete")
    {                               
        $sql = "delete from student where studentId = '".$studentId."'";
        $mysqli->query($sql);            
    }
    else if ($mode == "update")
    {        
        $sql = "select * from student where studentId='".$studentId."'";
        if ( $result = $mysqli->query($sql)) {
            if ($result->num_rows > 0) {                
                $sql = "update student set name = '".$name."' where studentId = '".$studentId."'";
                $mysqli->query($sql);
            }
            else {
                echo '{"err": "아이디는 변경하실 수 없습니다."}';
            }
            $result->close();
        }


    }
        
    $select_sql = "";
    $data_arr = array();

    $cnt = 0;
    $select_sql = "select * from student order by name asc";
    if ($result = $mysqli->query($select_sql)) {
        while ($data = $result->fetch_assoc()) {
            $data_arr[$cnt] = array("studentId"=> $data['studentId'], "name"=> $data['name']);
            $cnt++;
        }
        $result->close();
    }
        
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Q2</title>
        <style>
            p { margin: 0px; }
            input[type = "text"] { width: 250px; }
            span { font-size: 14px; text-decoration: underline; color: blue;}
            span:hover { cursor: pointer; }
            ul { margin-top: 0; }
            li { list-style-type: none; width: 150px;}
            li:nth-child(even) {background-color: lightblue;}
            #moreBtn {visibility: hidden;}
        </style>
        <script>
            let curShowCount = 5;
            let studentArr = <?php echo json_encode($data_arr);?>;    // php 배열을 javascript 배열에 바로 대입하고 싶을 때는 json을 활용
            
            function makeAlert()    // php에서 생성한 에러 메시지를 경고창으로 띄워주는 함수
            {
                var err = "<?php echo $err;?>";
                if (err.length > 0)
                {
                    window.alert(err);
                    return;
                }
            }
            function loadStudents() 
            {
                let length = studentArr.length                

                let markup = "<div><ul>";
                for (let i = 0; i < curShowCount; i++)
                {
                    if (i >= length)
                        break;
                    markup += "<li><span id = '" + studentArr[i].studentId + "' onclick = 'deleteInfo(" + i + ")'>" 
                        + studentArr[i].name + "</span>" + "(" +
                        "<span id = '" + studentArr[i].studentId + "' onclick = 'editInfo(" + i + ")'>" 
                        + studentArr[i].studentId + "</span>" + ")</li>";
                }
                markup += "</ul></div>";
                document.getElementById("studentBook").innerHTML = markup;

                if (curShowCount < length)
                    document.getElementById("moreBtn").style.visibility = "visible";
                else
                    document.getElementById("moreBtn").style.visibility = "hidden";
            }
            function saveInfo() 
            {
                let studentId = document.getElementById("studentId");
                let name = document.getElementById("name");                
                if(studentId.value == "")
                {
                    window.alert("학번을 입력하세요.");
                }
                else if(name.value == "")
                {
                    window.alert("이름을 입력하세요.");
                }
                else
                {                    
                    if (document.getElementById("mode").value != "update") 
                        document.getElementById("mode").value = "insert";
                    document.forms[0].submit();
                }
            }
            function deleteInfo( index ) 
            {
                if (confirm(studentArr[index].name + " 학생의 정보를 지우시겠습니까?"))
                {                    
                    document.getElementById("studentId").value = studentArr[index].studentId;
                    document.getElementById("mode").value = "delete";
                    document.forms[0].submit();
                }
            }
            function editInfo( index )
            {
                document.getElementById("studentId").value = studentArr[index].studentId;
                document.getElementById("name").value = studentArr[index].name;   
                document.getElementById("mode").value = "update";
                loadStudents();
            }
            function showMoreStudent()
            {
                curShowCount += 5;
                loadStudents();
            }
            function start()
            {
                makeAlert();    // 시작할 때 경고 메시지 먼저 보여줌
                let saveBtn = document.getElementById( "saveBtn" );
                saveBtn.addEventListener( "click", saveInfo, false );
                let moreBtn = document.getElementById( "moreBtn" );
                moreBtn.addEventListener( "click", showMoreStudent, false );
                loadStudents();
            }

            window.addEventListener( "load", start, false );
        </script>
    </head>
    <body>
        <h1>간이 학생명부</h1>
        <form method="POST" action="Q2_base.php">
            <p>학번: <input id = "studentId" name = "studentId" type = "text" placeholder = "학번을 입력하세요"></p>
            <p>이름: <input id = "name" name = "name" type = "text" placeholder = "이름을 쓰세요">
            <input type = "button" value = "Save" id = "saveBtn"></p>
            <!--submit 구분을 위한 hidden input 추가-->
            <input type="hidden" name="mode" id="mode" value="insert"/>
        </form>
        <h1>저장된 학생 정보</h1>
        <div id = "studentBook"></div>
        <input type = "button" value = "더보기" id = "moreBtn"></p>
    </body>
</html>
