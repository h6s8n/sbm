
<?php
$url = 'https://sbm24.com';
$headers = get_headers($url);
$status_code = substr($headers[0], 9, 3);

if ($status_code != '200' ) {
    checkweb("سایت به مشکل خورده است لطفا سریعا اقدام به برسی کنید.");
} else {
    echo 'وضعیت کد سایت مناسب است.';
}




function checkweb($mes) {
  $url = "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/sms/send.json";
  
  $data = array(
    'receptor' => '09183640998,09039458207',
    'message' => $mes
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  curl_close($ch);

  $data = json_decode($response);

  if ($data->return->status === 200) {
    echo "Verification request was successful.";
  } else {
    echo "Verification request failed. Error: " . $data->return->message;
  }
}






?>