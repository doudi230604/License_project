<?php
header('Content-Type: application/json');

// Directory where uploaded files will be stored
$uploads_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

// Create directory if it doesn't exist
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Check if file uploaded successfully
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $file['tmp_name'];
            $name = basename($file['name']);
            $target = $uploads_dir . DIRECTORY_SEPARATOR . $name;

            // Move file to target location
            if (move_uploaded_file($tmp_name, $target)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                    'filename' => $name
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to move uploaded file.'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Upload error code: ' . $file['error']
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No file field received.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Use POST.'
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Folder Manager</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

  <!-- Add Folder Button -->
  <button id="addFolderBtn" onclick="openModal()" class="action-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" title="Add Folder">
    <i class="fas fa-folder-plus"></i>
    <span>+Folder</span>
  </button>

  <!-- Floating Modal -->
  <div id="folderModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4">Create New Folder</h2>
      <input id="folderName" type="text" placeholder="Folder name"
        class="w-full p-2 border border-gray-300 rounded mb-4">
      <input id="fileInput" type="file" name="file" class="w-full mb-4">
      <div class="flex justify-end space-x-2">
        <button onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
        <button onclick="createFolder()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create</button>
      </div>
    </div>
  </div>

  <!-- Folder Table -->
  <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full table-auto">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 text-left">#</th>
          <th class="px-4 py-2 text-left">Folder Name</th>
          <th class="px-4 py-2 text-left">Uploaded File</th>
        </tr>
      </thead>
      <tbody id="folderTableBody">
        <!-- Dynamic rows here -->
      </tbody>
    </table>
  </div>

  <script>
    let folders = [];

    function openModal() {
      document.getElementById('folderModal').classList.remove('hidden');
    }

    function closeModal() {
      document.getElementById('folderModal').classList.add('hidden');
      document.getElementById('folderName').value = '';
      document.getElementById('fileInput').value = '';
    }

    function createFolder() {
      const name = document.getElementById('folderName').value.trim();
      const fileInput = document.getElementById('fileInput');
      const file = fileInput.files[0];

      if (!name) {
        alert('Folder name is required.');
        return;
      }

      if (file) {
        const formData = new FormData();
        formData.append('file', file);

        fetch('upload.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            folders.push({ name, file: data.filename });
            updateFolderTable();
            closeModal();
          } else {
            alert(data.message);
          }
        })
        .catch(err => {
          console.error(err);
          alert('An error occurred while uploading.');
        });
      } else {
        folders.push({ name, file: 'None' });
        updateFolderTable();
        closeModal();
      }
    }

    function updateFolderTable() {
      const tableBody = document.getElementById('folderTableBody');
      tableBody.innerHTML = '';

      folders.forEach((folder, index) => {
        const row = `<tr class="border-b">
          <td class="px-4 py-2">${index + 1}</td>
          <td class="px-4 py-2">${folder.name}</td>
          <td class="px-4 py-2">${folder.file}</td>
        </tr>`;
        tableBody.insertAdjacentHTML('beforeend', row);
      });
    }
  </script>
</body>
</html>
