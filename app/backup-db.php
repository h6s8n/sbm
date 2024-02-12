<?php
// اتصال به دیتابیس
$host = '185.18.213.18';
$db_name = 'sandbox';
$username = 'amirtorabi';
$password = 'Amir223311@@';

$conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);



$table_name = 'users';

$backup_file = 'backup.sql';

$query = "SELECT * INTO OUTFILE '$backup_file' FROM $table_name";
$stmt = $conn->prepare($query);
$stmt->execute();

$conn = null;

$ftp_host = '185.128.138.127';
$ftp_username = 'sbm';
$ftp_password = 's@!AmatFtp01';
$ftp_directory = '/';
$ftp_port = 5021; // پورت FTP مورد نظر

$ftp_conn = ftp_connect($ftp_host, $ftp_port);
$login = ftp_login($ftp_conn, $ftp_username, $ftp_password);

if ($login) {
    ftp_put($ftp_conn, $ftp_directory . '/' . $backup_file, $backup_file, FTP_BINARY);
    echo 'بک‌آپ با موفقیت آپلود شد.';
} else {
    echo 'مشکلی در اتصال به سرور FTP رخ داده است.';
}

// بستن اتصال به سرور FTP
ftp_close($ftp_conn);
?>