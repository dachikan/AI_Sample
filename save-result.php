<?php
// 正しいパスでdb_connect.phpを読み込む
require_once 'config/db_connect.php';

header('Content-Type: application/json');

// POSTリクエストのみ処理
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// POSTデータの取得
$aiTypeId = isset($_POST['ai_type_id']) ? (int)$_POST['ai_type_id'] : 1;
$prompt = isset($_POST['prompt']) ? $_POST['prompt'] : '';
$output = isset($_POST['output']) ? $_POST['output'] : '';
$improvement = isset($_POST['improvement']) ? $_POST['improvement'] : '';

// 画像アップロード処理
$inputImagePath = '';
$outputImagePath = '';

// 入力画像の処理
if (isset($_FILES['input_image']) && $_FILES['input_image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['input_image']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($ext), $allowed)) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $new_filename = 'input_' . uniqid() . '.' . $ext;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['input_image']['tmp_name'], $destination)) {
            $inputImagePath = $destination;
        }
    }
}

// 出力画像の処理
if (isset($_FILES['output_image']) && $_FILES['output_image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['output_image']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($ext), $allowed)) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $new_filename = 'output_' . uniqid() . '.' . $ext;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['output_image']['tmp_name'], $destination)) {
            $outputImagePath = $destination;
        }
    }
}

// データベースに保存
try {
    // AITrialResultsテーブルの構造に合わせてクエリを修正
    $sql = "INSERT INTO AITrialResults (ai_service, custom_prompt, result_description, improvements, input_image, output_image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    // AIタイプ名を取得
    $aiTypeName = '';
    $aiTypeQuery = "SELECT name FROM AITypes WHERE id = ?";
    $aiTypeStmt = $conn->prepare($aiTypeQuery);
    $aiTypeStmt->bind_param("i", $aiTypeId);
    $aiTypeStmt->execute();
    $aiTypeResult = $aiTypeStmt->get_result();
    
    if ($aiTypeResult && $aiTypeResult->num_rows > 0) {
        $aiTypeRow = $aiTypeResult->fetch_assoc();
        $aiTypeName = $aiTypeRow['name'];
    } else {
        $aiTypeName = 'Unknown AI';
    }
    
    $stmt->bind_param("ssssss", $aiTypeName, $prompt, $output, $improvement, $inputImagePath, $outputImagePath);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'データベース保存エラー: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'エラー: ' . $e->getMessage()]);
}
?>
