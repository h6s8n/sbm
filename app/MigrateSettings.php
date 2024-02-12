<?php

function toArray(): array
{
    return [
        1=>'ارسال اس ام اس برای ویزیت جدید',
        2=>'ارسال ایمیل برای ویزیت جدید',
        3=>'ارسال اس ام اس لیست انتظار',
        4=>'ارسال ایمیل لیست انتظار',
        5=>'قفل وقت های روز جاری',
        6=>'فقط بیماران خودم',
        7=>'چت تصویری',
        8=>'چت صوتی',
        9=>'چت متنی',
    ];
}

$servername = "localhost";
$username = "sandbox";
$password = "SbmApp@2018";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

$query = "select sandbox.users.* from sandbox.users where approve=1";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        foreach (toArray() as $key => $item) {
            $now = new DateTime();
            $data=[
                'user_id'=>$row['id'],
                'setting_type_id'=>$key,
                'changed_user_id'=>928,
                'subscribed'=> $key != 7,
                'created_at'=>$now->format('Y-m-d H:i:s'),
                'updated_at'=>$now->format('Y-m-d H:i:s')
            ];
            $insert_query = "INSERT into sandbox.user_settings (user_id,setting_type_id,last_changed_user_id,subscribed,created_at,updated_at)
VALUES (" . $data['user_id'] . "," . $data['setting_type_id'] . ",'" .
                $data['changed_user_id'] . "','" . $data['subscribed'] . "','" .
                $data['created_at'] . "','" . $data['updated_at']."')";

            $insert_result = $conn->query($insert_query);
            echo $conn->error;
        }
    }
}

mysqli_close($conn);
