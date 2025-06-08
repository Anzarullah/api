<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$koneksi = new mysqli("localhost", "root", "", "login_tele");
if ($koneksi->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

$inputJSON = file_get_contents('php://input');
$data      = json_decode($inputJSON);

// Validasi data
if ($data && isset($data->phone)) {
    $phone = $data->phone;

    if (! preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Format nomor telepon tidak valid",
        ]);
        exit();
    }

    $stmt = $koneksi->prepare("SELECT * FROM login WHERE phone_number = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            "status"  => "success",
            "message" => "Login berhasil",
            "phone"   => $phone,
        ]);
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Nomor tidak terdaftar",
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Data tidak valid atau kosong",
    ]);
}
