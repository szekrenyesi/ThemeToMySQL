<?php
/**
 * Memory-safe file validator for patstring and patdur
 *
 * @param string $filepath Path to file
 * @param string $filetype "patstring" or "patdur"
 * @param string $log_path Path to log file
 * @return array ['errors'=>[], 'warnings'=>[]]
 */
function validate_file_with_logging($filepath, $filetype, $log_path) {
    $errors = [];
    $warnings = [];
    $found_real_dataname = false;

    // Define expected fields
    if ($filetype === "patstring") {
        $expected_columns = ['dataname','id','N','Length','Level','N_actors','N_switches','patstring'];
        $numeric_fields = ['id','N','Length','Level','N_actors','N_switches'];
    } elseif ($filetype === "patdur") {
        $expected_columns = ['dataname','id','sample','starttime','endtime','duration'];
        $numeric_fields = ['id','sample','starttime','endtime','duration'];
    } else {
        return ['errors'=>["Unknown filetype '$filetype'"], 'warnings'=>[]];
    }

    // Open log file
    $log_handle = fopen($log_path, "a");
    fwrite($log_handle, "\n--- Validating $filetype (" . date("Y-m-d H:i:s") . ") ---\n");

    $handle = fopen($filepath, "r");
    if (!$handle) {
        $errors[] = "Cannot open file: $filepath";
        fwrite($log_handle, "[ERROR] Cannot open file.\n");
        fclose($log_handle);
        return compact('errors','warnings');
    }

    $line_number = 0;

    while (($row = fgetcsv($handle, 0, "\t")) !== false) {
        $line_number++;

        // Skip header row
        if ($line_number === 1) continue;

        // Skip empty lines
        if (count(array_filter($row)) === 0) continue;

        // Remove empty strings caused by multiple tabs
        $row = array_values(array_filter($row, function($v){ return $v !== ''; }));

        // Limit extremely long lines
        if (strlen(implode("\t", $row)) > 10000) {
            $warnings[] = "Line $line_number too long, skipped";
            fwrite($log_handle, "[WARN] Line $line_number too long, skipped.\n");
            continue;
        }

        // Pad or truncate to expected columns
        $row = array_slice(array_pad($row, count($expected_columns), ''), 0, count($expected_columns));

        // Map fields safely
        $dataname   = trim($row[0] ?? '');
        $id         = trim($row[1] ?? '');
        $N          = trim($row[2] ?? '');
        $Length     = trim($row[3] ?? '');
        $Level      = trim($row[4] ?? '');
        $N_actors   = trim($row[5] ?? '');
        $N_switches = trim($row[6] ?? '');
        $patstring  = trim($row[7] ?? '');

        if ($filetype === "patdur") {
            $sample     = trim($row[2] ?? '');
            $starttime  = trim($row[3] ?? '');
            $endtime    = trim($row[4] ?? '');
            $duration   = trim($row[5] ?? '');
        }

        // Check for at least one real dataname in patstring
        if ($filetype === "patstring" && $dataname !== "" && strtolower($dataname) !== "dataname") {
            $found_real_dataname = true;
        }

        // Validate numeric fields
        foreach ($numeric_fields as $field) {
            $val = ($filetype === "patstring") ? $$field : ($$field ?? '');
            if ($val === "" || !ctype_digit($val)) {
                $errors[] = "$filetype line $line_number: '$field' must be integer. Found: \"$val\"";
                fwrite($log_handle, "[ERROR] Line $line_number: '$field' invalid.\n");
            }
        }

        // Optional: check patstring format (PHP7 safe)
        if ($filetype === "patstring") {
            if ($patstring !== "" && (substr($patstring, 0, 1) !== "(" || substr($patstring, -1) !== ")")) {
                $warnings[] = "$filetype line $line_number: patstring may be malformed";
                fwrite($log_handle, "[WARN] Line $line_number: patstring malformed.\n");
            }
        }
    }

    fclose($handle);

    if ($filetype === "patstring" && !$found_real_dataname) {
        $errors[] = "No real dataname found in patstring file";
        fwrite($log_handle, "[ERROR] No real dataname found.\n");
    }

    fwrite($log_handle, "--- End validation for $filetype ---\n\n");
    fclose($log_handle);

    return compact('errors','warnings');
}
?>

