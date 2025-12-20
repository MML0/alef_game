<?php
include 'db.php'; // Include the database connection

function createTables($fresh) {
    global $pdo;
    
    try {
        // If fresh is 'yes', drop the tables first
        if ($fresh === 'yes') {
            $pdo->exec("DROP TABLE IF EXISTS answers, users, questions, game_stats;");
            echo "Tables dropped successfully!<br>";
        }

        // Create Users Table
        $pdo->exec(" 
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                phone_number VARCHAR(15) NOT NULL UNIQUE,
                token VARCHAR(255) NOT NULL,
                score INT DEFAULT 0,
                current_question INT DEFAULT 1,
                start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                start_game_time TIMESTAMP NULL,  
                end_game_time TIMESTAMP NULL,    
                game_status ENUM('not_started', 'ongoing', 'completed') DEFAULT 'not_started',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        
        // Create Questions Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                question_text TEXT NOT NULL,
                answer_1 TEXT NOT NULL,
                answer_2 TEXT NOT NULL,
                answer_3 TEXT NOT NULL,
                answer_4 TEXT NOT NULL,
                correct_answer INT NOT NULL
            );
        ");
        
        // Create Answers Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS answers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                question_id INT,
                answer INT,
                is_correct BOOLEAN,
                answer_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (question_id) REFERENCES questions(id)
            );
        ");
        // Create Game Stats Table (for storing game results for the top users)
        $pdo->exec(" 
            CREATE TABLE IF NOT EXISTS game_stats (
                game_status ENUM('not_started', 'ongoing', 'ended') DEFAULT 'not_started'
            );
        ");
        // Insert initial 'not_started' status if no records exist
        $stmt = $pdo->prepare("
            INSERT INTO game_stats (game_status)
            SELECT 'not_started'
            WHERE NOT EXISTS (SELECT 1 FROM game_stats)
        ");
        $stmt->execute();

        echo "Database tables created successfully!<br>";

        // If 'fresh' is 'yes', populate the database with random data
        if ($fresh === 'yes') {
            addFreshData();
        }
        
    } catch (PDOException $e) {
        echo "Error creating tables: " . $e->getMessage();
    }
}

function loadUsersFromCSV($csvFilePath) {
    $users = [];
    
    // Open the CSV file
    if (($handle = fopen($csvFilePath, 'r')) !== FALSE) {
        // Skip the first line (header)
        fgetcsv($handle);
        
        // Read each line in the CSV
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Extract the phone number
            $firstName = $data[0];  // name
            $lastName = $data[1];   // last name
            $phoneNumber = $data[2];  // phone num
            
            // Generate a random token for the user
            $token = bin2hex(random_bytes(16));
            
            // Add the user to the list
            $users[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $phoneNumber,
                'token' => $token
            ];
        }
        
        // Close the file
        fclose($handle);
    } else {
        echo "Error opening the CSV file.";
    }
    
    return $users;
}

