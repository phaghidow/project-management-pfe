<?php
/**
 * Verify API endpoint response
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Structure;

echo "=== VERIFICATION: /api/organigramme-v2 ===\n";
echo str_repeat("=", 70) . "\n";

// Get JSON response from endpoint
$response = file_get_contents('http://127.0.0.1:8000/api/organigramme-v2');
$data = json_decode($response, true);

echo "\n--- RAW JSON RESPONSE (Formatted) ---\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n" . str_repeat("-", 70) . "\n";
echo "--- RESPONSE ANALYSIS ---\n";
echo str_repeat("-", 70) . "\n";

// Count returned structures
$returnedCount = count($data);
echo "\nStructures returned by API: " . $returnedCount . "\n";

// Count active structures in DB
$dbCount = Structure::where('is_active', true)->count();
echo "Active structures in DB: " . $dbCount . "\n";

// Verify match
if ($returnedCount === $dbCount) {
    echo "✓ Count matches!\n";
} else {
    echo "✗ Count mismatch!\n";
}

// Check format is flat (not nested)
echo "\n--- FORMAT CHECK ---\n";
$isFlatArray = is_array($data) && !empty($data);
$hasOnlyExpectedKeys = true;
$expectedKeys = ['id', 'name', 'type', 'parent_id'];

if ($isFlatArray) {
    foreach ($data as $item) {
        if (!is_array($item)) {
            $isFlatArray = false;
            break;
        }

        $keys = array_keys($item);
        sort($keys);
        $expectedKeysSorted = $expectedKeys;
        sort($expectedKeysSorted);

        if ($keys !== $expectedKeysSorted) {
            $hasOnlyExpectedKeys = false;
            echo "Item has unexpected keys: " . implode(', ', $keys) . "\n";
            break;
        }

        // Check for nested structures or arrays (would indicate tree format)
        foreach ($item as $value) {
            if (is_array($value)) {
                $isFlatArray = false;
                echo "Found nested array in item - not flat format!\n";
                break 2;
            }
        }
    }
}

if ($isFlatArray && $hasOnlyExpectedKeys) {
    echo "✓ Format is flat array with correct keys (id, name, type, parent_id)\n";
    echo "✓ No nested children or users arrays\n";
} else {
    echo "✗ Format check failed!\n";
}

// Sample first and last items
echo "\n--- SAMPLE ITEMS ---\n";
echo "First item:\n";
echo json_encode($data[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\nLast item:\n";
echo json_encode($data[$returnedCount - 1], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "✓ ENDPOINT TEST COMPLETE\n";

exit(0);
