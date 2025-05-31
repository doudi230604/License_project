<?php
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    echo '<tr><td colspan="6" class="px-4 py-2 text-center text-red-600">Database error.</td></tr>';
} else {
    $result = $conn->query("SELECT * FROM trash ORDER BY deleted_at DESC");
    if ($result && $result->num_rows > 0) {
        // Add "Delete All" button above the table
        echo '<tr><td colspan="6" class="px-4 py-2 text-right">
        </td></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td class="px-4 py-2">' . htmlspecialchars($row['filename']) . '</td>';
            echo '<td class="px-4 py-2">' . strtoupper(htmlspecialchars($row['filetype'])) . '</td>';
            echo '<td class="px-4 py-2">' . htmlspecialchars($row['deleted_at']) . '</td>';
            echo '<td class="px-4 py-2">' . round($row['filesize'] / 1024, 2) . ' KB</td>';
            echo '<td class="px-4 py-2 text-center">
                <button onclick="restoreFile(' . $row['id'] . ')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs font-bold">Restore</button>
            </td>';
            echo '<td class="px-4 py-2 text-center">
                <button onclick="deleteTrashFile(' . $row['id'] . ', \'' . addslashes($row['filename']) . '\')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs font-bold">Delete Permanently</button>
            </td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" class="px-4 py-2 text-center text-gray-500">Trash is empty.</td></tr>';
    }
    $conn->close();
}
?>