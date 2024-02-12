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

while (true) {
    $query = "select users.approve,sandbox.dossiers.* from sandbox.users join sandbox.dossiers on
 sandbox.users.id = sandbox.dossiers.user_id where migrated=2 and sandbox.users.approve=2 and
 sandbox.dossiers.status ='active' limit 1000";

    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $row['message']=str_replace("'","",$row['message']);
            $data = [
                'user_id' => $row['user_id'],
                'audience_id' => $row['audience_id'],
                'message' => $row['message'] ? $row['message']  : null,
                'type' => $row['file'] ? 'dossierFile' : 'text',
                'file' => $row['file'],
                'room_token' => "migrated",
                'seen_audience' => $row['seen_audience'],
                'status' => $row['status'],
                'reply_to' => null,
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
            $insert_query = "INSERT into sandbox.messages (user_id,audience_id,message,messages.type,messages.file,room_token,seen_audience,messages.status,created_at,updated_at)
VALUES (" . $data['user_id'] . "," . $data['audience_id'] . ",'" . $data['message'] . "','" . $data['type'] . "','" . $data['file'] . "','" . $data['room_token'] . "'," . $data['seen_audience'] . ",'"
                . $data['status'] . "','" . $data['created_at'] . "','" . $data['updated_at'] . "')";
            $insert_result = $conn->query($insert_query);
            if ($insert_result === TRUE) {
                $update_query = "UPDATE sandbox.dossiers SET migrated=1 where id=" . $row['id'];
                $conn->query($update_query);
                $counts = $counts + 1;
                system('clear');
                echo $counts;
            } else {
                echo $conn->$insert_query;
                return 1;
                $update_query = "UPDATE sandbox.dossiers SET migrated=2 where id=" . $row['id'];
                $conn->query($update_query);
            }
        }
    } else {
        break;
    }
}

mysqli_close($conn);
