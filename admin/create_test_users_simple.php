<?php
require_once __DIR__ . '/../config/database.php';

// Function to generate random names
function generateRandomName() {
    $firstNames = [
        'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'James', 'Jessica', 'Robert', 'Amanda',
        'William', 'Ashley', 'Richard', 'Stephanie', 'Joseph', 'Nicole', 'Thomas', 'Elizabeth', 'Christopher', 'Helen',
        'Charles', 'Deborah', 'Daniel', 'Rachel', 'Matthew', 'Carolyn', 'Anthony', 'Janet', 'Mark', 'Catherine',
        'Donald', 'Maria', 'Steven', 'Heather', 'Paul', 'Diane', 'Andrew', 'Ruth', 'Joshua', 'Julie',
        'Kenneth', 'Joyce', 'Kevin', 'Virginia', 'Brian', 'Victoria', 'George', 'Kelly', 'Edward', 'Lauren',
        'Ronald', 'Christine', 'Timothy', 'Joan', 'Jason', 'Evelyn', 'Jeffrey', 'Judith', 'Ryan', 'Megan',
        'Jacob', 'Cheryl', 'Gary', 'Andrea', 'Nicholas', 'Hannah', 'Eric', 'Jacqueline', 'Jonathan', 'Martha',
        'Stephen', 'Gloria', 'Larry', 'Teresa', 'Justin', 'Ann', 'Scott', 'Sara', 'Brandon', 'Madison',
        'Benjamin', 'Frances', 'Samuel', 'Kathryn', 'Frank', 'Janice', 'Gregory', 'Jean', 'Raymond', 'Abigail',
        'Alexander', 'Alice', 'Patrick', 'Julia', 'Jack', 'Judy', 'Dennis', 'Sophia', 'Jerry', 'Grace',
        'Tyler', 'Denise', 'Aaron', 'Amber', 'Jose', 'Doris', 'Adam', 'Marilyn', 'Nathan', 'Danielle',
        'Henry', 'Beverly', 'Douglas', 'Isabella', 'Peter', 'Theresa', 'Zachary', 'Diana', 'Kyle', 'Natalie',
        'Walter', 'Brittany', 'Ethan', 'Charlotte', 'Jeremy', 'Marie', 'Harold', 'Kayla', 'Carl', 'Alexis',
        'Keith', 'Tiffany', 'Roger', 'Kaylee', 'Gerald', 'Destiny', 'Eugene', 'Lily', 'Arthur', 'Samantha',
        'Terry', 'Audrey', 'Christian', 'Angela', 'Sean', 'Jacqueline', 'Andrew', 'Eva', 'Edward', 'Maya',
        'Patrick', 'Madeline', 'Shawn', 'Penelope', 'Ronnie', 'Chloe', 'Larry', 'Layla', 'Russell', 'Riley',
        'Roy', 'Zoey', 'Ralph', 'Hannah', 'Bobby', 'Aria', 'Howard', 'Lillian', 'Eugene', 'Addison',
        'Carlos', 'Eleanor', 'Russell', 'Natalie', 'Bobby', 'Luna', 'Victor', 'Savannah', 'Martin', 'Brooklyn',
        'Ernest', 'Leah', 'Phillip', 'Zoe', 'Todd', 'Audrey', 'Jesse', 'Claire', 'Craig', 'Ivy',
        'Alan', 'Stella', 'Shawn', 'Violet', 'Claude', 'Bella', 'Mitchell', 'Aurora', 'Gerald', 'Lucy',
        'Jay', 'Anna', 'Adrian', 'Sofia', 'Karl', 'Caroline', 'Cory', 'Genesis', 'Claude', 'Aaliyah',
        'Erik', 'Kennedy', 'Dana', 'Kinsley', 'Dan', 'Allison', 'Ron', 'Maya', 'Vincent', 'Sarah',
        'Russ', 'Madelyn', 'Marshall', 'Adeline', 'Sal', 'Alexa', 'Perry', 'Ariana', 'Mickey', 'Elena',
        'Ed', 'Gabriella', 'Ben', 'Naomi', 'Jon', 'Alice', 'Dick', 'Sadie', 'Marty', 'Hailey',
        'Sid', 'Eva', 'Cary', 'Emilia', 'Milo', 'Autumn', 'Eddie', 'Quinn', 'Nicholas', 'Nevaeh',
        'Van', 'Piper', 'Fernando', 'Ruby', 'Duncan', 'Serenity', 'Danny', 'Willow', 'Bryan', 'Everly',
        'Kent', 'Cora', 'Efrain', 'Kaylee', 'Donn', 'Lydia', 'Gerardo', 'Paisley', 'Dante', 'Athena',
        'Jaime', 'Violet', 'Enoch', 'Clara', 'Michale', 'Bella', 'Mikel', 'Savannah', 'Orville', 'Lucy',
        'Maxwell', 'Paisley', 'Jarrod', 'Everly', 'Vernon', 'Bella', 'Pasquale', 'Claire', 'Mohammad', 'Isla',
        'Marlon', 'Chloe', 'Antone', 'Penelope', 'Enrique', 'Layla', 'Dorian', 'Riley', 'Dusty', 'Grace',
        'Sidney', 'Zoey', 'Lucio', 'Nora', 'Mickey', 'Lily', 'Kasey', 'Eleanor', 'Rosendo', 'Hannah',
        'Milford', 'Lillian', 'Sang', 'Addison', 'Deon', 'Aria', 'Christoper', 'Luna', 'Alfonzo', 'Ellie',
        'Lyman', 'Natalie', 'Josiah', 'Brooklyn', 'Brant', 'Scarlett', 'Wilton', 'Avery', 'Rico', 'Evelyn',
        'Rhett', 'Madison', 'Ethan', 'Chloe', 'Sydney', 'Ella', 'Alonso', 'Grace', 'Leif', 'Victoria',
        'Lavern', 'Riley', 'Carey', 'Aria', 'Carroll', 'Lily', 'Donny', 'Aubrey', 'Paris', 'Zoey',
        'Tyson', 'Penelope', 'Neville', 'Layla', 'Kelley', 'Chloe', 'Edison', 'Riley', 'Merle', 'Grace'
    ];
    
    $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
        'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts',
        'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes',
        'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper',
        'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward', 'Richardson',
        'Watson', 'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes',
        'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster', 'Jimenez',
        'Powell', 'Jenkins', 'Perry', 'Russell', 'Sullivan', 'Bell', 'Coleman', 'Butler', 'Henderson', 'Barnes',
        'Gonzales', 'Fisher', 'Vasquez', 'Simmons', 'Romero', 'Jordan', 'Patterson', 'Alexander', 'Hamilton', 'Graham',
        'Reynolds', 'Griffin', 'Wallace', 'Moreno', 'West', 'Cole', 'Hayes', 'Chavez', 'Gibson', 'Bryant',
        'Ellis', 'Stevens', 'Murray', 'Ford', 'Marshall', 'Owens', 'Mcdonald', 'Harrison', 'Ruiz', 'Kennedy',
        'Wells', 'Alvarez', 'Woods', 'Mendoza', 'Castillo', 'Olson', 'Webb', 'Washington', 'Tucker', 'Freeman',
        'Burns', 'Henry', 'Vasquez', 'Snyder', 'Simpson', 'Crawford', 'Jimenez', 'Porter', 'Mason', 'Shaw',
        'Gordon', 'Wagner', 'Hunter', 'Romero', 'Hicks', 'Dixon', 'Hunt', 'Palmer', 'Robertson', 'Black',
        'Holmes', 'Stone', 'Meyer', 'Boyd', 'Mills', 'Warren', 'Fox', 'Rose', 'Rice', 'Moreno',
        'Schmidt', 'Patel', 'Ferguson', 'Nichols', 'Herrera', 'Medina', 'Ryan', 'Fernandez', 'Weaver', 'Daniels',
        'Stephens', 'Gardner', 'Payne', 'Kelley', 'Dunn', 'Pierce', 'Arnold', 'Tran', 'Spencer', 'Peters',
        'Hawkins', 'Grant', 'Hansen', 'Castro', 'Hoffman', 'Hart', 'Elliott', 'Cunningham', 'Knight', 'Bradley',
        'Carroll', 'Hudson', 'Duncan', 'Armstrong', 'Berry', 'Andrews', 'Johnston', 'Ray', 'Lane', 'Riley',
        'Carpenter', 'Perkins', 'Aguilar', 'Silva', 'Richards', 'Willis', 'Matthews', 'Chapman', 'Lawrence', 'Garza',
        'Vargas', 'Watkins', 'Wheeler', 'Larson', 'Carlson', 'Harper', 'George', 'Greene', 'Burke', 'Guzman',
        'Morrison', 'Munoz', 'Jacobs', 'Obrien', 'Lawson', 'Franklin', 'Lynch', 'Bishop', 'Carr', 'Salazar',
        'Austin', 'Mendez', 'Gilbert', 'Jensen', 'Williamson', 'Montgomery', 'Harvey', 'Oliver', 'Howell', 'Dean',
        'Hanson', 'Weber', 'Garrett', 'Sims', 'Wagner', 'Burton', 'Black', 'Cole', 'Mcdaniel', 'West',
        'Jacobs', 'Reid', 'Spence', 'Thornton', 'Slaughter', 'Camacho', 'Kirk', 'Duffy', 'Davila', 'Thornton',
        'Mckenzie', 'Tyson', 'Duke', 'Townsend', 'Swanson', 'Norman', 'Mccarthy', 'Mccormick', 'Leonard', 'Harmon',
        'Walsh', 'Buchanan', 'Joyce', 'Dickson', 'Bartlett', 'Oconnor', 'Skinner', 'Blair', 'Daniel', 'Cross',
        'Simon', 'Dennis', 'Oconnor', 'Quinn', 'Gross', 'Navarro', 'Moss', 'Fitzgerald', 'Doyle', 'Mclaughlin',
        'Rojas', 'Rodgers', 'Stevenson', 'Singh', 'Yang', 'Figueroa', 'Harmon', 'Newton', 'Paul', 'Manning',
        'Garner', 'Mcgee', 'Reese', 'Francis', 'Burgess', 'Adkins', 'Goodman', 'Curry', 'Brady', 'Christensen',
        'Potter', 'Walton', 'Goodwin', 'Mullins', 'Molina', 'Webster', 'Fischer', 'Campos', 'Acosta', 'Marsh',
        'Horton', 'Mcdonald', 'Avila', 'Fleming', 'Bond', 'Daugherty', 'Calhoun', 'Wade', 'Eaton', 'Gates',
        'Mccann', 'Lennon', 'Hays', 'Norris', 'Prescott', 'Tran', 'Callahan', 'Ritter', 'Cowan', 'Rosen',
        'Mckenna', 'Leach', 'Mccullough', 'Vaughn', 'Berger', 'Daugherty', 'Strickland', 'Townsend', 'Potter', 'Huffman',
        'Boyer', 'Mclaughlin', 'Moss', 'Thornton', 'Dennis', 'Mccray', 'Lowery', 'Caldwell', 'Seymour', 'Luna',
        'Sexton', 'Powers', 'Vaughn', 'Briggs', 'Chan', 'Salazar', 'Mckinney', 'Love', 'Dudley', 'Rice',
        'Price', 'Hendrix', 'Cunningham', 'Hunt', 'Pierce', 'Meyer', 'Cruz', 'Montgomery', 'Marshall', 'Castro',
        'Ortiz', 'Myers', 'Dixon', 'Murray', 'Spencer', 'Tucker', 'Jennings', 'Vincent', 'Higgins', 'Gordon',
        'Sherman', 'Walters', 'Cole', 'Mack', 'Gilbert', 'Barrett', 'Tyler', 'Nunez', 'Fleming', 'Richardson'
    ];
    
    $firstName = $firstNames[array_rand($firstNames)];
    $lastName = $lastNames[array_rand($lastNames)];
    
    return $firstName . ' ' . $lastName;
}

