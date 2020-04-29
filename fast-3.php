<?php
// set_time_limit in php.ini
$running_timeout = 180;
set_time_limit($running_timeout);

// https://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function updateRunningStatus($env_data = null, $status = null)
{
    if ($status == null || $env_data == null) {
        die('code Err: call dev.');
    }
    switch ($status){
        case 'running':
            // update running-status
            $env_data->timestamp = time();
            $env_data->running = true;
            file_put_contents('fast-3.json', json_encode($env_data));
        break;
        
        case 'runned':
            // update runned-status
            $env_data->timestamp = 123;
            $env_data->running = false;
            file_put_contents('fast-3.json', json_encode($env_data));
        break;

        default: 
            die('code Err: call dev.');
    }

    return true;
}

function saveLog($text)
{
    echo $text;
    file_put_contents('log-fast-3.txt', "$text\n", FILE_APPEND);
}

function generateWordpress($scode = null)
{
    // filter $scode
    if (
        $scode == null ||
        strlen($scode) != 3 ||
        !is_numeric($scode)
    ) {
        saveLog('Error: รหัสนักศึกษา ให้ใส่เลข 3 ตัวท้าย');
        return 'err';
    }

    // from-file
    $file = 'wordpress-5.4.zip';
    $temp_path = 'temp-3';

    // unZip to
    $path = '61262620' . $scode . '-3';
    // create folder
    if (file_exists($path)) {
        saveLog('Err! Dir is exist.(โฟลเดอร์นี้มีอยู่แล้ว)');
        return 'err';
    }
    
    // check another-running
    $env = file_get_contents('fast-3.json');
    $env_data = json_decode($env);
    
    // running timeout 3นาที
    if ( (time() - ($env_data->timestamp)) > ($GLOBALS['running_timeout'] + 12)) {
        $env_data->running = false;
    }
    if ($env_data->running == true) {
        saveLog('Err! มีคนอื่นใช้อยู่.');
        return 'err';
    }

    updateRunningStatus($env_data, 'running');

    // TODO: save to Q-list mode | more one used in same time.
    // del-temp-Dir
    deleteDirectory($temp_path);

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
        // extract it to the path we determined above
        $zip->extractTo($temp_path);
        $zip->close();
        // move $temp_path to "scode"
        rename($temp_path . '/wordpress', $path);
        saveLog("Success! $file extracted to $path");
        echo '
        <div>
            <label style="color: green">สำเร็จ: </label>
            <a href="http://projectsthep.com/webapps/61262620'.$scode.'-3/">
                link
                <span>61262620'.$scode.'-3</span>
            </a>
        </div>
        ';
        updateRunningStatus($env_data, 'runned');

        return 'success';
    } else {
        $zip->close();
        saveLog("Err! I couldn't open $file");
        updateRunningStatus($env_data, 'runned');
        return 'err';
    }
}

if (!empty($_POST['scode'])) {
    $statusGenerate = generateWordpress($_POST['scode']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Wordpress</title>
</head>
<body>
    <h1>Generate Wordpress</h1>
    <form method="POST">
        <label for="รหัสนักศึกษา">รหัสนักศึกษา</label>
        <input name="scode" type="text">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</body>
</html>