function addFreshData() {
    global $pdo;
    
    // Insert questions into the questions table
    $questions = [
        [
            "question_text" => "داستان برند «الف» بیشتر به چه مفهومی اشاره داره؟",
            "answer_1" => "روایت تاریخچه‌ی الف",
            "answer_2" => "روایتی روشن، انسانی و ریشه‌دار در زندگی واقعی",
            "answer_3" => "معرفی موفقیت‌های تجاری الف",
            "answer_4" => "داستانی که با «یکی بود، یکی نبود» شروع می‌شه!",
            "correct_answer" => 2
        ],
        [
            "question_text" => "کدام گزینه، مأموریت «الف» رو توضیح می‌ده؟",
            "answer_1" => "تمرکز بر آسان کردن زندگی و احترام به ریشه‌ها",
            "answer_2" => "تمرکز بر رقابت در بازار",
            "answer_3" => "رشد سریع و توسعه‌ی پایدار",
            "answer_4" => "نجات دنیا و مهار قیمت دلار!",
            "correct_answer" => 1
        ],
        [
            "question_text" => "چشم‌انداز «الف» روی چه چیزی تأکید داره؟",
            "answer_1" => "کیفیت زندگی، سادگی و نگاه انسانی به آینده",
            "answer_2" => "سازماندهی و نگاه تکنولوژیک",
            "answer_3" => "توسعه، مقیاس‌پذیری و چابکی",
            "answer_4" => "آینده‌ای هوشمند و رباتیک!",
            "correct_answer" => 1
        ],
        [
            "question_text" => "کدام گزینه شامل ارزش‌های کلیدی «الف» هست؟",
            "answer_1" => "اعتماد، تعهد، اعتبار و اطمینان",
            "answer_2" => "دقت، سرعت، کیفیت و نتیجه‌گرایی",
            "answer_3" => "تعهد، کیفیت، همراهی و سنجیده‌کاری",
            "answer_4" => "هر چه جمع بپسنده!",
            "correct_answer" => 3
        ],
        [
            "question_text" => "فرهنگ سازمانی «الف» به چه صورتیه؟",
            "answer_1" => "مجموعه‌ای از قوانین مشخص و ثابت",
            "answer_2" => "جریانی زنده، با پذیرش تفاوت‌ها و مبتنی بر گفت‌وگو",
            "answer_3" => "چارچوبی برای یکپارچگی",
            "answer_4" => "بر پایهی اصول فرهنگستان زبان و ادب فارسی!",
            "correct_answer" => 2
        ],
        [
            "question_text" => "تعداد حدودی همکاران تو در گروه شرکت‌های «الف» چند نفر ه؟",
            "answer_1" => "۳۵۵۹ نفر",
            "answer_2" => "۳۶۵۰ نفر",
            "answer_3" => "۳۵۶۰ نفر",
            "answer_4" => "هر بار که می‌شماریم، فرق می‌کنه!",
            "correct_answer" => 1
        ],
        [
            "question_text" => "نام برند «میوا» بر اساس چه مفهومی شکل گرفته؟",
            "answer_1" => "نام یک روستا در دماوند",
            "answer_2" => "مخفف عبارت My Water",
            "answer_3" => "متشکل از ابتدای کلمات Mineral Water",
            "answer_4" => "از یک میوه‌ی خاص اومده!",
            "correct_answer" => 3
        ],
        [
            "question_text" => "نام برند «آستریا» به چه معناست؟",
            "answer_1" => "مس و لوله‌های مسی",
            "answer_2" => "نام الهه یونانی",
            "answer_3" => "نام یک شهر صنعتی",
            "answer_4" => "یک کلمه‌ی خارجی شیک و باکلاس",
            "correct_answer" => 2
        ],
        [
            "question_text" => "نام اولین محصول «پاکشوما» چه بود و در چه سالی وارد بازار شد؟",
            "answer_1" => "لباسشویی Wash-On (سال ۱۳۵۳)",
            "answer_2" => "لباسشویی Lady Wash (سال ۱۳۵۴)",
            "answer_3" => "جاروبرقی Nimbus (سال ۱۳۶۰)",
            "answer_4" => "ظرفشویی Kozet (سال ۱۳۶۲)",
            "correct_answer" => 2
        ],
        [
            "question_text" => "اگر امشب حضرت حافظ بخواد درباره‌ی «الف» فال بگیره، کدوم شعر به اون نزدیک‌تره؟",
            "answer_1" => "چو دخلت نیست، خرج آهسته‌تر کن",
            "answer_2" => "بنال بلبل اگر با منت سر یاریست",
            "answer_3" => "درخت دوستی بنشان که کام دل به بار آرد",
            "answer_4" => "برو کار می‌کن مگو چیست کار!",
            "correct_answer" => 3
        ]
    ];

    foreach ($questions as $question) {
        $stmt = $pdo->prepare("
            INSERT INTO questions (question_text, answer_1, answer_2, answer_3, answer_4, correct_answer)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $question['question_text'],
            $question['answer_1'],
            $question['answer_2'],
            $question['answer_3'],
            $question['answer_4'],
            $question['correct_answer']
        ]);
    }
    
    echo "load from csv.<br>";


    $users = loadUsersFromCSV('users.csv');
    
    foreach ($users as $user) {
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, phone_number, token, game_status)
            VALUES (?, ?, ?, ?, 'not_started')
        ");
        $stmt->execute([
            $user['first_name'], 
            $user['last_name'], 
            $user['phone_number'], 
            $user['token']
        ]);
    }

    
    echo "users added to the database.<br>";
}

// Check if 'fresh' and 'pass' are provided in the GET request
if (isset($_GET['fresh']) && isset($_GET['pass'])) {
    $fresh = $_GET['fresh'];
    $pass = $_GET['pass'];

    // Check if 'pass' is correct (can add more logic here)
    if ($pass === '1234') {
        createTables($fresh); // Create tables and add fresh data if 'fresh' is 'yes'
    } else {
        echo "Invalid pass value.<br>";
    }
} else {
    echo "Required parameters 'fresh' and 'pass' are missing.<br>";
}
?>
