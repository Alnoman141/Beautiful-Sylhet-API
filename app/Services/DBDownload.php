<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2025-04-10 10:53
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

class DBDownload
{
    public static function exportDatabase()
    {
        $dbName = config('database.connections.mysql.database');
        $tables = [];

        // Get all table names
        $result = DB::select("SHOW TABLES");
        $prep = "Tables_in_{$dbName}";
        foreach ($result as $res) {
            $tables[] = $res->$prep;
        }

        $pdo = DB::connection()->getPdo();
        $output = '';

        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $showTableQuery = "SHOW CREATE TABLE `$table`";
            $statement = $pdo->prepare($showTableQuery);
            $statement->execute();
            $createResult = $statement->fetch(PDO::FETCH_ASSOC);

            $output .= "\n\n" . $createResult['Create Table'] . ";\n\n";

            // Get data rows
            $selectQuery = "SELECT * FROM `$table`";
            $statement = $pdo->prepare($selectQuery);
            $statement->execute();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(fn($col) => "`" . addslashes($col) . "`", array_keys($row));
                $values = array_map(fn($val) => is_null($val) ? 'NULL' : "'" . addslashes($val) . "'", array_values($row));

                $output .= "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
        }

        $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path($fileName);
        file_put_contents($filePath, $output);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    public static function exportOnlyData()
    {
        $dbName = config('database.connections.mysql.database');
        $tables = [];

        // Get all table names
        $result = DB::select("SHOW TABLES");
        $tableKey = "Tables_in_{$dbName}";

        foreach ($result as $res) {
            $tables[] = $res->$tableKey;
        }

        $pdo = DB::connection()->getPdo();
        $output = '';

        foreach ($tables as $table) {
            if (!in_array($table, ['permissions', 'role_has_permissions', 'roles','migrations','transport_type_parents','transport_types','languages','modules','news_types'])) {
                // Fetch all data
                $selectQuery = "SELECT * FROM `$table`";
                $statement = $pdo->prepare($selectQuery);
                $statement->execute();

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_map(fn($col) => "`" . addslashes($col) . "`", array_keys($row));
                    $values = array_map(fn($val) => is_null($val) ? 'NULL' : "'" . addslashes($val) . "'",
                        array_values($row)
                    );

                    $output .= "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
            }
        }

        $fileName = 'only_data_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path($fileName);
        file_put_contents($filePath, $output);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
