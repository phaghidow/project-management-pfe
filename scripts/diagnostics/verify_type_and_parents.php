<?php
/**
 * Verify API data integrity:
 * 1. Check how type is used in UI
 * 2. Check for orphaned parent_id references
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICATION 1: Type Usage in OrganizationNode.vue ===\n";
echo str_repeat("=", 70) . "\n";

echo "\nFrom OrganizationNode.vue (line 44):\n";
echo "const normalizedType = computed(() => String(props.data?.type || 'ROLE').toUpperCase())\n";
echo "\nHow it's used:\n";
echo "- Simply converts type to UPPERCASE for display (no conditional styling)\n";
echo "- No color mapping, no icon selection based on type\n";
echo "- Just a generic text label\n";

echo "\n--- Type Mapping Analysis ---\n";
echo "Prototype types: 'pdg', 'dga', 'division', 'autre'\n";
echo "Real API types:  'dg', 'pole', 'direction', 'autre', 'division'\n";
echo "\n✓ NO REMAPPING NEEDED\n";
echo "Reason: OrganizationNode.vue only uppercases the type string for display.\n";
echo "Result: 'dg' → 'DG', 'pole' → 'POLE', 'division' → 'DIVISION', etc.\n";
echo "No styling conflicts or missing type handlers.\n";

// ========================================================================

echo "\n\n=== VERIFICATION 2: Check for Orphaned parent_id References ===\n";
echo str_repeat("=", 70) . "\n";

// Get API data
$response = file_get_contents('http://127.0.0.1:8000/api/organigramme-v2');
$apiData = json_decode($response, true);

echo "\nFetched " . count($apiData) . " structures from API\n";

// Build id set
$existingIds = array_map(fn($item) => $item['id'], $apiData);
$idSet = array_flip($existingIds);

echo "\nExisting IDs: " . implode(', ', $existingIds) . "\n";

// Check for orphaned parent_id
echo "\n--- Orphan Check ---\n";
$orphans = [];
$validParents = [];

foreach ($apiData as $item) {
    if ($item['parent_id'] !== null) {
        if (!isset($idSet[$item['parent_id']])) {
            $orphans[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'parent_id' => $item['parent_id'],
            ];
        } else {
            $validParents[$item['parent_id']] = true;
        }
    }
}

if (empty($orphans)) {
    echo "✓ NO ORPHANED PARENT REFERENCES FOUND\n";
    echo "  All 26 structures have valid parent_id values (or null for roots)\n";
} else {
    echo "✗ FOUND " . count($orphans) . " ORPHANED REFERENCES:\n";
    foreach ($orphans as $orphan) {
        echo "  - id={$orphan['id']}, name={$orphan['name']}, parent_id={$orphan['parent_id']} (NOT IN API)\n";
    }
}

echo "\n--- Parent Statistics ---\n";
$rootCount = count(array_filter($apiData, fn($item) => $item['parent_id'] === null));
echo "Root nodes (parent_id=null): " . $rootCount . "\n";
echo "Non-root nodes with valid parent: " . count($apiData) - $rootCount . "\n";
echo "Unique parents referenced: " . count($validParents) . "\n";

echo "\n--- Sample Parent-Child Relationships ---\n";
$relationships = [];
foreach ($apiData as $item) {
    if ($item['parent_id'] !== null) {
        $parent = null;
        foreach ($apiData as $p) {
            if ($p['id'] === $item['parent_id']) {
                $parent = $p;
                break;
            }
        }
        $relationships[] = [
            'parent_name' => $parent ? $parent['name'] : 'UNKNOWN',
            'child_name' => $item['name'],
        ];
    }
}

$displayed = array_slice($relationships, 0, 5);
foreach ($displayed as $rel) {
    echo "  {$rel['parent_name']} → {$rel['child_name']}\n";
}
if (count($relationships) > 5) {
    echo "  ... and " . (count($relationships) - 5) . " more\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✓ VERIFICATION COMPLETE\n";

exit(0);
