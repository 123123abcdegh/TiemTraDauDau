<?php
// Đọc dữ liệu từ file JSON
$dataFile = 'data.json';
$items = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$categories = ['Tất cả', 'Nước', 'Trà', 'Đồ ăn', 'Giải khát'];
$current_category = isset($_GET['cat']) ? $_GET['cat'] : 'Tất cả';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Tiệm Nước Siêu Cute</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Quicksand', sans-serif; background-color: #fff0f5; color: #5a3d45; margin: 0; padding: 20px; position: relative; overflow-x: hidden; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { text-align: center; color: #ff6b81; font-size: 2.5em; text-shadow: 2px 2px 4px rgba(255, 183, 197, 0.5); }
        .tabs { display: flex; justify-content: center; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; }
        .tab-btn { background: white; border: 2px solid #ffb7c5; padding: 10px 25px; border-radius: 30px; color: #ff6b81; font-weight: 700; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 6px rgba(255,183,197,0.2); }
        .tab-btn:hover, .tab-btn.active { background: #ffb7c5; color: white; transform: translateY(-2px); }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        
        /* Hiệu ứng nổi món khi chạm vào (Hover) */
        .card { background: white; border-radius: 25px; padding: 15px; text-align: center; box-shadow: 0 10px 20px rgba(255,183,197,0.3); transition: transform 0.3s, border-color 0.3s; border: 2px solid transparent; cursor: pointer; }
        .card:hover { transform: translateY(-8px); border-color: #ffb7c5; box-shadow: 0 15px 25px rgba(255,183,197,0.5); }
        
        .card img { width: 100%; height: 200px; object-fit: cover; border-radius: 15px; }
        .card h3 { margin: 15px 0 5px; color: #ff4757; font-size: 1.3em; }
        .card .price { font-size: 1.2em; font-weight: bold; color: #ff6b81; }
        .card .tag { display: inline-block; background: #ffeaa7; color: #d63031; padding: 4px 12px; border-radius: 15px; font-size: 0.85em; font-weight: 600; margin-top: 8px; }
        .empty { text-align: center; width: 100%; color: #ff6b81; font-size: 1.2em; margin-top: 50px; }

        /* Style cho hiệu ứng click chân mèo */
        .cat-paw {
            position: absolute;
            font-size: 30px; /* Độ to của chân mèo */
            pointer-events: none; /* Tránh click nhầm vào icon */
            transform: translate(-50%, -50%);
            animation: pawFade 0.8s ease-out forwards;
            z-index: 9999;
        }

        @keyframes pawFade {
            0% { opacity: 1; transform: translate(-50%, -50%) scale(0.5); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { opacity: 0; transform: translate(-50%, -80%) scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌸 Tiệm Nước Ngọt Ngào 🌸</h1>
        
        <div class="tabs">
            <?php foreach($categories as $cat): ?>
                <a href="?cat=<?= urlencode($cat) ?>" class="tab-btn <?= $current_category === $cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid">
            <?php 
            $count = 0;
            foreach($items as $item): 
                if($current_category === 'Tất cả' || $item['category'] === $current_category):
                    $count++;
            ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="Ảnh món">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <div class="price"><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</div>
                    <div class="tag">#<?= htmlspecialchars($item['tag']) ?></div>
                </div>
            <?php 
                endif;
            endforeach; 
            
            if($count === 0) {
                echo "<div class='empty'>Chưa có món nào ở danh mục này bạn nhé (╥_╥)</div>";
            }
            ?>
        </div>
    </div>

    <!-- Script tạo hiệu ứng click chân mèo -->
    <script>
        document.addEventListener('click', function(e) {
            // Tạo 1 thẻ div chứa icon chân mèo
            const paw = document.createElement('div');
            paw.classList.add('cat-paw');
            paw.innerHTML = '🐾'; // Bạn có thể thay bằng icon trái tim ❤️ nếu thích
            
            // Đặt vị trí chân mèo đúng vào chỗ click chuột
            paw.style.left = e.pageX + 'px';
            paw.style.top = e.pageY + 'px';
            
            // Thêm vào body
            document.body.appendChild(paw);
            
            // Xóa chân mèo sau 800ms (bằng thời gian animation) để không làm nặng web
            setTimeout(() => {
                paw.remove();
            }, 800);
        });
    </script>
</body>
</html>