// Function to generate username from name
function generateUsername($name) {
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]/', '', $name);
    $username = substr($name, 0, 8);
    $username .= rand(100, 999);
    return $username;
}

try {
    $pdo = getDBConnection();
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() == 0) {
        die("Admin users table does not exist. Please create it first.");
    }
    
    // Get current count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<h2>Creating 1000 Test Users</h2>";
    echo "<p>Current users in database: $currentCount</p>";
    
    // Create 1000 test users
    $successCount = 0;
    $errorCount = 0;
    
    for ($i = 1; $i <= 1000; $i++) {
        $fullName = generateRandomName();
        $username = generateUsername($fullName);
        $password = 'test123'; // Default password for all test users
        $email = $username . '@test.com';
        $role = 'admin'; // Default role as requested
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_users (username, email, password, full_name, role, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$username, $email, $hashedPassword, $fullName, $role]);
            
            $successCount++;
            
            if ($i % 100 == 0) {
                echo "<p>Created $i users so far...</p>";
            }
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p>Error creating user $i: " . $e->getMessage() . "</p>";
        }
    }
    
    // Get final count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
    $finalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<h3>Results:</h3>";
    echo "<p>✅ Successfully created: $successCount users</p>";
    echo "<p>❌ Errors: $errorCount</p>";
    echo "<p>📊 Total users in database: $finalCount</p>";
    echo "<p>🔑 Default password for all test users: <strong>test123</strong></p>";
    echo "<p>👤 Default role for all users: <strong>admin</strong></p>";
    
    // Show some sample users
    $stmt = $pdo->query("SELECT username, full_name, role FROM admin_users ORDER BY id DESC LIMIT 10");
    $sampleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Sample Users Created:</h3>";
    echo "<ul>";
    foreach ($sampleUsers as $user) {
        echo "<li><strong>{$user['username']}</strong> - {$user['full_name']} ({$user['role']})</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='setting.php' style='color: blue; text-decoration: underline;'>← Back to Settings</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h2, h3 {
    color: #333;
}

p {
    margin: 10px 0;
    line-height: 1.5;
}

ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

li {
    margin: 5px 0;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

li:last-child {
    border-bottom: none;
}
</style> 