<?php
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
        foreach (\App\Enums\UsersSetting::toArray() as $key => $item) {
            $data=[
                'user_id'=>$row['id'],
                'setting_type_id'=>$key,
                'changed_user_id'=>928,
                'subscribed'=> $key != 7,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now()
            ];
            return  $data;
            return true;
            $insert_query = "INSERT into sandbox.user_settings (user_id,setting_type_id,changed_user_id,subscribed,created_at,updated_at)
VALUES (" . $data['user_id'] . "," . $data['setting_type_id'] . ",'" .
                $data['changed_user_id'] . "','" . $data['subscribed'] . "','" .
                $data['created_at'] . "','" . $data['updated_at']."')";
            $insert_result = $conn->query($insert_query);
        }
    }
}

mysqli_close($conn);
