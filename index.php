<?php
$token = "TOKEN KAMU"

// Ambil konten POST yang dikirimkan oleh Telegram
$update = json_decode(file_get_contents("php://input"), true);

    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];
    $banned = json_decode(file_get_contents("banned-group.json"), true);
    $left = json_decode(file_get_contents("left.json"), true);
// Fungsi untuk mengirimkan pesan balasan ke pengguna
function sendMessage($chat_id, $text) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = http_build_query(array(
        'chat_id' => $chat_id,
        'text' => $text
    ));
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $data
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

// Fungsi untuk menyimpan ID pengguna ke dalam database
function saveUserId($user_id) {
    $json_file = 'list-id.json';
    $data = json_decode(file_get_contents($json_file));
    if (!in_array($user_id, $data->user_ids)) {
        $data->user_ids[] = $user_id;
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($json_file, $json_data);
             sendMessage($chat_id, "Anda telah bergabung lagi, ketik /left untuk keluar.");
    } else {
             sendMessage($chat_id, "Anda sudah bergabung.");
        
    }
}

function removeUserId($user_id) {
$json_file = 'list-id.json';
$data = json_decode(file_get_contents($json_file));
if (in_array($user_id, $data->user_ids)) {
$index = array_search($user_id, $data->user_ids);
array_splice($data->user_ids, $index, 1);
$json_data = json_encode($data, JSON_PRETTY_PRINT);
file_put_contents($json_file, $json_data);
            sendMessage($chat_id, "Anda telah keluar group! ketik /join untuk bergabung lagi.");
} else {
    
            sendMessage($chat_id, "Anda sudah keluar.");
}
}

if (in_array($chat_id, $banned)) {
    sendMessage(
        $chat_id,
        "Anda telah dibanned dari bot 🤖, kontak @rasirt2 untuk membukanya. 📞"
    );
    exit();
}
if (in_array($chat_id, $left)) {
    sendMessage(
        $chat_id,
        "Anda telah Keluar dari grup, ketik /join dulu."
    );
    exit();
}

if ( $text == "/id") {
    sendMessage(
        $chat_id,
        "ID Anda : $chat_id"
    );
    exit();
}

if ($text =="/join") {
    $chat_id = $update['message']['chat']['id'];
    $left = json_decode(file_get_contents("left.json"), true);
    saveUserId($chat_id);
        if (!in_array($chat_id, $left)) {
            $left[] = $chat_id;
            file_put_contents("left.json", json_encode($left));
            sendMessage($chat_id, "Anda telah join kembali, ketik /stop untuk keluar group.");
        } else {
            sendMessage($chat_id, "Anda sudah join digrup.");
        }
        exit();
}

if ($text =="/stop") {
    $chat_id = $update['message']['chat']['id'];
        $left = json_decode(file_get_contents("left.json"), true);
         removeUserId($chat_id);
        if (!in_array($chat_id, $left)) {
            $left[] = $chat_id;
            file_put_contents("left.json", json_encode($left));
            sendMessage($chat_id, "Anda telah keluar group! ketik /join untuk bergabung lagi.");
        } else {
            sendMessage($chat_id, "Anda sudah keluar.");
        }
        exit();
}


if (strpos($text, "/ban") === 0) {
    if ($chat_id == 1613688326) {
        $userId = trim(substr($text, 5));
        $banned = json_decode(file_get_contents("banned-group.json"), true);
        if (!in_array($userId, $banned)) {
            $banned[] = $userId;
            file_put_contents("banned-group.json", json_encode($banned));
            sendMessage($chat_id, "User ID $userId telah dibanned.");
        } else {
            sendMessage($chat_id, "User ID $userId sudah dibanned sebelumnya.");
        }
        exit();
    } else {
        sendMessage($chat_id, "Anda bukan owner");
        exit();
    }
}

if (strpos($text, "/unban") === 0) {
    if ($chat_id == 1613688326) {
        $userId = trim(substr($text, 7));
        $banned = json_decode(file_get_contents("banned-group.json"), true);
        if (in_array($userId, $banned)) {
            $banned = array_diff($banned, [$userId]);
            file_put_contents(
                "banned-group.json",
                json_encode(array_values($banned))
            );
            sendMessage($chat_id, "User ID $userId telah diunban.");
        } else {
            sendMessage(
                $chat_id,
                "User ID $userId tidak ditemukan dalam daftar banned."
            );
        }
        exit();
    } else {
        sendMessage($chat_id, "Anda bukan owner");
        exit();
    }
}

// Handle command /start
if ($update['message']['text'] == '/start') {
    $chat_id = $update['message']['chat']['id'];
    $first_name = $update['message']['chat']['first_name'];
    sendMessage($chat_id, "Bot Created Using Bot @BotMakerApp");
    sendMessage($chat_id, "Halo, $first_name! Selamat datang di group anonymous!.
    
Stop ketik /stop dan join ketik /join.

✅ Pesan
❌ Media
❌ Poll
❌ Audio
❌ Reply
❌ Video
❌ Photo");
   
    saveUserId($chat_id);
    exit();
} else {
    $chat_id = $update['message']['from']['id'];

    // Kirim pesan ke semua pengguna yang terdaftar
    $data = json_decode(file_get_contents('list-id.json'));
    $left = json_decode(file_get_contents("left.json"), true);
    foreach ($data->user_ids as $user_id) {
        if ($user_id != $chat_id) {
            if ($update['message']['from']['id'] == 1613688326) {
    $chat_id = "OWNER";
}
            sendMessage($user_id, $update['message']['chat']['first_name'] . ' (' . $chat_id . ') ' . ': ' . $update['message']['text']);
        }
    }
}
