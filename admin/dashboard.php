<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

// Handle delete request
if (isset($_POST['delete_project']) && isset($_POST['project_index'])) {
    $project_index = (int)$_POST['project_index'];
    
    $projects_file = "../data/projects.json";
    
    // Debug: Log the delete attempt
    error_log("Delete attempt - Index: $project_index, File: $projects_file");
    
    if (file_exists($projects_file)) {
        $projects_data = file_get_contents($projects_file);
        if ($projects_data !== false) {
            $projects = json_decode($projects_data, true) ?: [];
            
            // Debug: Log current projects
            error_log("Current projects count: " . count($projects));
            error_log("Projects: " . print_r($projects, true));
            
            if (isset($projects[$project_index])) {
                $project_to_delete = $projects[$project_index];
                
                // Debug: Log project to delete
                error_log("Project to delete: " . print_r($project_to_delete, true));
                
                // Remove project from array
                array_splice($projects, $project_index, 1);
                
                // Debug: Log projects after removal
                error_log("Projects after removal: " . print_r($projects, true));
                
                // Save updated projects
                $save_result = file_put_contents($projects_file, json_encode($projects, JSON_PRETTY_PRINT));
                
                if ($save_result !== false) {
                    // Delete the uploaded image file if it exists locally
                    if (isset($project_to_delete['image']) && strpos($project_to_delete['image'], 'assets/uploads/') === 0) {
                        $image_path = "../" . $project_to_delete['image'];
                        if (file_exists($image_path)) {
                            $delete_image = unlink($image_path);
                            error_log("Image deletion result: " . ($delete_image ? "success" : "failed"));
                        } else {
                            error_log("Image file not found: $image_path");
                        }
                    }
                    
                    $_SESSION['message'] = "Project deleted successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    error_log("Failed to save projects file");
                    $_SESSION['message'] = "Error saving project file. Please try again.";
                    $_SESSION['message_type'] = "error";
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                error_log("Project index not found: $project_index");
                $_SESSION['message'] = "Project not found!";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit();
            }
        } else {
            error_log("Failed to read projects file");
            $_SESSION['message'] = "Error reading projects file.";
            $_SESSION['message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }
    } else {
        error_log("Projects file not found: $projects_file");
        $_SESSION['message'] = "Projects file not found.";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }
}

// Handle form submission for adding new project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_project'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    
    if (empty($title) || empty($description)) {
        $_SESSION['message'] = "Title and description are required!";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit();
    } else {
        $image_path = "";
        
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "../assets/uploads/";
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_info = pathinfo($_FILES['image_file']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Check if file is an image
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_extension, $allowed_extensions)) {
                $_SESSION['message'] = "Only JPG, PNG, GIF, and WebP images are allowed!";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit();
            } else {
                // Generate unique filename
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                    $image_path = "assets/uploads/" . $unique_filename;
                } else {
                    $_SESSION['message'] = "Error uploading file. Please try again.";
                    $_SESSION['message_type'] = "error";
                    header("Location: dashboard.php");
                    exit();
                }
            }
        } elseif (!empty($image_url)) {
            // Use provided URL
            $image_path = $image_url;
        } else {
            $_SESSION['message'] = "Please provide either an image file or image URL!";
            $_SESSION['message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }
        
        // If we have a valid image path, save the project
        if (!empty($image_path)) {
            // Create data directory if it doesn't exist
            $data_dir = "../data";
            if (!is_dir($data_dir)) {
                mkdir($data_dir, 0755, true);
            }
            
            $projects_file = $data_dir . "/projects.json";
            
            // Load existing projects or create empty array
            $projects = [];
            if (file_exists($projects_file)) {
                $projects_data = file_get_contents($projects_file);
                if ($projects_data !== false) {
                    $projects = json_decode($projects_data, true) ?: [];
                }
            }
            
            // Add new project
            $new_project = [
                "title" => $title,
                "description" => $description,
                "image" => $image_path,
                "date_added" => date('Y-m-d H:i:s')
            ];
            
            $projects[] = $new_project;
            
            // Save back to file
            if (file_put_contents($projects_file, json_encode($projects, JSON_PRETTY_PRINT))) {
                $_SESSION['message'] = "Project added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['message'] = "Error saving project. Please try again.";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit();
            }
        }
    }
}

// Get message from session and clear it
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Load existing projects for display
$projects = [];
$projects_file = "../data/projects.json";
if (file_exists($projects_file)) {
    $projects_data = file_get_contents($projects_file);
    if ($projects_data !== false) {
        $projects = json_decode($projects_data, true) ?: [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
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
        }
        
        .header {
            background: #000;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .logout-btn {
            background: #333;
            color: white;
            border: 1px solid #555;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #555;
            border-color: #777;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .form-section, .projects-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: #fff;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #000;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .file-upload-container {
            border: 2px dashed #ccc;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .file-upload-container:hover {
            border-color: #000;
            background: #f1f3f4;
        }
        
        .file-upload-container input[type="file"] {
            display: none;
        }
        
        .file-upload-label {
            cursor: pointer;
            color: #000;
            font-weight: 500;
            font-size: 14px;
        }
        
        .file-upload-label:hover {
            text-decoration: underline;
        }
        
        .upload-info {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .or-divider {
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        
        .submit-btn {
            background: #000;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #333;
            transform: translateY(-1px);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 500;
            border-left: 4px solid;
        }
        
        .message.success {
            background: #f8f9fa;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .message.error {
            background: #f8f9fa;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        /* Loading Bar Styles */
        .upload-status {
            display: block;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .status-step {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .status-step:last-child {
            margin-bottom: 0;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-icon.pending {
            background: #ccc;
            color: #666;
        }
        
        .status-icon.processing {
            background: #000;
            color: white;
            animation: pulse 1.5s infinite;
        }
        
        .status-icon.success {
            background: #28a745;
            color: white;
        }
        
        .status-icon.error {
            background: #dc3545;
            color: white;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: #000;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 3px;
        }
        
        .upload-details {
            margin-top: 10px;
            padding: 8px;
            background: #f1f3f4;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .projects-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .project-item {
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .project-item:hover {
            border-color: #000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .project-title {
            font-weight: 600;
            color: #000;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .project-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .project-image {
            color: #000;
            font-size: 12px;
            word-break: break-all;
            font-family: monospace;
        }
        
        .project-date {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .delete-btn:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .no-projects {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
            padding: 8px 16px;
            border: 1px solid #000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: #000;
            color: white;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 15px;
            }
            
            .form-section, .projects-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Portfolio Admin Dashboard</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <div class="form-section">
                <h2 class="section-title">Add New Project</h2>
                <form method="POST" action="" enctype="multipart/form-data" id="projectForm">
                    <div class="form-group">
                        <label for="title">Project Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Project Image *</label>
                        <div class="file-upload-container">
                            <label for="image_file" class="file-upload-label">
                                📁 Choose Image File
                            </label>
                            <input type="file" id="image_file" name="image_file" accept="image/*">
                            <div class="upload-info">
                                Supported formats: JPG, PNG, GIF, WebP<br>
                                Max file size: 5MB
                            </div>
                        </div>
                        
                        <div class="or-divider">OR</div>
                        
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <!-- Upload Status Display -->
                    <div class="upload-status" id="uploadStatus">
                        <div class="status-step">
                            <div class="status-icon pending" id="step1">📋</div>
                            <span>Ready to upload...</span>
                        </div>
                        <div class="status-step">
                            <div class="status-icon pending" id="step2">📁</div>
                            <span>File selected: <span id="fileName">None</span></span>
                        </div>
                        <div class="status-step">
                            <div class="status-icon pending" id="step3">⏳</div>
                            <span>Upload progress: <span id="uploadProgress">0%</span></span>
                        </div>
                        <div class="status-step">
                            <div class="status-icon pending" id="step4">💾</div>
                            <span>Processing...</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="upload-details" id="uploadDetails">
                            <small>File size: <span id="fileSize">-</span> | Type: <span id="fileType">-</span></small>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">Add Project</button>
                </form>
            </div>
            
            <div class="projects-section">
                <h2 class="section-title">Current Projects (<?php echo count($projects); ?>)</h2>
                <div class="projects-list">
                    <?php if (empty($projects)): ?>
                        <div class="no-projects">
                            No projects added yet. Add your first project using the form on the left.
                        </div>
                    <?php else: ?>
                        <?php foreach ($projects as $index => $project): ?>
                            <div class="project-item">
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="delete_project" value="1">
                                    <input type="hidden" name="project_index" value="<?php echo $index; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this project?')">🗑️ Delete</button>
                                </form>
                                <div class="project-title"><?php echo htmlspecialchars($project['title']); ?></div>
                                <div class="project-description"><?php echo htmlspecialchars($project['description']); ?></div>
                                <div class="project-image"><?php echo htmlspecialchars($project['image']); ?></div>
                                <?php if (isset($project['date_added'])): ?>
                                    <div class="project-date">Added: <?php echo htmlspecialchars($project['date_added']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="back-link">
            <a href="../index.html">← Back to Portfolio</a>
            <a href="test_delete.php">🔧 Test Delete Function</a>
        </div>
    </div>
    
    <script>
        let uploadInProgress = false;
        let autoUploadTriggered = false;

        function canAutoUpload() {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const file = document.getElementById('image_file').files[0];
            return !!(title && description && file);
        }

        function tryAutoUpload() {
            if (!uploadInProgress && canAutoUpload() && !autoUploadTriggered) {
                autoUploadTriggered = true;
                setTimeout(() => {
                    submitForm();
                }, 200); // slight delay for UX
            }
        }

        document.getElementById('image_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const uploadStatus = document.getElementById('uploadStatus');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileType = document.getElementById('fileType');
            
            if (file) {
                // Update file info
                const label = document.querySelector('.file-upload-label');
                label.textContent = `📁 ${file.name}`;
                
                // Update status display
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                fileType.textContent = file.type || 'Unknown';
                
                // Update step 2 to show file selected
                document.getElementById('step2').className = 'status-icon success';
                document.getElementById('step2').textContent = '✓';
                
                // Update upload info
                const uploadInfo = document.querySelector('.upload-info');
                uploadInfo.innerHTML = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)<br>Supported formats: JPG, PNG, GIF, WebP`;
                
                tryAutoUpload();
            } else {
                // Reset status
                fileName.textContent = 'None';
                fileSize.textContent = '-';
                fileType.textContent = '-';
                document.getElementById('step2').className = 'status-icon pending';
                document.getElementById('step2').textContent = '📁';
            }
        });

        // Listen for changes on required fields to trigger auto-upload if possible
        document.getElementById('title').addEventListener('input', tryAutoUpload);
        document.getElementById('description').addEventListener('input', tryAutoUpload);

        // Handle form submission with real upload progress
        document.getElementById('projectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
        
        function submitForm() {
            if (uploadInProgress) return;
            uploadInProgress = true;
            const form = document.getElementById('projectForm');
            const submitBtn = document.getElementById('submitBtn');
            const progressFill = document.getElementById('progressFill');
            const uploadProgress = document.getElementById('uploadProgress');
            const file = document.getElementById('image_file').files[0];
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const imageUrl = document.getElementById('image_url').value.trim();
            
            // Validate required fields
            if (!title || !description) {
                alert('Title and description are required!');
                uploadInProgress = false;
                return;
            }
            
            if (!file && !imageUrl) {
                alert('Please provide either an image file or image URL!');
                uploadInProgress = false;
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            
            // Step 1: Form validation
            document.getElementById('step1').className = 'status-icon processing';
            document.getElementById('step1').textContent = '⏳';
            
            setTimeout(() => {
                document.getElementById('step1').className = 'status-icon success';
                document.getElementById('step1').textContent = '✓';
                
                // Step 3: Upload progress
                document.getElementById('step3').className = 'status-icon processing';
                document.getElementById('step3').textContent = '⏳';
                
                // Create FormData
                const formData = new FormData();
                formData.append('title', title);
                formData.append('description', description);
                if (file) {
                    formData.append('image_file', file);
                }
                if (imageUrl) {
                    formData.append('image_url', imageUrl);
                }
                
                // Create XMLHttpRequest for real upload progress
                const xhr = new XMLHttpRequest();
                
                // Track upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressFill.style.width = percentComplete + '%';
                        uploadProgress.textContent = Math.round(percentComplete) + '%';
                    }
                });
                
                // Handle upload completion
                xhr.addEventListener('load', function() {
                    uploadInProgress = false;
                    autoUploadTriggered = false;
                    if (xhr.status === 200) {
                        // Step 4: Processing complete
                        document.getElementById('step3').className = 'status-icon success';
                        document.getElementById('step3').textContent = '✓';
                        document.getElementById('step4').className = 'status-icon success';
                        document.getElementById('step4').textContent = '✓';
                        progressFill.style.width = '100%';
                        uploadProgress.textContent = '100%';
                        
                        // Reload page to show success message
                        window.location.reload();
                    } else {
                        // Handle error
                        document.getElementById('step3').className = 'status-icon error';
                        document.getElementById('step3').textContent = '✗';
                        document.getElementById('step4').className = 'status-icon error';
                        document.getElementById('step4').textContent = '✗';
                        alert('Upload failed. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Add Project';
                    }
                });
                
                // Handle upload error
                xhr.addEventListener('error', function() {
                    uploadInProgress = false;
                    autoUploadTriggered = false;
                    document.getElementById('step3').className = 'status-icon error';
                    document.getElementById('step3').textContent = '✗';
                    document.getElementById('step4').className = 'status-icon error';
                    document.getElementById('step4').textContent = '✗';
                    alert('Upload failed. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Project';
                });
                
                // Send the request
                xhr.open('POST', 'dashboard.php', true);
                xhr.send(formData);
            }, 300);
        }
        
        // Clear form after successful submission
        <?php if (isset($_SESSION['message']) && $_SESSION['message_type'] === 'success'): ?>
        document.getElementById('projectForm').reset();
        document.querySelector('.file-upload-label').textContent = '📁 Choose Image File';
        document.querySelector('.upload-info').innerHTML = 'Supported formats: JPG, PNG, GIF, WebP<br>Max file size: 5MB';
        
        // Reset status panel
        document.getElementById('fileName').textContent = 'None';
        document.getElementById('fileSize').textContent = '-';
        document.getElementById('fileType').textContent = '-';
        document.getElementById('uploadProgress').textContent = '0%';
        document.getElementById('progressFill').style.width = '0%';
        
        // Reset all status icons
        document.getElementById('step1').className = 'status-icon pending';
        document.getElementById('step1').textContent = '📋';
        document.getElementById('step2').className = 'status-icon pending';
        document.getElementById('step2').textContent = '📁';
        document.getElementById('step3').className = 'status-icon pending';
        document.getElementById('step3').textContent = '⏳';
        document.getElementById('step4').className = 'status-icon pending';
        document.getElementById('step4').textContent = '💾';
        <?php endif; ?>
    </script>
</body>
</html>
