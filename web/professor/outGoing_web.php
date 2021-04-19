<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// DATABASE 연결
$username = "root";
$password = "pringles";
$hostname = "localhost";
$db_name  = "finger_print";
$conn   = mysqli_connect($hostname, $username, $password, $db_name);

// 연결 오류 발생시 error문 출력
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL" . $conn->connect_error;
} else {
    // Handling JSON POST request
    // Takes raw data from the request
    // Converts it into a PHP object
    header("Content-Type: application/json; charset=UTF-8");
    $json = file_get_contents('php://input'); // JSON 포맷의 Data를 넘겨받는다.
    $decoded_json = json_decode($json, true);   // JSON 포맷을 Parsing 한다.

    // decoded 된 json 데이터에서 사용자의 id(input_user_id)와 pw(input_user_pw)를 가져온다.
    $userDevice = $decoded_json['userDevice'];      // email    - 학번
    $reason = $decoded_json['reason'];

    $timestamp  = strtotime("+1 days");
    $today    = date("y-m-d");
    $tomorrow   = date("y-m-d", $timestamp);

    $usersQuery = mysqli_query($conn, "SELECT * FROM student_inf");
    
    $students = array();

    while($datas = mysqli_fetch_assoc($usersQuery)) {
        
        $userQuery = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = $datas[std_num] AND created BETWEEN '$today' AND '$tomorrow'");    
        $userQueryResult = mysqli_fetch_assoc($userQuery);
        
        $outGoingQuery = mysqli_query($conn, "SELECT * FROM outgo_inf WHERE idx_attendance = $userQueryResult[idx] ORDER BY idx DESC limit 1");
        $outGoingResult = mysqli_fetch_assoc($outGoingQuery);

        $outGoingDatasQuery = mysqli_query($conn, "SELECT * FROM outgo_inf WHERE idx_attendance = $userQueryResult[idx]");
        
        
        
        $outGoingDataArray = array();
        while ($data = mysqli_fetch_assoc($outGoingDatasQuery)) {
            array_push($outGoingDataArray, array(
                "in_time" => $data['in_time'],
                "out_time" => $data['out_time'],
		"outgoing_time" => $data['outgoing_time'],
                "reason" => $data['reason']
            ));
        }

        array_push($students, array(
            "std_name" => $datas['std_name'],
            "in_time" => $userQueryResult['in_time'],
            "out_time" => $userQueryResult['out_time'],
            "out_list" => $outGoingDataArray
        ));
    }
    
    // 데이터 전송
    $json = json_encode(["students"=>$students]);
    print_r($json);
}
