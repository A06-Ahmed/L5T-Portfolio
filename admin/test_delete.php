<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle delete test
if (isset($_POST['test_delete'])) {
    $projects_file = "../data/projects.json";
    
    if (file_exists($projects_file)) {
        $projects_data = file_get_contents($projects_file);
        if ($projects_data !== false) {
            $projects = json_decode($projects_data, true) ?: [];
            
            $message = "Current projects: " . count($projects) . "<br>";
            $message .= "Projects data: <pre>" . print_r($projects, true) . "</pre>";
            
            // Test deleting first project
            if (!empty($projects)) {
                $project_to_delete = $projects[0];
                array_splice($projects, 0, 1);
                
                $save_result = file_put_contents($projects_file, json_encode($projects, JSON_PRETTY_PRINT));
                
                if ($save_result !== false) {
                    $message .= "<br>✅ Delete test successful! Project removed.";
                } else {
                    $message .= "<br>❌ Delete test failed! Could not save file.";
                }
            } else {
                $message .= "<br>No projects to delete.";
            }
        } else {
            $message = "❌ Could not read projects file.";
        }
    } else {
        $message = "❌ Projects file not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Test - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        h1 {
            color: #000;
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            font-weight: 600;
        }
        
        .message {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #000;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .btn {
            background: #000;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .btn:hover {
            background: #333;
            transform: translateY(-1px);
        }
        
        .back-link {
            margin-top: 20px;
        }
        
        .back-link a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border: 1px solid #000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: #000;
            color: white;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            overflow-x: auto;
            font-size: 12px;
            margin: 10px 0;
        }
        
        .success {
            color: #28a745;
            font-weight: 600;
        }
        
        .error {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delete Function Test</h1>
        
        <form method="POST">
            <button type="submit" name="test_delete" class="btn">Test Delete First Project</button>
        </form>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 