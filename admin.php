<?php
session_start();

// 1. Xử lý Đăng nhập
if (isset($_POST['login'])) {
    if ($_POST['password'] === 'binhdz') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Mật mã không đúng!";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Hàm khởi tạo và đọc dữ liệu
$dataFile = 'data.json';
if (!file_exists($dataFile)) file_put_contents($dataFile, json_encode([]));
$items = json_decode(file_get_contents($dataFile), true);

// Form đăng nhập
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8"><title>Đăng nhập Admin</title>
        <style>
            body { font-family: sans-serif; background: #fff0f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .login-box { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; }
            input[type="password"] { padding: 10px; width: 200px; border: 1px solid #ccc; border-radius: 5px; }
            button { padding: 10px 20px; background: #ff6b81; color: white; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2 style="color: #ff6b81;">Quản Trị Tiệm Nước</h2>
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Nhập mật mã..." required>
                <button type="submit" name="login">Vào</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 2. Xử lý Thêm / Sửa / Xóa
if (!is_dir('img')) mkdir('img', 0777, true);

if (isset($_POST['save_item'])) {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? $_POST['id'] : uniqid();
    
    // Nếu là sửa, giữ lại ảnh cũ nếu không up ảnh mới
    $imagePath = isset($items[$id]['image']) ? $items[$id]['image'] : '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImagePath = 'img/' . $id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $newImagePath)) {
            if ($imagePath && file_exists($imagePath)) unlink($imagePath); // Xóa ảnh cũ
            $imagePath = $newImagePath;
        }
    }

    $items[$id] = [
        'id' => $id,
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'tag' => $_POST['tag'],
        'category' => $_POST['category'],
        'image' => $imagePath
    ];
    
    file_put_contents($dataFile, json_encode($items));
    header("Location: admin.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (isset($items[$id])) {
        if (file_exists($items[$id]['image'])) unlink($items[$id]['image']);
        unset($items[$id]);
        file_put_contents($dataFile, json_encode($items));
    }
    header("Location: admin.php");
    exit;
}

// Chuẩn bị dữ liệu cho form Sửa
$editItem = null;
if (isset($_GET['edit']) && isset($items[$GET['edit']])) {
    $editItem = $items[$GET['edit']];
}
if (isset($_GET['edit']) && isset($items[$_GET['edit']])) {
    $editItem = $items[$_GET['edit']];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Menu</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; display: flex; justify-content: space-between; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button.btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        a.btn-danger { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 14px; }
        a.btn-edit { background: #ffc107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; }
        img.preview { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản lý Menu <a href="?logout=1" style="font-size: 14px; color: red;">Đăng xuất</a></h2>
        
        <!-- FORM THÊM / SỬA -->
        <div style="background: #e9ecef; padding: 15px; border-radius: 5px;">
            <h3><?= $editItem ? 'Sửa món' : 'Thêm món mới' ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editItem ? $editItem['id'] : '' ?>">
                
                <div class="form-group">
                    <label>Tên món</label>
                    <input type="text" name="name" value="<?= $editItem ? htmlspecialchars($editItem['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Giá bán (VNĐ)</label>
                    <input type="number" name="price" value="<?= $editItem ? htmlspecialchars($editItem['price']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Thẻ tự nhập (VD: Cafe, Sữa Chua, Rau Má...)</label>
                    <input type="text" name="tag" value="<?= $editItem ? htmlspecialchars($editItem['tag']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tab Hiển Thị</label>
                    <select name="category">
                        <?php
                        $cats = ['Nước', 'Trà', 'Đồ ăn', 'Giải khát'];
                        foreach($cats as $c) {
                            $selected = ($editItem && $editItem['category'] == $c) ? 'selected' : '';
                            echo "<option value='$c' $selected>$c</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Ảnh (<?= $editItem ? 'Bỏ trống nếu không muốn đổi ảnh' : 'Bắt buộc' ?>)</label>
                    <input type="file" name="image" accept="image/*" <?= $editItem ? '' : 'required' ?>>
                    <?php if($editItem && $editItem['image']): ?>
                        <br><img src="<?= $editItem['image'] ?>" class="preview" style="margin-top: 10px;">
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="save_item" class="btn"><?= $editItem ? 'Lưu thay đổi' : 'Thêm Món' ?></button>
                <?php if($editItem): ?>
                    <a href="admin.php" style="margin-left: 10px; color: #666;">Hủy sửa</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- DANH SÁCH MÓN -->
        <h3 style="margin-top: 30px;">Danh sách các món</h3>
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên món</th>
                    <th>Giá</th>
                    <th>Tab</th>
                    <th>Thẻ</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_reverse($items) as $item): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($item['image']) ?>" class="preview"></td>
                    <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                    <td><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><span style="background:#ffeaa7; padding: 2px 6px; border-radius: 10px; font-size:12px;">#<?= htmlspecialchars($item['tag']) ?></span></td>
                    <td>
                        <a href="?edit=<?= $item['id'] ?>" class="btn-edit">Sửa</a>
                        <a href="?delete=<?= $item['id'] ?>" class="btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa món này?');">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)) echo "<tr><td colspan='6' style='text-align:center;'>Chưa có dữ liệu</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</body>
</html>