<?php
/**
 * Verify FK constraints and clean up orphans from test data deletion
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FOREIGN KEY CONSTRAINT ANALYSIS ===\n";
echo str_repeat("-", 70) . "\n";

// Check FK constraints from information_schema
$query = "
    SELECT
        rc.CONSTRAINT_NAME,
        kcu.TABLE_NAME,
        kcu.COLUMN_NAME,
        kcu.REFERENCED_TABLE_NAME,
        kcu.REFERENCED_COLUMN_NAME,
        rc.DELETE_RULE
    FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
    JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
        ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
        AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
    WHERE rc.CONSTRAINT_SCHEMA = ?
    AND kcu.TABLE_NAME IN ('audit_logs', 'structure_histories')
    ORDER BY kcu.TABLE_NAME, kcu.COLUMN_NAME
";

$constraints = DB::select($query, [env('DB_DATABASE', 'project_management')]);

if (empty($constraints)) {
    echo "No FK constraints found.\n";
} else {
    echo "Found " . count($constraints) . " FK constraint(s):\n\n";
    foreach ($constraints as $constraint) {
        echo "Table: {$constraint->TABLE_NAME}\n";
        echo "  Column: {$constraint->COLUMN_NAME}\n";
        echo "  References: {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
        echo "  Delete Rule: {$constraint->DELETE_RULE}\n";
        echo "  Constraint Name: {$constraint->CONSTRAINT_NAME}\n\n";
    }
}

echo str_repeat("-", 70) . "\n";
echo "=== ORPHAN DATA ANALYSIS ===\n";
echo str_repeat("-", 70) . "\n";

// Check for orphans in structure_histories (should be empty due to CASCADE)
$orphanHistories = DB::select("
    SELECT sh.id, sh.structure_id
    FROM structure_histories sh
    LEFT JOIN structures s ON sh.structure_id = s.id
    WHERE s.id IS NULL
");

echo "\nOrphans in structure_histories (structure_id not in structures):\n";
if (empty($orphanHistories)) {
    echo "  ✓ None found. (CASCADE delete likely removed them)\n";
    $orphanHistoriesCount = 0;
} else {
    echo "  Found " . count($orphanHistories) . " orphan(s):\n";
    foreach ($orphanHistories as $orphan) {
        echo "    id=" . $orphan->id . ", structure_id=" . $orphan->structure_id . "\n";
    }
    $orphanHistoriesCount = count($orphanHistories);
}

// Check for orphans in audit_logs (for action='structures')
$orphanAudits = DB::select("
    SELECT al.id, al.entity_id
    FROM audit_logs al
    LEFT JOIN structures s ON al.entity_id = s.id
    WHERE al.entity_type = 'structures'
    AND s.id IS NULL
");

echo "\nOrphans in audit_logs (entity_type='structures', entity_id not in structures):\n";
if (empty($orphanAudits)) {
    echo "  ✓ None found.\n";
    $orphanAuditsCount = 0;
} else {
    echo "  Found " . count($orphanAudits) . " orphan(s):\n";
    foreach ($orphanAudits as $orphan) {
        echo "    id=" . $orphan->id . ", entity_id=" . $orphan->entity_id . "\n";
    }
    $orphanAuditsCount = count($orphanAudits);
}

echo "\n" . str_repeat("-", 70) . "\n";
echo "=== CLEANUP ===\n";
echo str_repeat("-", 70) . "\n";

$totalDeleted = 0;

// Delete orphans from structure_histories (if any exist despite CASCADE)
if ($orphanHistoriesCount > 0) {
    DB::statement("
        DELETE FROM structure_histories
        WHERE id IN (
            SELECT id FROM (
                SELECT sh.id
                FROM structure_histories sh
                LEFT JOIN structures s ON sh.structure_id = s.id
                WHERE s.id IS NULL
            ) temp
        )
    ");
    echo "Deleted " . $orphanHistoriesCount . " orphan(s) from structure_histories.\n";
    $totalDeleted += $orphanHistoriesCount;
} else {
    echo "✓ No cleanup needed for structure_histories.\n";
}

// Delete orphans from audit_logs (if any exist)
if ($orphanAuditsCount > 0) {
    DB::statement("
        DELETE FROM audit_logs
        WHERE id IN (
            SELECT id FROM (
                SELECT al.id
                FROM audit_logs al
                LEFT JOIN structures s ON al.entity_id = s.id
                WHERE al.entity_type = 'structures'
                AND s.id IS NULL
            ) temp
        )
    ");
    echo "Deleted " . $orphanAuditsCount . " orphan(s) from audit_logs.\n";
    $totalDeleted += $orphanAuditsCount;
} else {
    echo "✓ No cleanup needed for audit_logs.\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TOTAL ROWS DELETED: " . $totalDeleted . "\n";
echo "=== VERIFICATION COMPLETE ===\n";

exit(0);
