<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$koneksi = new mysqli("localhost", "root", "", "login_tele");
if ($koneksi->connect_error) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

// Cek apakah field tersedia
if (isset($_POST['phone']) && (isset($_POST['message']) || isset($_FILES['media']))) {
    $phone     = $_POST['phone'];
    $message   = isset($_POST['message']) ? $_POST['message'] : null;
    $mediaPath = null;

    // Cek apakah file dikirim
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        file_put_contents("debug.txt", print_r($_FILES, true));

        $targetDir  = "uploads/";
        $ext        = pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION);
        $filename   = uniqid() . "." . $ext;
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES["media"]["tmp_name"], $targetFile)) {
            $mediaPath = $targetFile;
            error_log("✅ File uploaded ke: $mediaPath");
        } else {
            error_log("❌ Gagal upload file ke: $targetFile");
            echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
            exit();
        }
    }

    error_log("📦 Data disimpan:");
    error_log("Phone: $phone");
    error_log("Message: $message");
    error_log("Media: $mediaPath");

    $stmt = $koneksi->prepare("INSERT INTO pesan (phone_number, message, media_path, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $phone, $message, $mediaPath);
    $sukses = $stmt->execute();

    if ($sukses) {
        echo json_encode(["status" => "success", "message" => "Pesan terkirim"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan pesan"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
